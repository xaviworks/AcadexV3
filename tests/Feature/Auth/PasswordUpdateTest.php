<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();
        $originalRememberToken = $user->remember_token;

        DB::table('user_devices')->insert([
            'user_id' => $user->id,
            'device_fingerprint' => 'trusted-device',
            'trust_token_hash' => hash('sha256', str_repeat('b', 64)),
            'ip_address' => '127.0.0.2',
            'browser' => 'Chrome',
            'platform' => 'macOS',
            'last_used_at' => now(),
            'trusted_until' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
        $this->assertNotSame($originalRememberToken, $user->remember_token);
        $this->assertDatabaseMissing('user_devices', [
            'user_id' => $user->id,
            'device_fingerprint' => 'trusted-device',
        ]);
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');
    }
}
