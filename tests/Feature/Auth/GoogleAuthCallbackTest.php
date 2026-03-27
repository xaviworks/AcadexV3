<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_callback_matches_standard_login_flow_for_instructors(): void
    {
        $user = User::factory()->create([
            'role' => 0,
            'email' => 'instructor@brokenshire.edu.ph',
            'is_active' => true,
            'google_id' => null,
            'remember_token' => null,
        ]);

        $googleUser = Mockery::mock();
        $googleUser->shouldReceive('getEmail')->andReturn('instructor@brokenshire.edu.ph');
        $googleUser->shouldReceive('getId')->andReturn('google-user-123');

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($googleUser);
        $driver->shouldNotReceive('stateless');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver);

        $response = $this
            ->withSession([
                'auth.google.device_fingerprint' => 'fingerprint-123',
                'active_academic_period_id' => 99,
            ])
            ->get('/auth/google/callback?code=fake-code&state=fake-state');

        $response->assertRedirect(route('select.academicPeriod'));
        $response->assertSessionHas('device_fingerprint', 'fingerprint-123');
        $response->assertSessionMissing('auth.google.device_fingerprint');
        $response->assertSessionMissing('active_academic_period_id');
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertDatabaseHas('user_logs', [
            'user_id' => $user->id,
            'event_type' => 'login',
        ]);
        $this->assertDatabaseCount('user_logs', 1);
        $this->assertSame('google-user-123', $user->fresh()->google_id);
        $this->assertNull($user->fresh()->remember_token);
    }
}
