<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAccountsSeeder;
use Database\Seeders\DepartmentsTableSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAccountSeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_privileged_account_seeding_is_blocked_by_default(): void
    {
        $this->seed(DepartmentsTableSeeder::class);
        config(['security.allow_privileged_account_seeding' => false]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Privileged account seeding is disabled.');

        $this->seed(AdminAccountsSeeder::class);
    }

    public function test_privileged_account_seeding_requires_explicit_authorization_and_uses_temporary_passwords(): void
    {
        $this->seed(DepartmentsTableSeeder::class);
        config(['security.allow_privileged_account_seeding' => true]);

        $this->seed(AdminAccountsSeeder::class);

        $admin = User::where('email', 'admin1@brokenshire.edu.ph')->firstOrFail();

        $this->assertSame(3, $admin->role);
        $this->assertTrue($admin->must_change_password);
        $this->assertFalse(Hash::check('password', $admin->password));
        $this->assertFalse(Hash::check('password123', $admin->password));
    }

    public function test_privileged_account_seeder_does_not_overwrite_existing_passwords(): void
    {
        $this->seed(DepartmentsTableSeeder::class);
        config(['security.allow_privileged_account_seeding' => true]);

        User::factory()->create([
            'email' => 'admin1@brokenshire.edu.ph',
            'password' => Hash::make('ExistingSecure1!'),
            'role' => 3,
            'must_change_password' => false,
        ]);

        $this->seed(AdminAccountsSeeder::class);

        $admin = User::where('email', 'admin1@brokenshire.edu.ph')->firstOrFail();

        $this->assertTrue(Hash::check('ExistingSecure1!', $admin->password));
        $this->assertFalse($admin->must_change_password);
    }

    public function test_demo_users_are_not_seeded_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Demo user seeding is only allowed in local or testing environments.');

        (new UserSeeder)->run();
    }

    public function test_reference_data_can_be_seeded_repeatedly_without_creating_users(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $this->seed(ReferenceDataSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('departments', [
            'department_code' => 'ASE',
        ]);
    }

    public function test_temporary_password_users_are_restricted_until_password_change(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Temporary1!'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('profile.edit'));

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();

        $this->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'Temporary1!',
                'password' => 'Permanent1!',
                'password_confirmation' => 'Permanent1!',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertFalse($user->refresh()->must_change_password);
        $this->assertTrue(Hash::check('Permanent1!', $user->password));
    }

    public function test_start_script_only_runs_reference_data_by_default(): void
    {
        $script = file_get_contents(base_path('start.sh'));

        $this->assertStringContainsString('db:seed --class=ReferenceDataSeeder --force', $script);
        $this->assertStringContainsString('${ALLOW_PRIVILEGED_ACCOUNT_SEEDING:-false}', $script);
        $this->assertStringContainsString('db:seed --class=AdminAccountsSeeder --force', $script);
        $this->assertStringNotContainsString('php artisan db:seed --force', $script);
    }

    public function test_initial_admin_command_can_create_verified_admin_with_hidden_password(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->artisan('app:create-initial-admin', [
            '--email' => 'local.admin@example.com',
            '--first-name' => 'Local',
            '--last-name' => 'Admin',
        ])
            ->expectsQuestion('Initial password (leave blank to require password reset)', 'LocalAdmin1!')
            ->assertExitCode(0);

        $admin = User::where('email', 'local.admin@example.com')->firstOrFail();

        $this->assertSame(3, $admin->role);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertFalse($admin->must_change_password);
        $this->assertTrue(Hash::check('LocalAdmin1!', $admin->password));
    }

    public function test_initial_admin_command_accepts_local_password_option(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->artisan('app:create-initial-admin', [
            '--email' => 'option.admin@example.com',
            '--first-name' => 'Option',
            '--last-name' => 'Admin',
            '--password' => 'OptionAdmin1!',
        ])
            ->assertExitCode(0);

        $admin = User::where('email', 'option.admin@example.com')->firstOrFail();

        $this->assertNotNull($admin->email_verified_at);
        $this->assertFalse($admin->must_change_password);
        $this->assertTrue(Hash::check('OptionAdmin1!', $admin->password));
    }

    public function test_initial_admin_command_blank_password_requires_reset_without_email_verification(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->artisan('app:create-initial-admin', [
            '--email' => 'reset.admin@example.com',
            '--first-name' => 'Reset',
            '--last-name' => 'Admin',
        ])
            ->expectsQuestion('Initial password (leave blank to require password reset)', '')
            ->assertExitCode(0);

        $admin = User::where('email', 'reset.admin@example.com')->firstOrFail();

        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue($admin->must_change_password);
        $this->assertFalse(Hash::check('password', $admin->password));
    }
}
