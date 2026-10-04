<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $tabulator;

    protected function setUp(): void
    {
        parent::setUp();

        YouthCamp::factory()->create(['is_current' => true]);
        $this->tabulator = User::factory()->create(['role' => 'tabulator']);
    }

    public function test_the_tabulator_creates_an_event_with_ranks_and_points(): void
    {
        $this->actingAs($this->tabulator)
            ->post('/events', [
                'name' => 'Basketball',
                'ranks' => [
                    ['label' => 'Champion', 'points' => 50],
                    ['label' => '1st Runner-up', 'points' => 30],
                    ['label' => 'L', 'points' => 0],
                ],
            ])
            ->assertRedirect('/events');

        $this->get('/events')
            ->assertSee('Basketball')
            ->assertSee('Champion')
            ->assertSee('50')
            ->assertSee('1st Runner-up')
            ->assertSee('30');
    }

    #[DataProvider('rolesThatCannotManageEvents')]
    public function test_only_the_tabulator_and_admin_can_manage_events(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get('/events')->assertForbidden();
        $this->post('/events', ['name' => 'X', 'ranks' => [['label' => 'Champion', 'points' => 10]]])->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatCannotManageEvents(): array
    {
        return [
            'circuit president' => ['circuit_president'],
            'secretary' => ['secretary'],
            'security' => ['security'],
        ];
    }
}
