<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\GateScan;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GateAppApiTest extends TestCase
{
    use RefreshDatabase;

    private Attendee $juan;

    protected function setUp(): void
    {
        parent::setUp();

        $camp = YouthCamp::factory()->create(['is_current' => true]);
        $church = LocalChurch::factory()->create([
            'circuit_id' => Circuit::factory()->create(['name' => 'Digos City'])->id,
            'name' => 'Grace Church',
        ]);
        $this->juan = Attendee::factory()->create(['youth_camp_id' => $camp->id, 'local_church_id' => $church->id, 'first_name' => 'Juan']);
    }

    private function signIn(User $user, string $password = 'Gate-Pass-1'): string
    {
        return $this->postJson('/api/gate/login', ['username' => $user->username, 'password' => $password, 'device_name' => 'Pixel'])
            ->assertOk()
            ->json('token');
    }

    private function security(): User
    {
        return User::factory()->create(['role' => 'security', 'username' => 'gate.one', 'password' => 'Gate-Pass-1']);
    }

    public function test_security_signs_in_downloads_the_roster_and_records_a_scan_with_the_token(): void
    {
        $token = $this->signIn($this->security());

        $this->withToken($token)->getJson('/api/gate/roster')
            ->assertOk()
            ->assertJsonFragment(['qr_code' => $this->juan->qr_code, 'first_name' => 'Juan', 'local_church' => 'Grace Church', 'circuit' => 'Digos City']);

        $this->withToken($token)->postJson('/api/gate/scans', [
            'client_scan_id' => (string) Str::uuid(),
            'direction' => 'in',
            'scanned_at' => now()->toISOString(),
            'qr_code' => $this->juan->qr_code,
        ])
            ->assertOk()
            ->assertJsonPath('status', 'in')
            ->assertJsonPath('attendee.first_name', 'Juan');

        $this->assertDatabaseHas('gate_scans', ['attendee_id' => $this->juan->id, 'direction' => 'in']);
    }

    public function test_a_wrong_password_gets_no_token(): void
    {
        $this->security();

        $this->postJson('/api/gate/login', ['username' => 'gate.one', 'password' => 'wrong-pass-1'])
            ->assertUnprocessable()
            ->assertJsonMissingPath('token');
    }

    public function test_repeated_wrong_passwords_lock_the_app_sign_in_too(): void
    {
        $this->security();

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/gate/login', ['username' => 'gate.one', 'password' => 'wrong-pass-1']);
        }

        $this->postJson('/api/gate/login', ['username' => 'gate.one', 'password' => 'Gate-Pass-1'])
            ->assertTooManyRequests();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rolesWithoutGateAccess(): array
    {
        return [
            'secretary' => ['secretary'],
            'circuit president' => ['circuit_president'],
            'tabulator' => ['tabulator'],
        ];
    }

    #[DataProvider('rolesWithoutGateAccess')]
    public function test_only_security_and_the_admin_can_sign_in_to_the_app(string $role): void
    {
        $user = User::factory()->create(['role' => $role, 'username' => 'not.gate', 'password' => 'Gate-Pass-1']);

        $this->postJson('/api/gate/login', ['username' => 'not.gate', 'password' => 'Gate-Pass-1'])
            ->assertForbidden()
            ->assertJsonMissingPath('token');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_someone_still_on_a_temporary_password_must_replace_it_on_the_website_first(): void
    {
        User::factory()->create(['role' => 'security', 'username' => 'gate.new', 'password' => 'Temp-Pass-1', 'must_change_password' => true]);

        $this->postJson('/api/gate/login', ['username' => 'gate.new', 'password' => 'Temp-Pass-1'])
            ->assertForbidden()
            ->assertJsonPath('message', 'Sign in on the website first and replace your temporary password.');
    }

    public function test_the_roster_and_scans_need_a_token(): void
    {
        $this->getJson('/api/gate/roster')->assertUnauthorized();
        $this->postJson('/api/gate/scans', [])->assertUnauthorized();
        $this->assertSame(0, GateScan::count());
    }

    public function test_signing_out_revokes_the_token(): void
    {
        $token = $this->signIn($this->security());

        $this->withToken($token)->postJson('/api/gate/logout')->assertOk();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/gate/roster')->assertUnauthorized();
    }

    public function test_a_password_reset_signs_the_app_out_on_every_phone(): void
    {
        $security = $this->security();
        $token = $this->signIn($security);

        $security->update(['password' => 'Brand-New-2']);
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/gate/roster')->assertUnauthorized();
    }

    public function test_a_token_stops_working_after_thirty_days(): void
    {
        $token = $this->signIn($this->security());

        $this->travel(31)->days();

        $this->withToken($token)->getJson('/api/gate/roster')->assertUnauthorized();
    }
}
