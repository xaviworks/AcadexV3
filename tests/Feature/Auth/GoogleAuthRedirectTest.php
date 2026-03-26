<?php

namespace Tests\Feature\Auth;

use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthRedirectTest extends TestCase
{
    public function test_google_redirect_persists_the_device_fingerprint_in_session(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/v2/auth'));
        $driver->shouldNotReceive('stateless');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver);

        $response = $this->get('/auth/google?device_fingerprint=test-fingerprint');

        $response->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
        $response->assertSessionHas('auth.google.device_fingerprint', 'test-fingerprint');
    }
}
