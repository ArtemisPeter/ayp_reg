<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GateRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_roster_has_only_qr_id_first_name_local_church_and_circuit_for_the_current_camp(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $lastYear = YouthCamp::factory()->create(['is_current' => false]);
        $circuit = Circuit::factory()->create(['name' => 'Digos City']);
        $church = LocalChurch::factory()->create(['circuit_id' => $circuit->id, 'name' => 'Grace Church']);

        $juan = Attendee::factory()->create([
            'youth_camp_id' => $camp->id,
            'local_church_id' => $church->id,
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'birthday' => '2008-04-03',
        ]);
        $removed = Attendee::factory()->create(['youth_camp_id' => $camp->id, 'local_church_id' => $church->id]);
        $removed->delete();
        Attendee::factory()->create(['youth_camp_id' => $lastYear->id, 'local_church_id' => $church->id]);

        $security = User::factory()->create(['role' => 'security']);

        $response = $this->actingAs($security)->getJson('/gate/roster.json')->assertOk()->json();

        $this->assertCount(1, $response);
        $this->assertSame([
            'qr_code' => $juan->qr_code,
            'first_name' => 'Juan',
            'local_church' => 'Grace Church',
            'circuit' => 'Digos City',
        ], $response[0]);
    }

    #[DataProvider('rolesThatCannotDownloadTheRoster')]
    public function test_only_security_and_admin_can_download_the_roster(string $role): void
    {
        YouthCamp::factory()->create(['is_current' => true]);
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/gate/roster.json')->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatCannotDownloadTheRoster(): array
    {
        return [
            'circuit president' => ['circuit_president'],
            'secretary' => ['secretary'],
            'tabulator' => ['tabulator'],
        ];
    }
}
