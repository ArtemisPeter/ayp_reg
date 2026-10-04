<?php

namespace Tests\Feature;

use App\CampId\QrCodeSvg;
use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CampIdPrintingTest extends TestCase
{
    use RefreshDatabase;

    private Circuit $digos;

    private Circuit $davao;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        YouthCamp::factory()->create(['year' => 2026, 'is_current' => true]);
        $this->digos = Circuit::factory()->create(['name' => 'Digos City', 'colour' => '#1E90FF']);
        $this->davao = Circuit::factory()->create(['name' => 'Davao City', 'colour' => '#FF4500']);
        $this->secretary = User::factory()->create(['role' => 'secretary']);
    }

    private function attendeeIn(Circuit $circuit, string $first, string $last, array $overrides = []): Attendee
    {
        return Attendee::factory()->create($overrides + [
            'youth_camp_id' => YouthCamp::first()->id,
            'local_church_id' => LocalChurch::factory()->create(['circuit_id' => $circuit->id])->id,
            'first_name' => $first,
            'last_name' => $last,
        ]);
    }

    public function test_the_backs_carry_only_each_attendees_random_qr_id_in_mirrored_order_for_double_sided_printing(): void
    {
        $qr = new class implements QrCodeSvg
        {
            /** @var array<int, string> */
            public array $payloads = [];

            public function render(string $payload): string
            {
                $this->payloads[] = $payload;

                return '<svg data-payload="'.e($payload).'"></svg>';
            }
        };
        $this->app->instance(QrCodeSvg::class, $qr);

        $ana = $this->attendeeIn($this->digos, 'Ana', 'Aquino');
        $ben = $this->attendeeIn($this->digos, 'Ben', 'Bautista');
        $carl = $this->attendeeIn($this->digos, 'Carl', 'Cruz');
        $dan = $this->attendeeIn($this->digos, 'Dan', 'Dizon');
        $eli = $this->attendeeIn($this->digos, 'Eli', 'Estrada');

        $this->actingAs($this->secretary)
            ->get("/camp-ids/{$this->digos->id}/backs")
            ->assertOk()
            ->assertDontSee('Ana')
            ->assertDontSee('Aquino');

        $this->assertSame(
            [$ben->qr_code, $ana->qr_code, $dan->qr_code, $carl->qr_code, $eli->qr_code],
            $qr->payloads,
        );
    }

    public function test_marking_cards_as_printed_removes_only_those_from_the_pending_list(): void
    {
        $juan = $this->attendeeIn($this->digos, 'Juan', 'Cruz');
        $this->attendeeIn($this->digos, 'Ramon', 'Bautista');

        $this->actingAs($this->secretary)
            ->post("/camp-ids/{$this->digos->id}/printed", ['attendee_ids' => [$juan->id]])
            ->assertRedirect('/camp-ids');

        $this->get("/camp-ids/{$this->digos->id}/fronts")
            ->assertDontSee('Juan')
            ->assertSee('Ramon');
    }

    public function test_a_card_flagged_for_reprint_reappears_until_it_is_printed_again(): void
    {
        $juan = $this->attendeeIn($this->digos, 'Juan', 'Cruz', ['printed_at' => now(), 'needs_reprint' => true]);
        $this->attendeeIn($this->digos, 'Ramon', 'Bautista', ['printed_at' => now(), 'needs_reprint' => false]);

        $this->actingAs($this->secretary)
            ->get("/camp-ids/{$this->digos->id}/fronts")
            ->assertSee('Juan')
            ->assertDontSee('Ramon');

        $this->post("/camp-ids/{$this->digos->id}/printed", ['attendee_ids' => [$juan->id]]);

        $this->get("/camp-ids/{$this->digos->id}/fronts")->assertDontSee('Juan');
    }

    public function test_the_secretary_sees_each_circuit_with_how_many_cards_are_waiting_to_be_printed(): void
    {
        $this->attendeeIn($this->digos, 'Juan', 'Cruz');
        $this->attendeeIn($this->digos, 'Maria', 'Santos');
        $this->attendeeIn($this->davao, 'Ramon', 'Bautista', ['printed_at' => now()]);

        $this->actingAs($this->secretary)
            ->get('/camp-ids')
            ->assertOk()
            ->assertViewHas('circuits', fn ($circuits) => $circuits->pluck('pending_count', 'name')->all() === [
                'Davao City' => 0,
                'Digos City' => 2,
            ]);
    }

    public function test_the_secretary_can_print_a_single_attendees_card_front_and_back(): void
    {
        $qr = new class implements QrCodeSvg
        {
            public function render(string $payload): string
            {
                return '<svg data-payload="'.e($payload).'"></svg>';
            }
        };
        $this->app->instance(QrCodeSvg::class, $qr);
        $juan = $this->attendeeIn($this->digos, 'Juan', 'Cruz');
        $this->attendeeIn($this->digos, 'Ramon', 'Bautista');

        $this->actingAs($this->secretary)
            ->get("/attendees/{$juan->id}/camp-id")
            ->assertOk()
            ->assertSee('Juan')
            ->assertSee('Digos City')
            ->assertSee('data-payload="'.$juan->qr_code.'"', false)
            ->assertDontSee('Cruz')
            ->assertDontSee('Ramon');
    }

    #[DataProvider('rolesThatCannotPrint')]
    public function test_only_the_secretary_and_admin_can_print_camp_ids(string $role): void
    {
        $juan = $this->attendeeIn($this->digos, 'Juan', 'Cruz');
        $user = User::factory()->create(['role' => $role, 'circuit_id' => $role === 'circuit_president' ? $this->digos->id : null]);

        $this->actingAs($user)->get("/camp-ids/{$this->digos->id}/fronts")->assertForbidden();
        $this->get("/camp-ids/{$this->digos->id}/backs")->assertForbidden();
        $this->post("/camp-ids/{$this->digos->id}/printed", ['attendee_ids' => [$juan->id]])->assertForbidden();
        $this->get('/camp-ids')->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatCannotPrint(): array
    {
        return [
            'circuit president' => ['circuit_president'],
            'security' => ['security'],
            'tabulator' => ['tabulator'],
        ];
    }

    public function test_the_fronts_page_shows_only_first_name_and_circuit_in_its_colour_for_that_circuit(): void
    {
        $this->attendeeIn($this->digos, 'Juan', 'Cruz');
        $this->attendeeIn($this->davao, 'Ramon', 'Bautista');

        $this->actingAs($this->secretary)
            ->get("/camp-ids/{$this->digos->id}/fronts")
            ->assertOk()
            ->assertSee('Juan')
            ->assertSee('Digos City')
            ->assertSee('#1E90FF')
            ->assertDontSee('Cruz')
            ->assertDontSee('Ramon');
    }
}
