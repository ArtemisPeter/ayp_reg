<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalChurchManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_an_admin_adds_a_local_church_under_a_circuit_and_sees_it_in_that_circuits_list(): void
    {
        $circuit = Circuit::factory()->create(['name' => 'Digos City']);

        $this->actingAs($this->admin())
            ->post("/admin/circuits/{$circuit->id}/churches", ['name' => 'Sto. Nino Church'])
            ->assertRedirect("/admin/circuits/{$circuit->id}/churches");

        $this->get("/admin/circuits/{$circuit->id}/churches")
            ->assertSee('Digos City')
            ->assertSee('Sto. Nino Church');
    }

    public function test_a_church_name_is_unique_within_its_circuit_but_may_repeat_in_another(): void
    {
        $admin = $this->admin();
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        $davao = Circuit::factory()->create(['name' => 'Davao City']);
        $this->actingAs($admin)->post("/admin/circuits/{$digos->id}/churches", ['name' => 'Grace Church']);

        $this->from("/admin/circuits/{$digos->id}/churches")
            ->post("/admin/circuits/{$digos->id}/churches", ['name' => 'grace church'])
            ->assertSessionHasErrors('name');

        $this->post("/admin/circuits/{$davao->id}/churches", ['name' => 'Grace Church'])
            ->assertSessionHasNoErrors();
        $this->get("/admin/circuits/{$davao->id}/churches")->assertSee('Grace Church');
    }

    public function test_an_admin_renames_a_local_church(): void
    {
        $circuit = Circuit::factory()->create();
        $circuit->localChurches()->create(['name' => 'Grace Chruch']);
        $church = $circuit->localChurches()->first();

        $this->actingAs($this->admin())
            ->put("/admin/circuits/{$circuit->id}/churches/{$church->id}", ['name' => 'Grace Church'])
            ->assertRedirect("/admin/circuits/{$circuit->id}/churches");

        $this->get("/admin/circuits/{$circuit->id}/churches")
            ->assertSee('Grace Church')
            ->assertDontSee('Grace Chruch');
    }

    public function test_an_admin_removes_a_local_church_and_the_name_can_be_reused(): void
    {
        $admin = $this->admin();
        $circuit = Circuit::factory()->create();
        $circuit->localChurches()->create(['name' => 'Grace Church']);
        $church = $circuit->localChurches()->first();

        $this->actingAs($admin)
            ->delete("/admin/circuits/{$circuit->id}/churches/{$church->id}")
            ->assertRedirect("/admin/circuits/{$circuit->id}/churches");

        $this->get("/admin/circuits/{$circuit->id}/churches")->assertDontSee('Grace Church');

        $this->post("/admin/circuits/{$circuit->id}/churches", ['name' => 'Grace Church'])
            ->assertSessionHasNoErrors();
        $this->get("/admin/circuits/{$circuit->id}/churches")->assertSee('Grace Church');
    }

    public function test_a_circuit_president_adds_a_church_to_their_own_circuit_and_sees_only_their_circuits_churches(): void
    {
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        $davao = Circuit::factory()->create(['name' => 'Davao City']);
        LocalChurch::factory()->create(['circuit_id' => $davao->id, 'name' => 'Davao Grace Church']);
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $digos->id]);

        $this->actingAs($president)
            ->post('/churches', ['name' => 'Sto. Nino Church', 'circuit_id' => $davao->id])
            ->assertRedirect('/churches');

        $this->assertDatabaseHas('local_churches', ['circuit_id' => $digos->id, 'name' => 'Sto. Nino Church']);
        $this->get('/churches')
            ->assertSee('Sto. Nino Church')
            ->assertDontSee('Davao Grace Church');
    }

    public function test_a_circuit_president_cannot_add_a_church_name_already_in_their_circuit(): void
    {
        $circuit = Circuit::factory()->create();
        LocalChurch::factory()->create(['circuit_id' => $circuit->id, 'name' => 'Grace Church']);
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $circuit->id]);

        $this->actingAs($president)
            ->from('/churches')
            ->post('/churches', ['name' => 'grace church'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, $circuit->localChurches()->count());
    }

    public function test_a_circuit_president_cannot_rename_or_remove_a_church(): void
    {
        $circuit = Circuit::factory()->create();
        $church = LocalChurch::factory()->create(['circuit_id' => $circuit->id, 'name' => 'Grace Church']);
        $president = User::factory()->create(['role' => 'circuit_president', 'circuit_id' => $circuit->id]);

        $this->actingAs($president)
            ->put("/admin/circuits/{$circuit->id}/churches/{$church->id}", ['name' => 'Renamed'])
            ->assertForbidden();
        $this->delete("/admin/circuits/{$circuit->id}/churches/{$church->id}")->assertForbidden();

        $this->get('/churches')->assertDontSee('Save')->assertDontSee('Remove');
        $this->assertDatabaseHas('local_churches', ['id' => $church->id, 'name' => 'Grace Church', 'deleted_at' => null]);
    }

    public function test_other_staff_cannot_use_the_circuit_presidents_church_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretary']))
            ->post('/churches', ['name' => 'Grace Church'])
            ->assertForbidden();

        $this->assertDatabaseCount('local_churches', 0);
    }

    public function test_a_local_church_with_registered_attendees_cannot_be_removed(): void
    {
        $circuit = Circuit::factory()->create();
        $church = LocalChurch::factory()->create(['circuit_id' => $circuit->id, 'name' => 'Grace Church']);
        Attendee::factory()->create(['local_church_id' => $church->id]);

        $this->actingAs($this->admin())
            ->delete("/admin/circuits/{$circuit->id}/churches/{$church->id}")
            ->assertRedirect("/admin/circuits/{$circuit->id}/churches")
            ->assertSessionHasErrors('name');

        $this->get("/admin/circuits/{$circuit->id}/churches")->assertSee('Grace Church');
    }
}
