<?php

namespace Tests\Feature;

use App\Models\Circuit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_password_without_both_letters_and_numbers_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['password', '12345678'] as $weakPassword) {
            $this->actingAs($admin)
                ->post('/admin/users', [
                    'name' => 'Maria Reyes',
                    'username' => 'maria.secretary',
                    'role' => 'secretary',
                    'password' => $weakPassword,
                ])
                ->assertSessionHasErrors('password');
        }

        $this->assertDatabaseMissing('users', ['username' => 'maria.secretary']);
    }

    public function test_an_admin_creates_a_user_who_appears_in_the_user_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Maria Reyes',
                'username' => 'maria.secretary',
                'role' => 'secretary',
                'password' => 'Temp-Pass-1',
            ])
            ->assertRedirect('/admin/users');

        $this->actingAs($admin)->get('/admin/users')
            ->assertSee('Maria Reyes')
            ->assertSee('maria.secretary');
    }

    public function test_an_admin_creates_a_circuit_president_attached_to_a_circuit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $circuit = Circuit::factory()->create(['name' => 'Digos City']);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Pedro Cruz',
                'username' => 'pedro.digos',
                'role' => 'circuit_president',
                'circuit_id' => $circuit->id,
                'password' => 'Temp-Pass-1',
            ])
            ->assertRedirect('/admin/users');

        $this->get('/admin/users')
            ->assertSee('pedro.digos')
            ->assertSee('Digos City');
    }

    public function test_a_circuit_president_cannot_be_created_without_a_circuit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Pedro Cruz',
                'username' => 'pedro.digos',
                'role' => 'circuit_president',
                'password' => 'Temp-Pass-1',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHasErrors('circuit_id');

        $this->get('/admin/users')
            ->assertViewHas('users', fn ($users) => $users->where('username', 'pedro.digos')->isEmpty());
    }

    public function test_an_admin_cannot_remove_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->delete("/admin/users/{$admin->id}")
            ->assertRedirect('/admin/users')
            ->assertSessionHasErrors();

        $this->get('/admin/users')->assertSee($admin->username);
    }

    public function test_a_new_user_must_replace_the_temporary_password_before_using_the_system(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Maria Reyes',
            'username' => 'maria.secretary',
            'role' => 'secretary',
            'password' => 'Temp-Pass-1',
        ]);
        $this->post('/logout');

        $this->post('/login', ['username' => 'maria.secretary', 'password' => 'Temp-Pass-1']);
        $this->get('/dashboard')->assertRedirect('/password/change');

        $this->post('/password/change', [
            'password' => 'My-Own-Pass-2',
            'password_confirmation' => 'My-Own-Pass-2',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }

    public function test_an_admin_resets_a_forgotten_password_and_the_user_must_change_it_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['username' => 'maria.secretary', 'password' => 'Old-Pass-1']);

        $this->actingAs($admin)
            ->post("/admin/users/{$user->id}/reset-password", ['password' => 'Temp-Pass-9'])
            ->assertRedirect('/admin/users');
        $this->post('/logout');

        $this->post('/login', ['username' => 'maria.secretary', 'password' => 'Old-Pass-1']);
        $this->get('/dashboard')->assertRedirect('/login');

        $this->post('/login', ['username' => 'maria.secretary', 'password' => 'Temp-Pass-9']);
        $this->get('/dashboard')->assertRedirect('/password/change');
    }

    public function test_a_removed_user_disappears_from_the_list_and_can_no_longer_sign_in(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['username' => 'maria.secretary', 'password' => 'Pass-1234']);

        $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->get('/admin/users')->assertDontSee('maria.secretary');
        $this->post('/logout');

        $this->post('/login', ['username' => 'maria.secretary', 'password' => 'Pass-1234']);
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
