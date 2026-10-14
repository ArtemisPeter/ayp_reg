<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CircuitsAndUsersSeeder;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{class-string}>
     */
    public static function demoSeeders(): array
    {
        return [
            'demo scenario' => [DemoScenarioSeeder::class],
            'circuits and users' => [CircuitsAndUsersSeeder::class],
        ];
    }

    /**
     * @param  class-string  $seeder
     */
    #[DataProvider('demoSeeders')]
    public function test_demo_accounts_with_the_published_password_are_never_created_in_production(string $seeder): void
    {
        $this->app['env'] = 'production';

        try {
            $this->app->make($seeder)->run();
            $this->fail('The seeder ran in production.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('must never run in production', $exception->getMessage());
        }

        $this->assertSame(0, User::count());
    }
}
