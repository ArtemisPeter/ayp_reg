<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignInTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_signs_in_with_username_and_password_and_reaches_the_dashboard(): void
    {
        User::factory()->create(['username' => 'juan.admin', 'password' => 'secret-pass-1']);

        $this->post('/login', ['username' => 'juan.admin', 'password' => 'secret-pass-1'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }

    public function test_a_wrong_password_is_rejected_and_the_dashboard_stays_closed(): void
    {
        User::factory()->create(['username' => 'juan.admin', 'password' => 'secret-pass-1']);

        $this->from('/login')
            ->post('/login', ['username' => 'juan.admin', 'password' => 'not-the-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_repeated_wrong_passwords_lock_that_username_out_even_with_the_right_password(): void
    {
        User::factory()->create(['username' => 'juan.admin', 'password' => 'secret-pass-1']);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['username' => 'juan.admin', 'password' => 'not-the-password']);
        }

        $this->from('/login')
            ->post('/login', ['username' => 'juan.admin', 'password' => 'secret-pass-1'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_a_lockout_on_one_username_does_not_block_another_user_on_the_same_network(): void
    {
        User::factory()->create(['username' => 'maria.sec', 'password' => 'secret-pass-2']);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['username' => 'juan.admin', 'password' => 'not-the-password']);
        }

        $this->post('/login', ['username' => 'maria.sec', 'password' => 'secret-pass-2'])
            ->assertRedirect('/dashboard');
    }

    public function test_trying_many_usernames_from_one_address_locks_that_address_out(): void
    {
        User::factory()->create(['username' => 'maria.sec', 'password' => 'secret-pass-2']);

        foreach (range(1, 30) as $attempt) {
            $this->post('/login', ['username' => "guess.{$attempt}", 'password' => 'Password1']);
        }

        $this->from('/login')
            ->post('/login', ['username' => 'maria.sec', 'password' => 'secret-pass-2'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_a_password_reset_signs_the_user_out_of_sessions_already_open(): void
    {
        $user = User::factory()->create(['username' => 'juan.admin', 'password' => 'secret-pass-1']);

        $this->post('/login', ['username' => 'juan.admin', 'password' => 'secret-pass-1']);
        $this->get('/dashboard')->assertOk();

        $user->update(['password' => 'new-pass-22']);
        $this->app['auth']->forgetGuards();

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_signing_out_closes_the_session(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect('/login');

        $this->get('/dashboard')->assertRedirect('/login');
    }
}
