<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function attendeeIn(LocalChurch $church, array $overrides = []): Attendee
    {
        return Attendee::factory()->create($overrides + ['local_church_id' => $church->id]);
    }

    public function test_the_admin_sees_district_wide_registration_totals(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        $davao = Circuit::factory()->create(['name' => 'Davao City']);
        $digosChurch = LocalChurch::factory()->create(['circuit_id' => $digos->id]);
        $davaoChurch = LocalChurch::factory()->create(['circuit_id' => $davao->id]);

        $this->attendeeIn($digosChurch, ['youth_camp_id' => $camp->id, 'type' => 'youth', 'registration_type' => 'pre_registration', 'is_baptized' => true]);
        $this->attendeeIn($digosChurch, ['youth_camp_id' => $camp->id, 'type' => 'youth', 'registration_type' => 'on_site', 'is_baptized' => false]);
        $this->attendeeIn($davaoChurch, ['youth_camp_id' => $camp->id, 'type' => 'pastor', 'registration_type' => 'pre_registration', 'is_baptized' => true]);
        $this->attendeeIn($davaoChurch, ['youth_camp_id' => $camp->id, 'type' => 'youth', 'registration_type' => 'pre_registration', 'is_baptized' => true])->delete();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('overview', fn ($overview) => $overview['total'] === 3
                && $overview['youth'] === 2
                && $overview['pastors'] === 1
                && $overview['preRegistration'] === 2
                && $overview['onSite'] === 1
                && $overview['baptized'] === 2);
    }

    public function test_the_secretary_also_sees_district_wide_totals(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $circuit = Circuit::factory()->create();
        $church = LocalChurch::factory()->create(['circuit_id' => $circuit->id]);
        $this->attendeeIn($church, ['youth_camp_id' => $camp->id]);

        $secretary = User::factory()->create(['role' => 'secretary']);

        $this->actingAs($secretary)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('overview', fn ($overview) => $overview['total'] === 1);
    }

    public function test_past_camp_attendees_are_excluded(): void
    {
        $currentCamp = YouthCamp::factory()->create(['is_current' => true]);
        $pastCamp = YouthCamp::factory()->create(['is_current' => false]);
        $circuit = Circuit::factory()->create();
        $church = LocalChurch::factory()->create(['circuit_id' => $circuit->id]);
        $this->attendeeIn($church, ['youth_camp_id' => $currentCamp->id]);
        $this->attendeeIn($church, ['youth_camp_id' => $pastCamp->id]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/dashboard')
            ->assertViewHas('overview', fn ($overview) => $overview['total'] === 1);
    }

    public function test_a_circuit_president_only_sees_their_own_circuit_totals(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $ownCircuit = Circuit::factory()->create();
        $otherCircuit = Circuit::factory()->create();
        $ownChurch = LocalChurch::factory()->create(['circuit_id' => $ownCircuit->id]);
        $otherChurch = LocalChurch::factory()->create(['circuit_id' => $otherCircuit->id]);

        $this->attendeeIn($ownChurch, ['youth_camp_id' => $camp->id]);
        $this->attendeeIn($otherChurch, ['youth_camp_id' => $camp->id]);
        $this->attendeeIn($otherChurch, ['youth_camp_id' => $camp->id]);

        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $ownCircuit->id]);

        $this->actingAs($president)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('overview', fn ($overview) => $overview['total'] === 1)
            ->assertViewMissing('perCircuit');
    }

    public function test_the_admin_sees_a_per_circuit_breakdown_with_printed_camp_id_counts(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $polomolok = Circuit::factory()->create(['name' => 'Polomolok Circuit']);
        $gensan = Circuit::factory()->create(['name' => 'Gensan Circuit']);
        $polomolokChurch = LocalChurch::factory()->create(['circuit_id' => $polomolok->id]);
        $gensanChurch = LocalChurch::factory()->create(['circuit_id' => $gensan->id]);

        $this->attendeeIn($polomolokChurch, ['youth_camp_id' => $camp->id, 'registration_type' => 'pre_registration', 'printed_at' => now()]);
        $this->attendeeIn($polomolokChurch, ['youth_camp_id' => $camp->id, 'registration_type' => 'on_site', 'printed_at' => null]);
        $this->attendeeIn($gensanChurch, ['youth_camp_id' => $camp->id, 'registration_type' => 'pre_registration', 'printed_at' => now(), 'needs_reprint' => true]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('perCircuit', function ($perCircuit) {
                $byName = $perCircuit->keyBy('circuit');

                return $byName['Polomolok Circuit']['total'] === 2
                    && $byName['Polomolok Circuit']['preRegistration'] === 1
                    && $byName['Polomolok Circuit']['onSite'] === 1
                    && $byName['Polomolok Circuit']['printed'] === 1
                    && $byName['Polomolok Circuit']['pending'] === 1
                    && $byName['Gensan Circuit']['total'] === 1
                    && $byName['Gensan Circuit']['printed'] === 0
                    && $byName['Gensan Circuit']['pending'] === 1;
            });
    }

    public function test_security_and_tabulator_do_not_see_the_overview_panel(): void
    {
        $security = User::factory()->create(['role' => 'security']);
        $tabulator = User::factory()->create(['role' => 'tabulator']);

        $this->actingAs($security)->get('/dashboard')->assertOk()->assertViewMissing('overview');
        $this->actingAs($tabulator)->get('/dashboard')->assertOk()->assertViewMissing('overview');
    }
}
