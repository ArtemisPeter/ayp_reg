<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GateScannerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_can_open_the_scanner_page(): void
    {
        $security = User::factory()->create(['role' => 'security']);

        $this->actingAs($security)->get('/gate')
            ->assertOk()
            ->assertSee('IN')
            ->assertSee('OUT');
    }

    #[DataProvider('rolesThatCannotOpenTheScanner')]
    public function test_only_security_and_admin_can_open_the_scanner_page(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get('/gate')->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesThatCannotOpenTheScanner(): array
    {
        return [
            'circuit president' => ['circuit_president'],
            'secretary' => ['secretary'],
            'tabulator' => ['tabulator'],
        ];
    }
}
