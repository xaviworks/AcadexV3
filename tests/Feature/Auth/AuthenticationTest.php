<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('select.academicPeriod', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_invalid_password_attempts_are_logged_as_failed_logins(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $user->id,
            'event_type' => 'failed_login',
        ]);
    }

    public function test_existing_device_fingerprint_alone_can_not_bypass_two_factor(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => app(Google2FA::class)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);

        DB::table('user_devices')->insert([
            'user_id' => $user->id,
            'device_fingerprint' => 'trusted-fingerprint',
            'ip_address' => '127.0.0.2',
            'browser' => 'Chrome',
            'platform' => 'macOS',
            'last_used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_fingerprint' => 'trusted-fingerprint',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_valid_trusted_device_cookie_can_bypass_two_factor(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => app(Google2FA::class)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);

        $token = str_repeat('a', 64);

        DB::table('user_devices')->insert([
            'user_id' => $user->id,
            'device_fingerprint' => 'trusted-fingerprint',
            'trust_token_hash' => hash('sha256', $token),
            'ip_address' => '127.0.0.2',
            'browser' => 'Chrome',
            'platform' => 'macOS',
            'last_used_at' => now(),
            'trusted_until' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withCookie('trusted_device_'.$user->id, $token)
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'device_fingerprint' => 'trusted-fingerprint',
            ]);

        $this->assertAuthenticatedAs($user->fresh());
        $response->assertRedirect(route('select.academicPeriod', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_login_reaches_two_factor_challenge_without_creating_a_successful_login_log(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => app(Google2FA::class)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_fingerprint' => 'fresh-device-fingerprint',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('auth.2fa.id', $user->id);
        $response->assertSessionHas('auth.2fa.fingerprint', 'fresh-device-fingerprint');
        $this->assertGuest();
        $this->assertDatabaseCount('user_logs', 0);
    }

    public function test_admin_logout_preserves_other_active_sessions(): void
    {
        $admin = User::factory()->create([
            'role' => 3,
        ]);

        DB::table('sessions')->insert([
            'id' => 'other-admin-session',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.2',
            'user_agent' => 'Test Browser',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $response = $this->actingAs($admin)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertDatabaseHas('sessions', [
            'id' => 'other-admin-session',
            'user_id' => $admin->id,
        ]);
    }
}
