<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\Event;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_shows_the_current_camps_theme_year_and_dates(): void
    {
        YouthCamp::factory()->create([
            'year' => 2026,
            'theme' => 'Rise and Shine',
            'starts_on' => '2026-12-26',
            'ends_on' => '2026-12-29',
            'is_current' => true,
        ]);
        YouthCamp::factory()->create(['year' => 2025, 'theme' => 'Last Years Theme', 'is_current' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rise and Shine')
            ->assertSee('2026')
            ->assertSee('Dec 26')
            ->assertSee('29, 2026')
            ->assertDontSee('Last Years Theme');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function youthIn(Circuit $circuit, YouthCamp $camp, array $attributes = []): Attendee
    {
        return Attendee::factory()->create($attributes + [
            'youth_camp_id' => $camp->id,
            'local_church_id' => LocalChurch::factory()->create(['circuit_id' => $circuit->id])->id,
        ]);
    }

    public function test_the_youth_count_leaderboard_counts_current_camp_youth_only_and_lists_empty_circuits_last(): void
    {
        $camp = YouthCamp::factory()->create(['year' => 2026, 'is_current' => true]);
        $lastYear = YouthCamp::factory()->create(['year' => 2025, 'is_current' => false]);
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        $davao = Circuit::factory()->create(['name' => 'Davao City']);
        Circuit::factory()->create(['name' => 'Zamboanga City']);

        $this->youthIn($digos, $camp);
        $this->youthIn($digos, $camp, ['registration_type' => 'on_site']);
        $this->youthIn($digos, $camp, ['type' => 'pastor']);
        $this->youthIn($digos, $camp)->delete();
        $this->youthIn($davao, $camp);
        $this->youthIn($davao, $lastYear);
        $this->youthIn($davao, $lastYear);

        // The Youth-count leaderboard isn't shown on the public page (contest leaderboard only,
        // by design), but the controller still computes it, in case it's surfaced elsewhere later.
        $this->get('/')
            ->assertOk()
            ->assertViewHas('leaderboard', fn ($rows) => $rows
                ->map(fn ($row) => [$row['circuit'], $row['youth']])
                ->all() === [['Digos City', 2], ['Davao City', 1], ['Zamboanga City', 0]]);
    }

    public function test_tied_circuits_share_a_rank_are_listed_alphabetically_and_marked_tie(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $standings = ['Zamboanga City' => 3, 'Digos City' => 2, 'Davao City' => 2, 'Cebu City' => 1];

        foreach ($standings as $name => $youth) {
            $circuit = Circuit::factory()->create(['name' => $name]);
            foreach (range(1, $youth) as $ignored) {
                $this->youthIn($circuit, $camp);
            }
        }

        $this->get('/')
            ->assertViewHas('leaderboard', fn ($rows) => $rows
                ->map(fn ($row) => [$row['circuit'], $row['rank'], $row['tie']])
                ->all() === [
                    ['Zamboanga City', 1, false],
                    ['Davao City', 2, true],
                    ['Digos City', 2, true],
                    ['Cebu City', 4, false],
                ]);
    }

    public function test_circuits_with_no_youth_yet_are_not_ranked_or_marked_as_tied(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $this->youthIn(Circuit::factory()->create(['name' => 'Digos City']), $camp);
        Circuit::factory()->create(['name' => 'Davao City']);
        Circuit::factory()->create(['name' => 'Cebu City']);

        $this->get('/')
            ->assertViewHas('leaderboard', fn ($rows) => $rows
                ->map(fn ($row) => [$row['circuit'], $row['rank'], $row['tie']])
                ->all() === [
                    ['Digos City', 1, false],
                    ['Cebu City', null, false],
                    ['Davao City', null, false],
                ]);
    }

    public function test_the_contest_leaderboard_sums_points_across_events_and_treats_unscored_as_zero(): void
    {
        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        $davao = Circuit::factory()->create(['name' => 'Davao City']);
        Circuit::factory()->create(['name' => 'Cebu City']);
        $tabulator = User::factory()->create(['role' => 'tabulator']);

        $this->actingAs($tabulator)->post('/events', [
            'name' => 'Basketball',
            'ranks' => [['label' => 'Champion', 'points' => 50], ['label' => 'L', 'points' => 0]],
        ]);
        $basketball = Event::where('name', 'Basketball')->sole();
        $champion = $basketball->ranks()->where('label', 'Champion')->sole();
        $loss = $basketball->ranks()->where('label', 'L')->sole();

        $this->post("/events/{$basketball->id}/score", ['circuit_id' => $digos->id, 'event_rank_id' => $champion->id]);
        $this->post("/events/{$basketball->id}/score", ['circuit_id' => $davao->id, 'event_rank_id' => $loss->id]);

        $this->actingAs($tabulator)->post('/events', [
            'name' => 'Bible Quiz',
            'ranks' => [['label' => 'Champion', 'points' => 20]],
        ]);
        $quiz = Event::where('name', 'Bible Quiz')->sole();
        $quizChampion = $quiz->ranks()->where('label', 'Champion')->sole();
        $this->post("/events/{$quiz->id}/score", ['circuit_id' => $davao->id, 'event_rank_id' => $quizChampion->id]);

        $this->get('/')
            ->assertViewHas('contestLeaderboard', fn ($rows) => $rows
                ->map(fn ($row) => [$row['circuit'], $row['points']])
                ->all() === [['Digos City', 50], ['Davao City', 20], ['Cebu City', 0]]);
    }

    public function test_the_home_page_counts_down_the_days_until_the_camp_starts(): void
    {
        $this->travelTo('2026-12-20 15:00:00');
        YouthCamp::factory()->create(['starts_on' => '2026-12-27', 'ends_on' => '2026-12-29', 'is_current' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['data-countdown-days>7<', 'days to go'], false)
            ->assertDontSee('Happening now');
    }

    public function test_the_home_page_says_the_camp_is_happening_now_from_its_first_to_its_last_day(): void
    {
        YouthCamp::factory()->create(['starts_on' => '2026-12-27', 'ends_on' => '2026-12-29', 'is_current' => true]);

        foreach (['2026-12-27 00:00:00', '2026-12-29 23:59:00'] as $moment) {
            $this->travelTo($moment);

            $this->get('/')
                ->assertSee('Happening now')
                ->assertDontSee('days to go');
        }
    }

    public function test_the_home_page_says_the_camp_has_ended_after_its_last_day(): void
    {
        $this->travelTo('2026-12-30 00:00:00');
        YouthCamp::factory()->create(['starts_on' => '2026-12-27', 'ends_on' => '2026-12-29', 'is_current' => true]);

        $this->get('/')
            ->assertSee('This camp has ended')
            ->assertDontSee('Happening now')
            ->assertDontSee('days to go');
    }

    public function test_the_home_page_shows_the_2026_poster_and_speaker_only_for_the_2026_camp(): void
    {
        $camp = YouthCamp::factory()->create(['year' => 2026, 'is_current' => true]);

        $this->get('/')
            ->assertSee('Camp at a glance')
            ->assertSee('Rev. Jade T. Diaz')
            ->assertSee('downloads/duyog-26-rooted-camp-guide.pdf');

        $this->assertFileExists(public_path('downloads/duyog-26-rooted-camp-guide.pdf'));

        $camp->update(['year' => 2027]);

        $this->get('/')
            ->assertDontSee('Camp at a glance')
            ->assertDontSee('Rev. Jade T. Diaz');
    }

    public function test_the_home_page_has_a_footer_naming_the_district(): void
    {
        $this->get('/')->assertSee('South Mindanao District, CAMACOP');
    }

    public function test_the_home_page_says_registration_opens_soon_when_no_camp_is_current(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Registration')
            ->assertSee('Opens soon');
    }
}
