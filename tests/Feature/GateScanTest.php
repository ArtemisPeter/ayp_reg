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

class GateScanTest extends TestCase
{
    use RefreshDatabase;

    private User $security;

    private Attendee $juan;

    protected function setUp(): void
    {
        parent::setUp();

        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $circuit = Circuit::factory()->create(['name' => 'Digos City']);
        $church = LocalChurch::factory()->create(['circuit_id' => $circuit->id, 'name' => 'Grace Church']);
        $this->juan = Attendee::factory()->create([
            'youth_camp_id' => $camp->id,
            'local_church_id' => $church->id,
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
        ]);
        $this->security = User::factory()->create(['role' => 'security']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function scanPayload(array $overrides = []): array
    {
        return $overrides + [
            'client_scan_id' => (string) Str::uuid(),
            'direction' => 'in',
            'scanned_at' => now()->toISOString(),
            'qr_code' => $this->juan->qr_code,
        ];
    }

    public function test_the_first_scan_of_an_attendee_records_them_as_in_with_no_mismatch(): void
    {
        $response = $this->actingAs($this->security)
            ->postJson('/gate/scans', $this->scanPayload())
            ->assertOk()
            ->json();

        $this->assertSame('in', $response['status']);
        $this->assertFalse($response['mismatch']);
        $this->assertSame('Juan', $response['attendee']['first_name']);
        $this->assertSame('Grace Church', $response['attendee']['local_church']);
        $this->assertSame('Digos City', $response['attendee']['circuit']);

        $this->assertDatabaseHas('gate_scans', [
            'attendee_id' => $this->juan->id,
            'direction' => 'in',
            'is_mismatch' => false,
        ]);
    }

    public function test_scanning_in_again_while_already_in_is_a_mismatch_but_still_recorded(): void
    {
        $this->actingAs($this->security)->postJson('/gate/scans', $this->scanPayload([
            'scanned_at' => now()->subMinutes(10)->toISOString(),
        ]));

        $response = $this->postJson('/gate/scans', $this->scanPayload())
            ->assertOk()
            ->json();

        $this->assertSame('in', $response['status']);
        $this->assertTrue($response['mismatch']);
        $this->assertStringContainsString('Already IN', $response['message']);

        $this->assertSame(2, GateScan::where('attendee_id', $this->juan->id)->count());
    }

    public function test_a_scan_time_sent_in_utc_by_the_scanner_is_reported_in_philippine_time(): void
    {
        $this->actingAs($this->security)->postJson('/gate/scans', $this->scanPayload([
            'scanned_at' => '2026-12-01T02:15:00.000Z',
        ]));

        $this->postJson('/gate/scans', $this->scanPayload(['scanned_at' => '2026-12-01T03:00:00.000Z']))
            ->assertJsonPath('message', 'Already IN since 10:15 AM.');
    }

    public function test_scanning_out_with_no_prior_in_is_a_mismatch(): void
    {
        $response = $this->actingAs($this->security)
            ->postJson('/gate/scans', $this->scanPayload(['direction' => 'out']))
            ->assertOk()
            ->json();

        $this->assertSame('out', $response['status']);
        $this->assertTrue($response['mismatch']);
        $this->assertStringContainsString('Not recorded as IN', $response['message']);
    }

    public function test_alternating_in_then_out_has_no_mismatch_either_time(): void
    {
        $this->actingAs($this->security)->postJson('/gate/scans', $this->scanPayload([
            'scanned_at' => now()->subMinutes(10)->toISOString(),
        ]))->assertJsonPath('mismatch', false);

        $this->postJson('/gate/scans', $this->scanPayload(['direction' => 'out']))
            ->assertJsonPath('status', 'out')
            ->assertJsonPath('mismatch', false);
    }

    public function test_a_same_direction_scan_within_five_seconds_is_ignored(): void
    {
        $now = now();

        $this->actingAs($this->security)->postJson('/gate/scans', $this->scanPayload(['scanned_at' => $now->toISOString()]))
            ->assertJsonPath('mismatch', false);

        $this->postJson('/gate/scans', $this->scanPayload(['scanned_at' => $now->copy()->addSeconds(3)->toISOString()]))
            ->assertJsonPath('mismatch', false);

        $this->assertSame(1, GateScan::where('attendee_id', $this->juan->id)->count());
    }

    public function test_a_same_direction_scan_after_five_seconds_is_a_new_scan(): void
    {
        $now = now();

        $this->actingAs($this->security)->postJson('/gate/scans', $this->scanPayload(['scanned_at' => $now->toISOString()]));

        $this->postJson('/gate/scans', $this->scanPayload(['scanned_at' => $now->copy()->addSeconds(10)->toISOString()]))
            ->assertJsonPath('mismatch', true);

        $this->assertSame(2, GateScan::where('attendee_id', $this->juan->id)->count());
    }

    public function test_an_unrecognized_qr_code_is_invalid_and_records_nothing(): void
    {
        $response = $this->actingAs($this->security)
            ->postJson('/gate/scans', $this->scanPayload(['qr_code' => 'not-a-real-code']))
            ->assertOk()
            ->json();

        $this->assertSame('invalid', $response['status']);
        $this->assertDatabaseCount('gate_scans', 0);
    }

    public function test_a_removed_attendees_qr_code_is_invalid(): void
    {
        $qrCode = $this->juan->qr_code;
        $this->juan->delete();

        $response = $this->actingAs($this->security)
            ->postJson('/gate/scans', $this->scanPayload(['qr_code' => $qrCode]))
            ->assertOk()
            ->json();

        $this->assertSame('invalid', $response['status']);
    }

    public function test_a_manual_name_entry_is_recorded_with_no_attendee_and_flagged_for_review(): void
    {
        $response = $this->actingAs($this->security)
            ->postJson('/gate/scans', $this->scanPayload(['qr_code' => null, 'manual_name' => 'Late Comer']))
            ->assertOk()
            ->json();

        $this->assertSame('in', $response['status']);
        $this->assertTrue($response['mismatch']);
        $this->assertNull($response['attendee']);

        $this->assertDatabaseHas('gate_scans', [
            'attendee_id' => null,
            'manual_name' => 'Late Comer',
            'is_mismatch' => true,
        ]);
    }

    public function test_a_scan_needs_either_a_qr_code_or_a_manual_name(): void
    {
        $this->actingAs($this->security)
            ->postJson('/gate/scans', $this->scanPayload(['qr_code' => null]))
            ->assertUnprocessable();
    }

    public function test_resubmitting_the_same_client_scan_id_does_not_create_a_second_row(): void
    {
        $payload = $this->scanPayload();

        $this->actingAs($this->security)->postJson('/gate/scans', $payload)->assertJsonPath('mismatch', false);
        $this->postJson('/gate/scans', $payload)->assertJsonPath('mismatch', false);

        $this->assertSame(1, GateScan::where('attendee_id', $this->juan->id)->count());
    }

    #[DataProvider('rolesThatCannotScanTheGate')]
    public function test_only_security_and_admin_can_record_a_gate_scan(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->postJson('/gate/scans', $this->scanPayload())->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatCannotScanTheGate(): array
    {
        return [
            'circuit president' => ['circuit_president'],
            'secretary' => ['secretary'],
            'tabulator' => ['tabulator'],
        ];
    }
}
