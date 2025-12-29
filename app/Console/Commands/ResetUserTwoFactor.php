<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetUserTwoFactor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-2fa {email : The email address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emergency reset of two-factor authentication for a user (use when locked out)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return 1;
        }

        if (!$user->two_factor_secret) {
            $this->info("User {$user->full_name} does not have 2FA enabled.");
            return 0;
        }

        // Confirm the action
        if (!$this->confirm("Are you sure you want to reset 2FA for {$user->full_name} ({$email})?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Reset 2FA
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        // Clear trusted devices
        $user->devices()->delete();

        // Log the action
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'event_type' => 'session_revoked', // Using existing enum value
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Console Command',
            'browser' => 'CLI',
            'device' => 'Server',
            'platform' => 'Console',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("✓ Two-factor authentication has been reset for {$user->full_name}");
        $this->info("✓ All trusted devices have been cleared");
        $this->warn("⚠ The user should re-enable 2FA for security");

        return 0;
    }
}
