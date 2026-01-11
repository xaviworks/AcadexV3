<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tutorial;
use App\Models\TutorialStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Default Tutorials Seeder
 * 
 * Seeds all default tutorials migrated from the original JavaScript-based tutorial system.
 * These tutorials serve as the baseline educational content for the platform.
 * 
 * Original Source: public/js/*-tutorials/*.js files
 * Migration Date: 2026-01-11
 */
class DefaultTutorialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user to be the creator
        $adminUser = User::where('role', 3)->first();
        
        if (!$adminUser) {
            $this->command->error('No admin user found. Please create an admin user first.');
            return;
        }

        $this->command->info('Seeding default tutorials...');

        DB::transaction(function () use ($adminUser) {
            // Admin Tutorials
            $this->seedAdminDashboard($adminUser);
            
            $this->command->info('Default tutorials seeded successfully!');
        });
    }

    /**
     * Seed Admin Dashboard Tutorial
     */
    private function seedAdminDashboard(User $adminUser): void
    {
        $tutorial = Tutorial::create([
            'role' => 'admin',
            'page_identifier' => 'admin-dashboard',
            'title' => 'Admin Dashboard Overview',
            'description' => 'Learn how to monitor system activity, user statistics, login patterns, and security metrics',
            'is_active' => true,
            'priority' => 10,
            'created_by' => $adminUser->id,
        ]);

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
            [
                'title' => 'Activity Table Headers',
                'content' => 'The table columns show: Hour (12 AM to 11 PM), Successful Logins count, Failed Attempts count, and Success Rate percentage. Each row represents one hour of the selected day.',
                'target_selector' => '.table-responsive table thead',
                'position' => 'bottom',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Peak Activity Hours',
                'content' => 'Highlighted rows (with subtle background) indicate peak activity hours - the times with highest total login attempts. Plan system maintenance and updates during off-peak hours to minimize user disruption.',
                'target_selector' => '.table-active, .table-responsive tbody tr:first-child',
                'position' => 'bottom',
                'is_optional' => true,
                'requires_data' => true,
            ],
            [
                'title' => 'Success Rate Indicators',
                'content' => 'Visual progress bars show the success rate for each time period. Colors indicate health: Green (90%+) = Excellent, Blue (70-89%) = Good, Yellow (50-69%) = Needs attention, Red (<50%) = Investigate immediately.',
                'target_selector' => '.progress-bar, .progress',
                'position' => 'left',
                'is_optional' => true,
                'requires_data' => true,
            ],
            [
                'title' => 'Monthly Overview Panel',
                'content' => 'This panel shows login trends across the entire year. Compare month-over-month activity to identify seasonal patterns, such as increased usage during enrollment periods or reduced activity during breaks.',
                'target_selector' => '.col-lg-4 .card, .card:has(.bi-calendar-check)',
                'position' => 'left',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Year Selection Filter',
                'content' => 'Use this dropdown to view statistics from previous years. Compare year-over-year trends to track system growth and identify long-term patterns in user engagement.',
                'target_selector' => 'select[name="year"]',
                'position' => 'left',
                'is_optional' => false,
                'requires_data' => false,
            ],
            [
                'title' => 'Monthly Statistics Display',
                'content' => 'Each month shows: successful logins (green badge), failed attempts (red badge), and a progress bar indicating success rate. Highlighted months indicate highest activity periods.',
                'target_selector' => '.col-lg-4 .bg-light, .col-lg-4 .mb-3:first-of-type',
                'position' => 'left',
                'is_optional' => true,
                'requires_data' => true,
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

        $this->command->info("  ✓ Admin Dashboard ({$tutorial->id}): {$tutorial->title} - " . count($steps) . " steps");
    }
}
