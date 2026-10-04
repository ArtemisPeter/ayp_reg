<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SecretaryAttendeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private LocalChurch $grace;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        YouthCamp::factory()->create(['year' => 2026, 'is_current' => true]);
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        $this->grace = LocalChurch::factory()->create(['circuit_id' => $digos->id, 'name' => 'Grace Church']);
        $this->secretary = User::factory()->create(['role' => 'secretary']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function onSiteForm(array $overrides = []): array
    {
        return $overrides + [
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'local_church_id' => $this->grace->id,
            'birthday' => '2008-04-03',
            'baptismal' => 'Yes',
            'type' => 'Y',
        ];
    }

    public function test_the_secretary_registers_an_attendee_on_site_by_form(): void
    {
        $this->actingAs($this->secretary)
            ->post('/attendees', $this->onSiteForm())
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('Cruz')
            ->assertSee('On Site');
    }

    private function registeredJuan(): Attendee
    {
        return Attendee::factory()->create([
            'youth_camp_id' => YouthCamp::first()->id,
            'local_church_id' => $this->grace->id,
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'birthday' => '2008-04-03',
            'is_baptized' => false,
        ]);
    }

    public function test_the_secretary_edits_an_attendee_and_a_birthday_change_needs_no_reprint(): void
    {
        $juan = $this->registeredJuan();

        $this->actingAs($this->secretary)
            ->put("/attendees/{$juan->id}", $this->onSiteForm(['birthday' => '2008-04-04', 'baptismal' => 'Yes']))
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('2008-04-04')
            ->assertDontSee('2008-04-03')
            ->assertDontSee('Camp ID needs reprint');
    }

    public function test_correcting_a_first_name_flags_the_camp_id_for_reprint(): void
    {
        $juan = $this->registeredJuan();

        $this->actingAs($this->secretary)
            ->put("/attendees/{$juan->id}", $this->onSiteForm(['first_name' => 'Juanito']))
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('Juanito')
            ->assertSee('Camp ID needs reprint');
    }

    public function test_a_removed_attendee_leaves_the_list_and_can_be_registered_again(): void
    {
        $juan = $this->registeredJuan();

        $this->actingAs($this->secretary)
            ->delete("/attendees/{$juan->id}")
            ->assertRedirect('/attendees');

        $this->get('/attendees')->assertDontSee('Cruz');

        $this->post('/attendees', $this->onSiteForm())->assertSessionHasNoErrors();
        $this->get('/attendees')->assertSee('Cruz');
    }

    public function test_a_circuit_president_cannot_edit_or_remove_an_attendee(): void
    {
        $juan = $this->registeredJuan();
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $this->grace->circuit_id]);

        $this->actingAs($president)->put("/attendees/{$juan->id}", $this->onSiteForm())->assertForbidden();
        $this->delete("/attendees/{$juan->id}")->assertForbidden();
    }

    public function test_the_secretary_uploads_an_on_site_file_covering_several_circuits(): void
    {
        $davao = Circuit::factory()->create(['name' => 'Davao City']);
        LocalChurch::factory()->create(['circuit_id' => $davao->id, 'name' => 'Faith Church']);

        $file = UploadedFile::fake()->createWithContent('late.csv', implode("\n", [
            'First Name,Middle Name,Last Name,Church Name,Circuit Name,Birthday,Baptismal,Type',
            'Juan,,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y',
            'Ramon,,Bautista,Faith Church,Davao City,09/09/2003,No,Y',
        ])."\n");

        $this->actingAs($this->secretary)
            ->post('/registrations/upload', ['file' => $file])
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('Cruz')
            ->assertSee('Bautista')
            ->assertSee('Davao City')
            ->assertViewHas('attendees', fn ($attendees) => $attendees->every(
                fn ($attendee) => $attendee->registration_type->value === 'on_site',
            ));
    }

    public function test_editing_an_attendee_into_someone_elses_identity_is_rejected(): void
    {
        $juan = $this->registeredJuan();
        Attendee::factory()->create([
            'youth_camp_id' => YouthCamp::first()->id,
            'local_church_id' => $this->grace->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $this->actingAs($this->secretary)
            ->from("/attendees/{$juan->id}/edit")
            ->put("/attendees/{$juan->id}", $this->onSiteForm(['first_name' => 'Maria', 'last_name' => 'Santos']))
            ->assertRedirect("/attendees/{$juan->id}/edit")
            ->assertSessionHasErrors('first_name');

        $this->get('/attendees')->assertSee('Juan')->assertSee('Cruz');
    }

    public function test_a_circuit_president_cannot_register_anyone_on_site(): void
    {
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $this->grace->circuit_id]);

        $this->actingAs($president)->post('/attendees', $this->onSiteForm())->assertForbidden();
    }

    public function test_someone_already_registered_cannot_be_registered_again(): void
    {
        $this->actingAs($this->secretary)->post('/attendees', $this->onSiteForm());

        $this->from('/attendees')
            ->post('/attendees', $this->onSiteForm(['first_name' => ' juan ', 'last_name' => 'CRUZ']))
            ->assertRedirect('/attendees')
            ->assertSessionHasErrors('first_name');

        $this->get('/attendees')
            ->assertViewHas('attendees', fn ($attendees) => $attendees->count() === 1);
    }
}
