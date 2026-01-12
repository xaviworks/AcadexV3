<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SecurityAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'middle_name' => 'QA',
                'last_name' => 'User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test', $user->first_name);
        $this->assertSame('QA', $user->middle_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'middle_name' => $user->middle_name,
                'last_name' => 'User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_update_password_with_profile_changes(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $admin = User::factory()->create([
            'role' => 3,
            'is_active' => true,
        ]);

        Notification::fake();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Updated',
                'middle_name' => null,
                'last_name' => 'User',
                'email' => 'updated@example.com',
                'current_password' => 'password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Updated', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertTrue(Hash::check('new-secure-password', $user->password));

        Notification::assertSentTo(
            $admin,
            SecurityAlert::class,
            function (SecurityAlert $notification) use ($admin, $user) {
                $payload = $notification->toArray($admin);

                return ($payload['alert_type'] ?? null) === SecurityAlert::TYPE_PASSWORD_CHANGED
                    && ($payload['affected_user_id'] ?? null) === $user->id;
            }
        );
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
