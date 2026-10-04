<?php

namespace Tests\Feature;

use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CircuitManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_an_admin_adds_a_circuit_with_a_colour_and_sees_it_in_the_list(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/circuits', ['name' => 'Digos City', 'colour' => '#1E90FF'])
            ->assertRedirect('/admin/circuits');

        $this->get('/admin/circuits')
            ->assertSee('Digos City')
            ->assertSee('#1E90FF');
    }

    public function test_two_circuits_cannot_share_a_name(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/circuits', ['name' => 'Digos City', 'colour' => '#1E90FF']);

        $this->from('/admin/circuits')
            ->post('/admin/circuits', ['name' => 'digos city', 'colour' => '#FF0000'])
            ->assertRedirect('/admin/circuits')
            ->assertSessionHasErrors('name');

        $this->get('/admin/circuits')
            ->assertViewHas('circuits', fn ($circuits) => $circuits->count() === 1);
    }

    public function test_an_admin_renames_a_circuit_and_changes_its_colour(): void
    {
        $circuit = Circuit::factory()->create(['name' => 'Digos', 'colour' => '#111111']);

        $this->actingAs($this->admin())
            ->put("/admin/circuits/{$circuit->id}", ['name' => 'Digos City', 'colour' => '#22AA22'])
            ->assertRedirect('/admin/circuits');

        $this->get('/admin/circuits')
            ->assertSee('Digos City')
            ->assertSee('#22AA22')
            ->assertDontSee('#111111');
    }

    public function test_a_removed_circuit_disappears_from_the_list_and_its_name_can_be_reused(): void
    {
        $admin = $this->admin();
        $circuit = Circuit::factory()->create(['name' => 'Digos City']);

        $this->actingAs($admin)
            ->delete("/admin/circuits/{$circuit->id}")
            ->assertRedirect('/admin/circuits');

        $this->get('/admin/circuits')->assertDontSee('Digos City');

        $this->post('/admin/circuits', ['name' => 'Digos City', 'colour' => '#1E90FF'])
            ->assertSessionHasNoErrors();
        $this->get('/admin/circuits')->assertSee('Digos City');
    }

    public function test_a_circuit_that_still_has_local_churches_cannot_be_removed(): void
    {
        $circuit = Circuit::factory()->create(['name' => 'Digos City']);
        LocalChurch::factory()->create(['circuit_id' => $circuit->id]);

        $this->actingAs($this->admin())
            ->delete("/admin/circuits/{$circuit->id}")
            ->assertRedirect('/admin/circuits')
            ->assertSessionHasErrors('name');

        $this->get('/admin/circuits')->assertSee('Digos City');
    }

    public function test_a_circuit_that_still_has_a_user_account_cannot_be_removed(): void
    {
        $circuit = Circuit::factory()->create(['name' => 'Digos City']);
        User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $circuit->id]);

        $this->actingAs($this->admin())
            ->delete("/admin/circuits/{$circuit->id}")
            ->assertRedirect('/admin/circuits')
            ->assertSessionHasErrors('name');

        $this->get('/admin/circuits')->assertSee('Digos City');
    }
}
