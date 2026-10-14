<?php

namespace Database\Seeders;

use App\Enums\AttendeeType;
use App\Enums\GateDirection;
use App\Enums\RegistrationType;
use App\Enums\Role;
use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\Event;
use App\Models\GateScan;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Sets up a scenario for clicking through the app by hand, built on real SMD data where it's
 * been given to us and clearly-labeled "Demo" placeholders everywhere else:
 *
 * - The 2026 Youth Camp (year, theme, venue, dates, fees) is real: DUYOG '26, "ROOTED".
 * - The 7 Circuits are real SMD Circuit names, given directly by the user. They, like the
 *   Youth Camp, are kept (not wiped) on re-run via updateOrCreate.
 * - Everything else — Local Churches, Users, Attendees, Events, Gate Scans — is invented test
 *   data, prefixed "Demo" so it can never be mistaken for the real thing, and safe to re-run:
 *   this seeder deletes its own previous demo rows first every time.
 *
 * Wipe everything, including the real Camp and Circuit records, with `php artisan migrate:fresh`
 * before going live.
 */
class DemoScenarioSeeder extends Seeder
{
    private const PASSWORD = 'Demo-Pass-1';

    /**
     * The real South Mindanao District Circuits, with a distinct colour assigned to each for
     * their Camp IDs. Order matters only for display; it isn't otherwise significant.
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
        if (app()->isProduction()) {
            throw new RuntimeException('Demo seeders create accounts with a published password and must never run in production.');
        }

        $this->wipePreviousRun();

        // Real camp information from the official DUYOG '26 poster.
        $camp = YouthCamp::updateOrCreate(['year' => 2026], [
            'theme' => 'ROOTED',
            'venue' => 'Camp Mariano, Bo. 8, Barangay Saravia, Koronadal City',
            'verse_reference' => 'Colossians 2:6-7',
            'tagline' => 'Growing Deeper. Reaching Higher. Living for Christ.',
            'pre_registration_fee' => 350,
            'on_site_fee' => 450,
            'starts_on' => '2026-12-27',
            'ends_on' => '2026-12-29',
            'is_current' => true,
        ]);

        $pastCamp = YouthCamp::create([
            'year' => 2020,
            'theme' => 'Demo Past Camp',
            'starts_on' => '2020-12-26',
            'ends_on' => '2020-12-29',
            'is_current' => false,
        ]);

        // The real Circuits. Most are left bare — no Local Churches or Attendees — matching
        // what they'll actually look like before the Admin populates them for real. Polomolok,
        // Gensan and Surallah get invented ("Demo") Churches, Attendees, scores and Gate Scans
        // so every role has something to look at immediately.
        $circuits = collect(self::CIRCUITS)->map(
            fn (string $colour, string $name) => Circuit::updateOrCreate(['name' => $name], ['colour' => $colour]),
        );
        $polomolok = $circuits['Polomolok Circuit'];
        $gensan = $circuits['Gensan Circuit'];
        $surallah = $circuits['Surallah Circuit'];

        $polomolokChurchOne = $polomolok->localChurches()->create(['name' => 'Demo Grace Church']);
        $polomolokChurchTwo = $polomolok->localChurches()->create(['name' => 'Demo Faith Church']);
        $gensanChurchOne = $gensan->localChurches()->create(['name' => 'Demo Hope Church']);
        $gensanChurchTwo = $gensan->localChurches()->create(['name' => 'Demo Trinity Church']);

        $admin = User::create([
            'name' => 'Demo Admin', 'username' => 'demo.admin', 'password' => self::PASSWORD,
            'role' => Role::Admin, 'must_change_password' => false,
        ]);
        User::create([
            'name' => 'Demo Secretary', 'username' => 'demo.secretary', 'password' => self::PASSWORD,
            'role' => Role::Secretary, 'must_change_password' => false,
        ]);
        // One Circuit President account per real Circuit, not just the two with demo data —
        // every Circuit genuinely needs its own login to upload its own roster.
        $presidentUsernames = $circuits->map(function (Circuit $circuit) {
            $slug = str($circuit->name)->before(' Circuit')->lower()->toString();

            User::create([
                'name' => "Demo President {$circuit->name}", 'username' => "demo.president.{$slug}", 'password' => self::PASSWORD,
                'role' => Role::CircuitPresident, 'circuit_id' => $circuit->id, 'must_change_password' => false,
            ]);

            return "demo.president.{$slug}";
        });

        $security = User::create([
            'name' => 'Demo Security', 'username' => 'demo.security', 'password' => self::PASSWORD,
            'role' => Role::Security, 'must_change_password' => false,
        ]);
        User::create([
            'name' => 'Demo Tabulator', 'username' => 'demo.tabulator', 'password' => self::PASSWORD,
            'role' => Role::Tabulator, 'must_change_password' => false,
        ]);

        $juan = $this->attendee($camp, $polomolokChurchOne, 'Juan', 'Cruz', '2008-04-03', true, AttendeeType::Youth, RegistrationType::PreRegistration);
        $this->attendee($camp, $polomolokChurchOne, 'Maria', 'Santos', '2001-11-20', false, AttendeeType::Youth, RegistrationType::PreRegistration);
        $this->attendee($camp, $polomolokChurchOne, 'Pastor Pedro', 'Reyes', '1975-02-14', true, AttendeeType::Pastor, RegistrationType::PreRegistration);
        $this->attendee($camp, $polomolokChurchTwo, 'Ramon', 'Bautista', '2003-09-09', true, AttendeeType::Youth, RegistrationType::OnSite);
        $this->attendee($camp, $polomolokChurchTwo, 'Ana', 'Aquino', '2005-05-05', false, AttendeeType::Youth, RegistrationType::OnSite)->delete();

        $renamed = $this->attendee($camp, $gensanChurchOne, 'Juanito', 'Dela Cruz', '2006-06-06', true, AttendeeType::Youth, RegistrationType::PreRegistration);
        $renamed->update(['needs_reprint' => true]);
        $this->attendee($camp, $gensanChurchOne, 'Carmela', 'Villanueva', '2004-03-30', true, AttendeeType::Youth, RegistrationType::PreRegistration);
        $printed = $this->attendee($camp, $gensanChurchTwo, 'Dennis', 'Lim', '2002-12-01', true, AttendeeType::Youth, RegistrationType::PreRegistration);
        $printed->update(['printed_at' => now()->subDay()]);

        $this->attendee($pastCamp, $polomolokChurchOne, 'Old', 'Timer', '1999-01-01', true, AttendeeType::Youth, RegistrationType::PreRegistration);

        $basketball = Event::create(['youth_camp_id' => $camp->id, 'name' => 'Demo Basketball']);
        $basketball->ranks()->createMany([
            ['label' => 'Champion', 'points' => 50],
            ['label' => '1st Runner-up', 'points' => 30],
            ['label' => 'L', 'points' => 0],
        ]);
        $bbChampion = $basketball->ranks()->where('label', 'Champion')->sole();
        $bbRunnerUp = $basketball->ranks()->where('label', '1st Runner-up')->sole();
        $bbLoss = $basketball->ranks()->where('label', 'L')->sole();
        $basketball->scores()->create(['circuit_id' => $polomolok->id, 'event_rank_id' => $bbChampion->id]);
        $basketball->scores()->create(['circuit_id' => $gensan->id, 'event_rank_id' => $bbRunnerUp->id]);
        $basketball->scores()->create(['circuit_id' => $surallah->id, 'event_rank_id' => $bbLoss->id]);

        $quiz = Event::create(['youth_camp_id' => $camp->id, 'name' => 'Demo Bible Quiz']);
        $quiz->ranks()->createMany([
            ['label' => 'Champion', 'points' => 20],
            ['label' => 'L', 'points' => 0],
        ]);
        $quizChampion = $quiz->ranks()->where('label', 'Champion')->sole();
        $quizLoss = $quiz->ranks()->where('label', 'L')->sole();
        $quiz->scores()->create(['circuit_id' => $gensan->id, 'event_rank_id' => $quizChampion->id]);
        $quiz->scores()->create(['circuit_id' => $polomolok->id, 'event_rank_id' => $quizLoss->id]);
        $quiz->scores()->create(['circuit_id' => $surallah->id, 'event_rank_id' => $quizLoss->id]);
        // Polomolok and Gensan both total 50 points here: a deliberate tie for the contest leaderboard.
        // TuTam, KorLuTan, NorSan and Banga are never scored, showing what an untouched real Circuit looks like.

        GateScan::create([
            'client_scan_id' => (string) Str::uuid(), 'attendee_id' => $juan->id, 'direction' => GateDirection::In,
            'scanned_by' => $security->id, 'scanned_at' => now()->subHours(2), 'is_mismatch' => false,
        ]);
        GateScan::create([
            'client_scan_id' => (string) Str::uuid(), 'attendee_id' => $juan->id, 'direction' => GateDirection::In,
            'scanned_by' => $security->id, 'scanned_at' => now()->subHour(), 'is_mismatch' => true,
            'message' => 'Already IN since '.now()->subHours(2)->format('g:i A').'.',
        ]);
        GateScan::create([
            'client_scan_id' => (string) Str::uuid(), 'attendee_id' => null, 'manual_name' => 'Demo Walk-in Guest',
            'direction' => GateDirection::In, 'scanned_by' => $security->id, 'scanned_at' => now()->subMinutes(30),
            'is_mismatch' => true, 'message' => 'Entered by name; not on the downloaded roster. Needs matching.',
        ]);

        $this->command?->info('Demo scenario ready. Every account password is "'.self::PASSWORD.'".');
        $this->command?->table(['Username', 'Role'], [
            ['demo.admin', 'Admin'],
            ['demo.secretary', 'Secretary'],
            ...$presidentUsernames->map(fn (string $username) => [$username, 'Circuit President'])->all(),
            ['demo.security', 'Security'],
            ['demo.tabulator', 'Tabulator'],
        ]);
    }

    private function attendee(
        YouthCamp $camp,
        LocalChurch $church,
        string $first,
        string $last,
        string $birthday,
        bool $baptized,
        AttendeeType $type,
        RegistrationType $registrationType,
    ): Attendee {
        return Attendee::create([
            'youth_camp_id' => $camp->id,
            'local_church_id' => $church->id,
            'first_name' => $first,
            'last_name' => $last,
            'birthday' => $birthday,
            'is_baptized' => $baptized,
            'type' => $type,
            'registration_type' => $registrationType,
        ]);
    }

    private function wipePreviousRun(): void
    {
        $circuitIds = Circuit::withTrashed()->whereIn('name', array_keys(self::CIRCUITS))->pluck('id');
        $churchIds = LocalChurch::withTrashed()->where('name', 'like', 'Demo %')->whereIn('circuit_id', $circuitIds)->pluck('id');

        GateScan::whereHas('attendee', fn ($q) => $q->whereIn('local_church_id', $churchIds))
            ->orWhere('manual_name', 'Demo Walk-in Guest')
            ->delete();
        Attendee::withTrashed()->whereIn('local_church_id', $churchIds)->forceDelete();

        $eventIds = Event::withTrashed()->where('name', 'like', 'Demo %')->pluck('id');
        DB::table('event_scores')->whereIn('event_id', $eventIds)->delete();
        Event::withTrashed()->whereIn('id', $eventIds)->forceDelete();

        User::withTrashed()->where('username', 'like', 'demo.%')->forceDelete();
        LocalChurch::withTrashed()->whereIn('id', $churchIds)->forceDelete();
        YouthCamp::withTrashed()->where('year', 2020)->where('theme', 'Demo Past Camp')->forceDelete();
    }
}
