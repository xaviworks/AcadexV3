<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'ResetPass1!',
                'password_confirmation' => 'ResetPass1!',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_password_reset_revokes_existing_authenticated_sessions(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'session-to-revoke',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.10',
            'user_agent' => 'Reset Test Browser',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        DB::table('user_devices')->insert([
            'user_id' => $user->id,
            'device_fingerprint' => 'trusted-device',
            'trust_token_hash' => hash('sha256', str_repeat('c', 64)),
            'ip_address' => '127.0.0.11',
            'browser' => 'Chrome',
            'platform' => 'macOS',
            'last_used_at' => now(),
            'trusted_until' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });

        $this->assertDatabaseMissing('sessions', [
            'id' => 'session-to-revoke',
        ]);
        $this->assertDatabaseMissing('user_devices', [
            'user_id' => $user->id,
            'device_fingerprint' => 'trusted-device',
        ]);
    }
}
