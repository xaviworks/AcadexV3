<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 3)->first();
        
        if (!$admin) {
            $this->command->warn('No admin user found. Skipping announcement seeding.');
            return;
        }

        // Welcome announcement for all users
        Announcement::create([
            'title' => '🎉 Welcome to ACADEX V3!',
            'message' => "We're excited to have you here! This new announcement system will keep you informed about important updates, maintenance schedules, and system improvements.\n\nStay tuned for more updates!",
            'type' => 'success',
            'priority' => 'normal',
            'target_roles' => null, // All users
            'is_active' => true,
            'is_dismissible' => true,
            'show_once' => false,
            'created_by' => $admin->id,
        ]);

        // Urgent system maintenance announcement
        Announcement::create([
            'title' => '⚠️ Scheduled Maintenance',
            'message' => "System maintenance is scheduled for this weekend (Jan 10-11, 2026).\n\nThe system will be temporarily unavailable from 2:00 AM to 6:00 AM.\n\nPlease save your work before this time.",
            'type' => 'warning',
            'priority' => 'urgent',
            'target_roles' => null, // All users
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'is_active' => true,
            'is_dismissible' => true,
            'show_once' => false,
            'created_by' => $admin->id,
        ]);

        // Instructor-specific announcement
        Announcement::create([
            'title' => '📝 Grade Submission Reminder',
            'message' => "Dear Instructors,\n\nPlease submit all final grades by January 15, 2026.\n\nLate submissions may affect student enrollment for the next semester.",
            'type' => 'info',
            'priority' => 'high',
            'target_roles' => [0], // Only instructors
            'is_active' => true,
            'is_dismissible' => true,
            'show_once' => true,
            'created_by' => $admin->id,
        ]);

        $this->command->info('✓ Sample announcements created successfully!');
    }
}
