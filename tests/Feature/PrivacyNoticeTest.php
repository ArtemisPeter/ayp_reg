<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyNoticeTest extends TestCase
{
    public function test_anyone_can_read_the_privacy_notice_without_signing_in(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy Notice');
    }

    public function test_the_home_page_links_to_the_privacy_notice(): void
    {
        $this->get('/')->assertSee('/privacy', false);
    }
}
