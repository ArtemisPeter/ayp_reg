<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_refuse_to_be_framed_by_other_sites_and_allow_only_this_sites_camera(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (['/', '/login', '/dashboard'] as $page) {
            $this->get($page)
                ->assertHeader('X-Frame-Options', 'DENY')
                ->assertHeader('Content-Security-Policy', "frame-ancestors 'none'")
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
        }
    }
}
