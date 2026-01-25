<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * BlockMobileDevicesTest
 * 
 * Tests mobile device blocking middleware functionality.
 * Verifies that mobile phones AND tablets are blocked while desktops can access.
 */
class BlockMobileDevicesTest extends TestCase
{
    /**
     * Test that desktop users can access the application.
     */
    public function test_desktop_users_can_access_application(): void
    {
        // Simulate a desktop user agent
        $response = $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        // Desktop users should NOT be blocked (can be 200 or 302)
        $this->assertNotEquals(403, $response->status());
        $response->assertDontSee('Mobile Device Access Blocked');
    }

    /**
     * Test that tablet users are now blocked.
     */
    public function test_tablet_users_are_blocked(): void
    {
        // Simulate an iPad user agent
        $response = $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1'
        ]);

        // Tablet users should be blocked with 403
        $response->assertStatus(403);
        $response->assertSee('Mobile Device Access Blocked');
    }

    /**
     * Test that mobile phone users are blocked with 403 status.
     */
    public function test_mobile_phone_users_are_blocked(): void
    {
        // Simulate an iPhone user agent
        $response = $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
        ]);

        // Mobile users should receive 403 Forbidden
        $response->assertStatus(403);
        $response->assertSee('Mobile Device Access Blocked');
    }

    /**
     * Test that Android phone users are blocked.
     */
    public function test_android_phone_users_are_blocked(): void
    {
        // Simulate an Android phone user agent
        $response = $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 13; SM-S908B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36'
        ]);

        // Mobile users should receive 403 Forbidden
        $response->assertStatus(403);
        $response->assertSee('Mobile Device Access Blocked');
    }

    /**
     * Test that Android tablet users are blocked.
     */
    public function test_android_tablet_users_are_blocked(): void
    {
        // Simulate an Android tablet user agent
        $response = $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 13; SM-X900) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        // Tablet users should be blocked with 403
        $response->assertStatus(403);
        $response->assertSee('Mobile Device Access Blocked');
    }

    /**
     * Test that the blocked page contains proper information.
     */
    public function test_blocked_page_contains_proper_information(): void
    {
        // Simulate a mobile user
        $response = $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
        ]);

        $response->assertStatus(403);
        $response->assertSee('Mobile Device Access Blocked');
        $response->assertSee('Desktop Computers');
        $response->assertSee('Laptop Computers');
        $response->assertDontSee('Tablet Devices');
    }
}
