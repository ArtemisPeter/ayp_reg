<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccessByRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_open_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_a_visitor_who_is_not_signed_in_is_sent_to_the_login_page(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    #[DataProvider('nonAdminRoles')]
    public function test_every_other_role_is_forbidden_from_user_management(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    public static function nonAdminRoles(): array
    {
        return [
            'secretary' => ['secretary'],
            'circuit president' => ['circuit_president'],
            'security' => ['security'],
            'tabulator' => ['tabulator'],
        ];
    }
}
