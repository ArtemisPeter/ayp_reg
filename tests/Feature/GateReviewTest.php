<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\GateScan;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GateReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_mismatched_and_nameless_scans_appear_in_the_review_log_but_clean_scans_do_not(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $church = LocalChurch::factory()->create(['circuit_id' => Circuit::factory()->create()->id]);
        $juan = Attendee::factory()->create(['youth_camp_id' => $camp->id, 'local_church_id' => $church->id, 'first_name' => 'Juan']);
        $security = User::factory()->create(['role' => 'security']);

        GateScan::create([
            'client_scan_id' => (string) Str::uuid(),
            'attendee_id' => $juan->id,
            'direction' => 'in',
            'scanned_by' => $security->id,
            'scanned_at' => now(),
            'is_mismatch' => false,
        ]);
        GateScan::create([
            'client_scan_id' => (string) Str::uuid(),
            'attendee_id' => $juan->id,
            'direction' => 'in',
            'scanned_by' => $security->id,
            'scanned_at' => now(),
            'is_mismatch' => true,
            'message' => 'Already IN since 10:00 AM.',
        ]);
        GateScan::create([
            'client_scan_id' => (string) Str::uuid(),
            'attendee_id' => null,
            'manual_name' => 'Late Comer',
            'direction' => 'in',
            'scanned_by' => $security->id,
            'scanned_at' => now(),
            'is_mismatch' => true,
            'message' => 'Entered by name; not on the downloaded roster. Needs matching.',
        ]);

        $secretary = User::factory()->create(['role' => 'secretary']);

        $this->actingAs($secretary)->get('/gate/review')
            ->assertOk()
            ->assertSee('Already IN since 10:00 AM')
            ->assertSee('Late Comer')
            ->assertSee('Needs matching');

        $this->assertDatabaseCount('gate_scans', 3);
    }

    public function test_the_review_log_shows_the_attendees_full_name_and_circuit(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $church = LocalChurch::factory()->create(['circuit_id' => Circuit::factory()->create(['name' => 'Digos City'])->id, 'name' => 'Grace Church']);
        $juan = Attendee::factory()->create([
            'youth_camp_id' => $camp->id, 'local_church_id' => $church->id,
            'first_name' => 'Juan', 'middle_name' => 'Dela', 'last_name' => 'Cruz',
        ]);
        $this->mismatchFor($juan);

        $this->actingAs(User::factory()->create(['role' => 'secretary']))
            ->get('/gate/review')
            ->assertSee('Cruz, Juan Dela')
            ->assertSee('Digos City')
            ->assertSee('Grace Church');
    }

    public function test_deleting_a_scan_removes_it_from_the_review_and_from_the_attendees_in_out_record(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $church = LocalChurch::factory()->create(['circuit_id' => Circuit::factory()->create()->id]);
        $juan = Attendee::factory()->create(['youth_camp_id' => $camp->id, 'local_church_id' => $church->id, 'last_name' => 'Cruz']);
        $wrongScan = $this->mismatchFor($juan, direction: 'out', message: 'Not recorded as IN yet.');

        $this->actingAs(User::factory()->create(['role' => 'secretary']))
            ->delete("/gate/scans/{$wrongScan->id}")
            ->assertRedirect('/gate/review');

        $this->assertSoftDeleted($wrongScan);
        $this->get('/gate/review')->assertDontSee('Not recorded as IN yet');

        $this->actingAs(User::factory()->create(['role' => 'security']))
            ->postJson('/gate/scans', [
                'client_scan_id' => (string) Str::uuid(),
                'direction' => 'in',
                'scanned_at' => now()->toISOString(),
                'qr_code' => $juan->qr_code,
            ])
            ->assertJsonPath('mismatch', false);
    }

    public function test_a_scanner_resending_a_deleted_scan_does_not_bring_it_back(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $church = LocalChurch::factory()->create(['circuit_id' => Circuit::factory()->create()->id]);
        $juan = Attendee::factory()->create(['youth_camp_id' => $camp->id, 'local_church_id' => $church->id]);
        $scan = $this->mismatchFor($juan);
        $scan->delete();

        $this->actingAs(User::factory()->create(['role' => 'security']))
            ->postJson('/gate/scans', [
                'client_scan_id' => $scan->client_scan_id,
                'direction' => 'in',
                'scanned_at' => now()->toISOString(),
                'qr_code' => $juan->qr_code,
            ])
            ->assertOk();

        $this->assertSame(0, GateScan::count());
        $this->assertSame(1, GateScan::withTrashed()->count());
    }

    #[DataProvider('rolesThatCannotReview')]
    public function test_only_the_secretary_and_admin_can_delete_a_scan(string $role): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $church = LocalChurch::factory()->create(['circuit_id' => Circuit::factory()->create()->id]);
        $scan = $this->mismatchFor(Attendee::factory()->create(['youth_camp_id' => $camp->id, 'local_church_id' => $church->id]));

        $this->actingAs(User::factory()->create(['role' => $role]))
            ->delete("/gate/scans/{$scan->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted($scan);
    }

    private function mismatchFor(Attendee $attendee, string $direction = 'in', string $message = 'Already IN since 10:00 AM.'): GateScan
    {
        return GateScan::create([
            'client_scan_id' => (string) Str::uuid(),
            'attendee_id' => $attendee->id,
            'direction' => $direction,
            'scanned_by' => User::factory()->create(['role' => 'security'])->id,
            'scanned_at' => now()->subMinutes(10),
            'is_mismatch' => true,
            'message' => $message,
        ]);
    }

    #[DataProvider('rolesThatCannotReview')]
    public function test_only_the_secretary_and_admin_can_see_the_review_log(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get('/gate/review')->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatCannotReview(): array
    {
        return [
            'circuit president' => ['circuit_president'],
            'security' => ['security'],
            'tabulator' => ['tabulator'],
        ];
    }
}
