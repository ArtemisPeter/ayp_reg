<?php

namespace Tests\Feature;

use App\Models\Circuit;
use App\Models\Event;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventScoringTest extends TestCase
{
    use RefreshDatabase;

    private User $tabulator;

    private Event $basketball;

    private Circuit $digos;

    private Circuit $davao;

    protected function setUp(): void
    {
        parent::setUp();

        YouthCamp::factory()->create(['is_current' => true]);
        $this->tabulator = User::factory()->create(['role' => 'tabulator']);
        $this->digos = Circuit::factory()->create(['name' => 'Digos City']);
        $this->davao = Circuit::factory()->create(['name' => 'Davao City']);

        $this->actingAs($this->tabulator)->post('/events', [
            'name' => 'Basketball',
            'ranks' => [
                ['label' => 'Champion', 'points' => 50],
                ['label' => 'L', 'points' => 0],
            ],
        ]);
        $this->basketball = Event::where('name', 'Basketball')->sole();
    }

    public function test_the_tabulator_assigns_a_rank_to_each_circuit_and_sees_the_points(): void
    {
        $champion = $this->basketball->ranks()->where('label', 'Champion')->sole();
        $loss = $this->basketball->ranks()->where('label', 'L')->sole();

        $this->actingAs($this->tabulator)
            ->post("/events/{$this->basketball->id}/score", [
                'circuit_id' => $this->digos->id,
                'event_rank_id' => $champion->id,
            ])
            ->assertRedirect("/events/{$this->basketball->id}/score");

        $this->post("/events/{$this->basketball->id}/score", [
            'circuit_id' => $this->davao->id,
            'event_rank_id' => $loss->id,
        ]);

        $this->get("/events/{$this->basketball->id}/score")
            ->assertSee('Digos City')
            ->assertSee('Davao City')
            ->assertSee('50')
            ->assertSee('Champion');
    }

    public function test_correcting_a_circuits_rank_updates_it_instead_of_duplicating(): void
    {
        $champion = $this->basketball->ranks()->where('label', 'Champion')->sole();
        $loss = $this->basketball->ranks()->where('label', 'L')->sole();

        $this->actingAs($this->tabulator)->post("/events/{$this->basketball->id}/score", [
            'circuit_id' => $this->digos->id,
            'event_rank_id' => $loss->id,
        ]);
        $this->post("/events/{$this->basketball->id}/score", [
            'circuit_id' => $this->digos->id,
            'event_rank_id' => $champion->id,
        ]);

        $this->assertSame(1, $this->basketball->scores()->where('circuit_id', $this->digos->id)->count());
        $this->get("/events/{$this->basketball->id}/score")->assertSee('50');
    }

    #[DataProvider('rolesThatCannotScore')]
    public function test_only_the_tabulator_and_admin_can_score_an_event(string $role): void
    {
        $champion = $this->basketball->ranks()->where('label', 'Champion')->sole();
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get("/events/{$this->basketball->id}/score")->assertForbidden();
        $this->post("/events/{$this->basketball->id}/score", [
            'circuit_id' => $this->digos->id,
            'event_rank_id' => $champion->id,
        ])->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatCannotScore(): array
    {
        return [
            'circuit president' => ['circuit_president'],
            'secretary' => ['secretary'],
            'security' => ['security'],
        ];
    }
}
