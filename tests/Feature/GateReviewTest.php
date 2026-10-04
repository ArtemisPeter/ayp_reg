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
