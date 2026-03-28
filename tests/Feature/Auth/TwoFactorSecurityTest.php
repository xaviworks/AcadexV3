<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabling_two_factor_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->post(route('two-factor.enable'), []);

        $response
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('enableTwoFactor', 'password');

        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_two_factor_secret_is_encrypted_and_not_inlined_on_the_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->post(route('two-factor.enable'), [
                'password' => 'password',
            ])->assertRedirect('/profile');

        $user->refresh();
        $storedSecret = DB::table('users')->where('id', $user->id)->value('two_factor_secret');

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($storedSecret);
        $this->assertNotSame($user->two_factor_secret, $storedSecret);
        $this->assertSame($user->two_factor_secret, Crypt::decryptString($storedSecret));

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertDontSee($user->two_factor_secret);
        $response->assertDontSee('qr-code-img');
        $response->assertDontSee('qr-code-svg');
    }

    public function test_qr_code_reveal_requires_the_current_password_and_returns_markup_after_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->post(route('two-factor.enable'), [
                'password' => 'password',
            ])->assertRedirect('/profile');

        $this->actingAs($user)
            ->postJson(route('two-factor.reveal-qr'), [
                'password' => 'wrong-password',
            ])
            ->assertStatus(422);

        $response = $this->actingAs($user)
            ->postJson(route('two-factor.reveal-qr'), [
                'password' => 'password',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertStringContainsString('qr-code', $response->json('qr_code'));
    }

    public function test_successful_two_factor_login_sets_a_server_issued_trusted_device_cookie(): void
    {
        $secret = app(Google2FA::class)->generateSecretKey();
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->withSession([
            'auth.2fa.id' => $user->id,
            'auth.2fa.fingerprint' => 'fingerprint-123',
        ])->post(route('two-factor.login.store'), [
            'code' => app(Google2FA::class)->getCurrentOtp($secret),
            'device_fingerprint' => 'fingerprint-123',
        ]);

        $response->assertRedirect(route('select.academicPeriod', absolute: false));
        $response->assertCookie('trusted_device_'.$user->id);
        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'device_fingerprint' => 'fingerprint-123',
        ]);
        $this->assertNotNull(
            DB::table('user_devices')->where('user_id', $user->id)->value('trust_token_hash')
        );
        $this->assertNotNull(
            DB::table('user_devices')->where('user_id', $user->id)->value('trusted_until')
        );
    }

    public function test_two_factor_login_challenge_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => app(Google2FA::class)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->withSession([
                'auth.2fa.id' => $user->id,
            ])->post(route('two-factor.login.store'), [
                'code' => '000000',
            ])->assertRedirect();
        }

        $this->withSession([
            'auth.2fa.id' => $user->id,
        ])->post(route('two-factor.login.store'), [
            'code' => '000000',
        ])->assertStatus(429);
    }

    public function test_password_reset_two_factor_challenge_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => app(Google2FA::class)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->withSession([
                'password_reset.email' => $user->email,
                'password_reset.requires_2fa' => true,
            ])->post(route('password.2fa.verify'), [
                'code' => '000000',
            ])->assertRedirect();
        }

        $this->withSession([
            'password_reset.email' => $user->email,
            'password_reset.requires_2fa' => true,
        ])->post(route('password.2fa.verify'), [
            'code' => '000000',
        ])->assertStatus(429);
    }
}
