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

class AttendeeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Circuit $digos;

    private Circuit $davao;

    protected function setUp(): void
    {
        parent::setUp();

        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $this->digos = Circuit::factory()->create(['name' => 'Digos City']);
        $this->davao = Circuit::factory()->create(['name' => 'Davao City']);

        Attendee::factory()->create([
            'youth_camp_id' => $camp->id,
            'first_name' => 'Juan',
            'local_church_id' => LocalChurch::factory()->create(['circuit_id' => $this->digos->id])->id,
        ]);
        Attendee::factory()->create([
            'youth_camp_id' => $camp->id,
            'first_name' => 'Ramon',
            'local_church_id' => LocalChurch::factory()->create(['circuit_id' => $this->davao->id])->id,
        ]);
    }

    public function test_a_circuit_president_sees_only_their_own_circuits_attendees(): void
    {
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $this->digos->id]);

        $this->actingAs($president)->get('/attendees')
            ->assertOk()
            ->assertSee('Juan')
            ->assertDontSee('Ramon');
    }

    public function test_the_list_shows_only_the_current_camps_attendees(): void
    {
        $lastYear = YouthCamp::factory()->create(['year' => 2025, 'is_current' => false]);
        Attendee::factory()->create([
            'youth_camp_id' => $lastYear->id,
            'first_name' => 'Old-Timer',
            'local_church_id' => LocalChurch::factory()->create(['circuit_id' => $this->digos->id])->id,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'secretary']))->get('/attendees')
            ->assertSee('Juan')
            ->assertDontSee('Old-Timer');
    }

    public function test_searching_by_name_narrows_the_list(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretary']))->get('/attendees?search=ram')
            ->assertOk()
            ->assertSee('Ramon')
            ->assertDontSee('Juan');
    }

    public function test_a_circuit_presidents_search_never_reaches_another_circuit(): void
    {
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $this->digos->id]);

        $this->actingAs($president)->get('/attendees?search=Ramon')
            ->assertOk()
            ->assertViewHas('attendees', fn ($attendees) => $attendees->isEmpty());
    }

    public function test_a_long_list_is_split_into_pages_of_fifty(): void
    {
        Attendee::factory()->count(60)->create([
            'youth_camp_id' => YouthCamp::where('is_current', true)->value('id'),
            'local_church_id' => LocalChurch::factory()->create(['circuit_id' => $this->digos->id])->id,
        ]);
        $secretary = User::factory()->create(['role' => 'secretary']);

        $this->actingAs($secretary)->get('/attendees')
            ->assertViewHas('attendees', fn ($attendees) => $attendees->count() === 50 && $attendees->total() === 62);

        $this->get('/attendees?page=2')
            ->assertViewHas('attendees', fn ($attendees) => $attendees->count() === 12);
    }

    #[DataProvider('rolesThatSeeEveryone')]
    public function test_the_secretary_and_the_admin_see_every_attendee(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get('/attendees')
            ->assertOk()
            ->assertSee('Juan')
            ->assertSee('Ramon');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatSeeEveryone(): array
    {
        return ['secretary' => ['secretary'], 'admin' => ['admin']];
    }

    #[DataProvider('rolesThatSeeNobody')]
    public function test_security_and_the_tabulator_cannot_see_attendee_details(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get('/attendees')->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatSeeNobody(): array
    {
        return ['security' => ['security'], 'tabulator' => ['tabulator']];
    }
}
