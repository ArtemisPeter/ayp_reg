<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YouthCampManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_an_admin_creates_a_youth_camp_and_sees_it_in_the_list(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/youth-camps', [
                'year' => 2026,
                'theme' => 'Rise and Shine',
                'starts_on' => '2026-12-26',
                'ends_on' => '2026-12-29',
            ])
            ->assertRedirect('/admin/youth-camps');

        $this->get('/admin/youth-camps')
            ->assertSee('Rise and Shine')
            ->assertSee('2026-12-26');
    }

    public function test_an_admin_sets_a_camps_venue_verse_tagline_and_fees(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/youth-camps', [
                'year' => 2026,
                'theme' => 'Rooted',
                'venue' => 'Camp Mariano, Koronadal City',
                'verse_reference' => 'Colossians 2:6-7',
                'tagline' => 'Growing Deeper. Reaching Higher.',
                'pre_registration_fee' => 350,
                'on_site_fee' => 450,
                'starts_on' => '2026-12-27',
                'ends_on' => '2026-12-29',
            ])
            ->assertRedirect('/admin/youth-camps');

        $this->assertDatabaseHas('youth_camps', [
            'theme' => 'Rooted',
            'venue' => 'Camp Mariano, Koronadal City',
            'verse_reference' => 'Colossians 2:6-7',
            'tagline' => 'Growing Deeper. Reaching Higher.',
            'pre_registration_fee' => 350,
            'on_site_fee' => 450,
        ]);
    }

    public function test_an_admin_edits_a_youth_camps_theme_and_dates(): void
    {
        $camp = YouthCamp::factory()->create(['year' => 2026, 'theme' => 'Old Theme']);

        $this->actingAs($this->admin())
            ->put("/admin/youth-camps/{$camp->id}", [
                'year' => 2026,
                'theme' => 'New Theme',
                'starts_on' => '2026-12-27',
                'ends_on' => '2026-12-30',
            ])
            ->assertRedirect('/admin/youth-camps');

        $this->get('/admin/youth-camps')
            ->assertSee('New Theme')
            ->assertDontSee('Old Theme')
            ->assertSee('2026-12-27');
    }

    public function test_a_youth_camp_with_no_attendees_can_be_removed(): void
    {
        $camp = YouthCamp::factory()->create(['year' => 2024, 'is_current' => false]);

        $this->actingAs($this->admin())
            ->delete("/admin/youth-camps/{$camp->id}")
            ->assertRedirect('/admin/youth-camps');

        $this->get('/admin/youth-camps')->assertDontSee('2024');
    }

    public function test_the_current_youth_camp_cannot_be_removed(): void
    {
        $camp = YouthCamp::factory()->create(['year' => 2026, 'is_current' => true]);

        $this->actingAs($this->admin())
            ->from('/admin/youth-camps')
            ->delete("/admin/youth-camps/{$camp->id}")
            ->assertRedirect('/admin/youth-camps')
            ->assertSessionHasErrors();

        $this->get('/admin/youth-camps')->assertSee('2026');
    }

    public function test_a_youth_camp_with_attendees_cannot_be_removed(): void
    {
        $camp = YouthCamp::factory()->create(['year' => 2025, 'is_current' => false]);
        $circuit = Circuit::factory()->create();
        Attendee::factory()->create([
            'youth_camp_id' => $camp->id,
            'local_church_id' => LocalChurch::factory()->create(['circuit_id' => $circuit->id])->id,
        ]);

        $this->actingAs($this->admin())
            ->from('/admin/youth-camps')
            ->delete("/admin/youth-camps/{$camp->id}")
            ->assertRedirect('/admin/youth-camps')
            ->assertSessionHasErrors();

        $this->get('/admin/youth-camps')->assertSee('2025');
    }

    public function test_marking_a_camp_as_current_makes_it_the_only_current_one(): void
    {
        $admin = $this->admin();
        $previous = YouthCamp::factory()->create(['year' => 2025]);
        $next = YouthCamp::factory()->create(['year' => 2026]);

        $this->actingAs($admin)->post("/admin/youth-camps/{$previous->id}/current");
        $this->post("/admin/youth-camps/{$next->id}/current")
            ->assertRedirect('/admin/youth-camps');

        $this->get('/admin/youth-camps')->assertViewHas(
            'youthCamps',
            fn ($camps) => $camps->where('is_current', true)->pluck('year')->all() === [2026],
        );
    }
}
