<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTwoFactorFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_callback_requires_two_factor_challenge_when_setup_is_unconfirmed(): void
    {
        $user = User::factory()->create([
            'role' => 0,
            'email' => 'instructor@brokenshire.edu.ph',
            'is_active' => true,
            'google_id' => null,
            'two_factor_secret' => encrypt('secret-key'),
            'two_factor_confirmed_at' => null,
        ]);

        $googleUser = Mockery::mock();
        $googleUser->shouldReceive('getEmail')->andReturn('instructor@brokenshire.edu.ph');
        $googleUser->shouldReceive('getId')->andReturn('google-user-2fa');

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver);

        $response = $this
            ->withSession([
                'auth.google.device_fingerprint' => 'fingerprint-2fa',
            ])
            ->get('/auth/google/callback?code=fake-code&state=fake-state');

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('auth.2fa.id', $user->id);
        $response->assertSessionHas('auth.2fa.fingerprint', 'fingerprint-2fa');
        $this->assertGuest();
    }
}
