<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Circuit;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Just the real SMD Circuits and one login per role — nothing else. No Youth Camp, Local
 * Churches, Attendees, Events or Gate Scans; use DemoScenarioSeeder instead for a fully
 * populated scenario to click through.
 *
 * Safe to re-run: Circuits are matched by name and updated in place (updateOrCreate), and
 * accounts are matched by username and updated in place too, so re-running never duplicates
 * anything — it just resets names, colours, roles and passwords back to these values.
 */
class CircuitsAndUsersSeeder extends Seeder
{
    private const PASSWORD = 'Demo-Pass-1';

    /**
     * The real South Mindanao District Circuits, with a distinct colour assigned to each for
     * their Camp IDs.
     *
     * @var array<string, string>
     */
    private const CIRCUITS = [
        'Polomolok Circuit' => '#696cff',
        'Gensan Circuit' => '#71dd37',
        'TuTam Circuit' => '#ffab00',
        'KorLuTan Circuit' => '#ff3e1d',
        'NorSan Circuit' => '#03c3ec',
        'Banga Circuit' => '#8592a3',
        'Surallah Circuit' => '#e83e8c',
    ];

    public function run(): void
    {
        $circuits = collect(self::CIRCUITS)->map(
            fn (string $colour, string $name) => Circuit::updateOrCreate(['name' => $name], ['colour' => $colour]),
        );

        $this->account('Demo Admin', 'demo.admin', Role::Admin);
        $this->account('Demo Secretary', 'demo.secretary', Role::Secretary);
        $this->account('Demo Security', 'demo.security', Role::Security);
        $this->account('Demo Tabulator', 'demo.tabulator', Role::Tabulator);

        $presidentUsernames = $circuits->map(function (Circuit $circuit) {
            $slug = str($circuit->name)->before(' Circuit')->lower()->toString();
            $username = "demo.president.{$slug}";

            $this->account("Demo President {$circuit->name}", $username, Role::CircuitPresident, $circuit->id);

            return $username;
        });

        $this->command?->info('Circuits and accounts ready. Every account password is "'.self::PASSWORD.'".');
        $this->command?->table(['Username', 'Role'], [
            ['demo.admin', 'Admin'],
            ['demo.secretary', 'Secretary'],
            ...$presidentUsernames->map(fn (string $username) => [$username, 'Circuit President'])->all(),
            ['demo.security', 'Security'],
            ['demo.tabulator', 'Tabulator'],
        ]);
    }

    private function account(string $name, string $username, Role $role, ?int $circuitId = null): void
    {
        User::updateOrCreate(['username' => $username], [
            'name' => $name,
            'password' => self::PASSWORD,
            'role' => $role,
            'circuit_id' => $circuitId,
            'must_change_password' => false,
        ]);
    }
}
