<?php

namespace Tests\Feature;

use App\Enums\AttendeeType;
use App\Enums\RegistrationType;
use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosterFilterAndExportTest extends TestCase
{
    use RefreshDatabase;

    private Circuit $banga;

    private LocalChurch $lamba;

    private LocalChurch $grace;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $this->banga = Circuit::factory()->create(['name' => 'Banga Circuit']);
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        $this->lamba = LocalChurch::factory()->create(['circuit_id' => $this->banga->id, 'name' => 'Lamba Alliance Church']);
        $this->grace = LocalChurch::factory()->create(['circuit_id' => $digos->id, 'name' => 'Grace Church']);

        Attendee::factory()->create([
            'youth_camp_id' => $camp->id, 'local_church_id' => $this->lamba->id,
            'first_name' => 'Kate', 'last_name' => 'Niquit', 'birthday' => '2005-11-16',
            'is_baptized' => true, 'type' => AttendeeType::Youth, 'registration_type' => RegistrationType::PreRegistration,
        ]);
        Attendee::factory()->create([
            'youth_camp_id' => $camp->id, 'local_church_id' => $this->lamba->id,
            'first_name' => 'Nathan', 'last_name' => 'Cambronero', 'birthday' => '2014-03-02',
            'is_baptized' => false, 'type' => AttendeeType::Youth, 'registration_type' => RegistrationType::OnSite,
        ]);
        Attendee::factory()->create([
            'youth_camp_id' => $camp->id, 'local_church_id' => $this->grace->id,
            'first_name' => 'Ramon', 'last_name' => 'Reyes', 'birthday' => '1980-01-10',
            'is_baptized' => true, 'type' => AttendeeType::Pastor, 'registration_type' => RegistrationType::PreRegistration,
        ]);

        $this->secretary = User::factory()->create(['role' => 'secretary']);
    }

    public function test_the_roster_is_narrowed_by_circuit_church_and_each_column(): void
    {
        $this->actingAs($this->secretary);

        $this->get("/attendees?circuit={$this->banga->id}")->assertSee('Niquit')->assertSee('Cambronero')->assertDontSee('Reyes');
        $this->get("/attendees?church={$this->grace->id}")->assertSee('Reyes')->assertDontSee('Niquit');
        $this->get('/attendees?baptismal=no')->assertSee('Cambronero')->assertDontSee('Niquit')->assertDontSee('Reyes');
        $this->get('/attendees?type=pastor')->assertSee('Reyes')->assertDontSee('Niquit');
        $this->get('/attendees?registration=on_site')->assertSee('Cambronero')->assertDontSee('Niquit');
        $this->get("/attendees?circuit={$this->banga->id}&baptismal=yes")->assertSee('Niquit')->assertDontSee('Cambronero')->assertDontSee('Reyes');
    }

    public function test_the_csv_export_holds_only_the_filtered_rows_in_the_upload_template_columns(): void
    {
        $response = $this->actingAs($this->secretary)->get("/attendees/export/csv?circuit={$this->banga->id}&baptismal=yes");

        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('"First Name","Middle Name","Last Name","Church Name","Circuit Name",Birthday,Baptismal,Type,Registration', $csv);
        $this->assertStringContainsString('Kate,,Niquit,"Lamba Alliance Church","Banga Circuit",11/16/2005,Yes,Y,Pre-Registration', $csv);
        $this->assertStringNotContainsString('Cambronero', $csv);
        $this->assertStringNotContainsString('Reyes', $csv);
    }

    public function test_a_name_that_looks_like_a_spreadsheet_formula_is_exported_as_plain_text(): void
    {
        Attendee::first()->update(['first_name' => '=HYPERLINK("http://evil.test")']);

        $csv = $this->actingAs($this->secretary)->get('/attendees/export/csv')->streamedContent();

        $this->assertStringContainsString('"\'=HYPERLINK(""http://evil.test"")"', $csv);
    }

    public function test_the_pdf_export_lists_the_filtered_rows_and_names_the_filters(): void
    {
        $this->actingAs($this->secretary)->get('/attendees/export/pdf?type=pastor')
            ->assertOk()
            ->assertSee('Type: Pastor')
            ->assertSee('Reyes')
            ->assertDontSee('Niquit');
    }

    public function test_a_circuit_presidents_exports_never_include_other_circuits(): void
    {
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $this->banga->id]);
        $this->actingAs($president);

        $csv = $this->get("/attendees/export/csv?church={$this->grace->id}")->streamedContent();
        $this->assertStringNotContainsString('Reyes', $csv);

        $this->get('/attendees/export/pdf')->assertSee('Niquit')->assertDontSee('Reyes');
    }

    public function test_roles_without_roster_access_cannot_export(): void
    {
        $security = User::factory()->create(['role' => 'security']);

        $this->actingAs($security)->get('/attendees/export/csv')->assertForbidden();
        $this->actingAs($security)->get('/attendees/export/pdf')->assertForbidden();
    }
}
