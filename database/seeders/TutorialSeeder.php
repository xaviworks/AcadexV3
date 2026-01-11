<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tutorial;
use App\Models\TutorialStep;
use App\Models\TutorialDataCheck;
use App\Models\User;

class TutorialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * NOTE: This seeder is now deprecated in favor of DefaultTutorialsSeeder.
     * DefaultTutorialsSeeder contains all tutorials migrated from the original
     * JavaScript-based system and is called by DatabaseSeeder automatically.
     * 
     * This seeder remains for backward compatibility and testing purposes.
     * Seeds a sample tutorial from existing static tutorial (admin-dashboard)
     */
    public function run(): void
    {
        // Get first admin user (role = 3)
        $adminUser = User::where('role', 3)->first();
        
        if (!$adminUser) {
            $this->command->error('No admin user found. Please create an admin user first.');
            return;
        }

        // Create Admin Dashboard Tutorial
        $tutorial = Tutorial::create([
            'role' => 'admin',
            'page_identifier' => 'admin-dashboard',
            'title' => 'Admin Dashboard Overview',
            'description' => 'Learn how to monitor system activity, user statistics, login patterns, and security metrics',
            'is_active' => true,
            'priority' => 10,
            'created_by' => $adminUser->id,
        ]);

        // Create tutorial steps (from existing admin-dashboard tutorial)
        $steps = [
            [
                'title' => 'Admin Control Panel',
                'content' => 'Welcome to the Admin Control Panel! This is your central hub for monitoring system health, user activity, and security metrics. The dashboard provides real-time insights into how users interact with Acadex.',
                'target_selector' => '.container-fluid h2, .fw-bold.text-dark',
                'position' => 'bottom',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Total Users Card',
                'content' => 'This card displays the total number of registered accounts in the system, including Instructors, Chairpersons, Deans, GE Coordinators, VPAA, and Admins. Use this to track overall system adoption.',
                'target_selector' => '.hover-lift:first-child, .col-md-3:first-child .card',
                'position' => 'bottom',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Successful Logins Today',
                'content' => 'Shows the count of successful login attempts for the current day. A healthy system should show consistent daily logins. Sudden drops may indicate technical issues or user problems.',
                'target_selector' => '.hover-lift:nth-child(2), .col-md-3:nth-child(2) .card',
                'position' => 'bottom',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Failed Login Attempts',
                'content' => 'SECURITY METRIC: Tracks failed login attempts today. Monitor this closely - sudden spikes could indicate: brute-force attacks, credential stuffing, or users having password issues. Consider enabling account lockouts if this is consistently high.',
                'target_selector' => '.hover-lift:nth-child(3), .col-md-3:nth-child(3) .card',
                'position' => 'bottom',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Active Users Percentage',
                'content' => 'Shows what percentage of registered users logged in today. This engagement metric helps you understand system utilization. Low percentages during academic periods may warrant investigation.',
                'target_selector' => '.hover-lift:nth-child(4), .col-md-3:nth-child(4) .card',
                'position' => 'bottom',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Hourly Login Activity Panel',
                'content' => 'This detailed table breaks down login activity by hour. It shows successful logins (green badges), failed attempts (red badges), and success rate with visual progress bars. Use this to identify peak usage times.',
                'target_selector' => '.col-lg-8 .card, .card:has(.bi-graph-up)',
                'position' => 'bottom',
                'is_optional' => false,
                'requires_data' => false,
            ],
        ];

        foreach ($steps as $index => $stepData) {
            TutorialStep::create([
                'tutorial_id' => $tutorial->id,
                'step_order' => $index,
                'title' => $stepData['title'],
                'content' => $stepData['content'],
                'target_selector' => $stepData['target_selector'],
                'position' => $stepData['position'],
                'is_optional' => $stepData['is_optional'],
                'requires_data' => $stepData['requires_data'],
            ]);
        }

        $this->command->info('✓ Tutorial seeded successfully: ' . $tutorial->title);
        $this->command->info('  ID: ' . $tutorial->id);
        $this->command->info('  Steps: ' . count($steps));
    }
}

