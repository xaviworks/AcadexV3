<?php

namespace Database\Seeders;

use App\Models\AnnouncementTemplate;
use Illuminate\Database\Seeder;

class AnnouncementTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Message',
                'key' => 'welcome',
                'title' => 'Welcome to ACADEX!',
                'message' => "Welcome to the Academic Excellence (ACADEX) system! We're excited to have you on board.",
                'type' => 'info',
                'priority' => 'normal',
                'icon' => 'fa-hand-paper',
                'description' => 'A friendly welcome message for new users joining the system. Perfect for onboarding.',
                'order' => 1,
            ],
            [
                'name' => 'System Maintenance',
                'key' => 'maintenance',
                'title' => 'Scheduled System Maintenance',
                'message' => "The ACADEX system will undergo scheduled maintenance on [Insert Date] from [Insert Time] to [Insert Time], lasting approximately [X] hours. During this period, the system will be temporarily unavailable. Please be assured that all your data will be preserved and securely backed up, and no action is required from your end. We apologize for any inconvenience this may cause and appreciate your patience as we work to improve our services. Thank you for your understanding!",
                'type' => 'warning',
                'priority' => 'high',
                'icon' => 'fa-tools',
                'description' => 'Alert users about upcoming system maintenance or downtime periods.',
                'order' => 2,
            ],
            [
                'name' => 'Urgent Notice',
                'key' => 'urgent',
                'title' => 'URGENT: Action Required Immediately',
                'message' => "This is an urgent notification regarding [describe the urgent situation here]. Immediate action is required from you to complete the following tasks: [First action item], [Second action item], and [Third action item]. Please ensure that all actions are completed by [Insert deadline date and time]. Failure to complete these requirements may result in [Consequence 1] and [Consequence 2]. If you have any questions or concerns, please contact [Contact Person/Department] immediately. Thank you for your prompt attention to this matter.",
                'type' => 'danger',
                'priority' => 'urgent',
                'icon' => 'fa-exclamation-triangle',
                'description' => 'Critical announcements requiring immediate user attention and action.',
                'order' => 3,
            ],
            [
                'name' => 'New Feature',
                'key' => 'new_feature',
                'title' => 'Exciting New Feature Available!',
                'message' => "We're thrilled to announce a new feature in ACADEX! [Describe the new feature here]. This enhancement brings several key benefits including [Benefit 1], [Benefit 2], and [Benefit 3]. To access this feature, simply [Step 1], then [Step 2], and finally [Step 3]. Here's a helpful tip: [Include a helpful tip about using the feature]. We hope this enhancement improves your experience with ACADEX. As always, your feedback is valuable to us!",
                'type' => 'success',
                'priority' => 'normal',
                'icon' => 'fa-star',
                'description' => 'Announce new features, updates, or improvements to the system.',
                'order' => 4,
            ],
            [
                'name' => 'Deadline Reminder',
                'key' => 'deadline',
                'title' => 'Important Deadline Approaching',
                'message' => "This is a friendly reminder about an important deadline. The task [Insert task name] is due on [Insert date] at [Insert time]. Please ensure you have completed [Item 1], [Item 2], and [Item 3] before the deadline. Please note that late submissions may not be accepted, so double-check all requirements before submitting. If you need any assistance, contact us immediately at [Contact information]. Thank you for your timely attention to this matter!",
                'type' => 'warning',
                'priority' => 'high',
                'icon' => 'fa-clock',
                'description' => 'Remind users about upcoming deadlines for grades, submissions, or tasks.',
                'order' => 5,
            ],
            [
                'name' => 'Academic Event',
                'key' => 'academic_event',
                'title' => 'Upcoming Academic Event',
                'message' => "We are pleased to announce [Event Name] scheduled for [Insert date] at [Insert time]. The event will be held at [Insert location/platform]. [Brief description of the event]. This event is open to [Target audience 1] and [Target audience 2]. [Registration details or indicate if not required]. During the event, you can expect [Activity 1], [Activity 2], and [Activity 3]. We look forward to your participation! For more information, please contact [Contact details].",
                'type' => 'info',
                'priority' => 'normal',
                'icon' => 'fa-calendar-alt',
                'description' => 'Inform users about academic events, enrollment periods, or important dates.',
                'order' => 6,
            ],
            [
                'name' => 'Grade Submission',
                'key' => 'grade_submission',
                'title' => 'URGENT: Grade Submission Deadline',
                'message' => "Dear Instructors, this is an urgent reminder about the upcoming grade submission deadline on [Insert date and time], with [Calculate time] remaining. Please complete the following required actions: finalize all student grades, review grade distributions, submit grades through ACADEX, and verify submission confirmation. Please ensure all assessments are recorded and double-check your grade calculations. Submit before the deadline to avoid penalties as late submissions require special approval. If you need assistance, contact the Academic Affairs Office immediately. Thank you for your cooperation!",
                'type' => 'danger',
                'priority' => 'urgent',
                'icon' => 'fa-file-upload',
                'description' => 'Urgent reminder for instructors to submit grades before the deadline.',
                'order' => 7,
            ],
            [
                'name' => 'Policy Update',
                'key' => 'policy_update',
                'title' => 'Updated System Policies - Please Review',
                'message' => "We have updated our system policies and procedures. Please review the changes carefully. The key changes include [Policy change 1], [Policy change 2], and [Policy change 3], which will be effective on [Insert date]. [Provide summary of major changes]. We ask that you review the updated policies, acknowledge your understanding, and implement the changes in your workflow. The full policy document is available at [Provide link or location]. By continuing to use ACADEX after [date], you agree to comply with these updated policies. If you have any questions, please contact [Contact information].",
                'type' => 'info',
                'priority' => 'high',
                'icon' => 'fa-gavel',
                'description' => 'Notify users about changes to policies, procedures, or guidelines.',
                'order' => 8,
            ],
            [
                'name' => 'System Issue',
                'key' => 'system_issue',
                'title' => 'Known System Issue - We\'re Working On It',
                'message' => "We are currently experiencing a system issue that may affect your experience. [Describe the issue]. This issue is currently affecting [Affected feature 1] and [Affected feature 2]. Our technical team is actively working on a solution and we expect to have this resolved by [Estimated time]. [Provide temporary solution if applicable]. In the meantime, we recommend saving your work frequently, avoiding the affected features if possible, and checking back for updates. We apologize for the inconvenience and appreciate your patience. Updates will be posted as progress is made.",
                'type' => 'warning',
                'priority' => 'high',
                'icon' => 'fa-bug',
                'description' => 'Inform users about known issues and expected resolution times.',
                'order' => 9,
            ],
        ];

        foreach ($templates as $template) {
            AnnouncementTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template
            );
        }
    }
}
