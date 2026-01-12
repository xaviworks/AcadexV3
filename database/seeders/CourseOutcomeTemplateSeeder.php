<?php

namespace Database\Seeders;

use App\Models\CourseOutcomeTemplate;
use App\Models\CourseOutcomeTemplateItem;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseOutcomeTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find a chairperson or GE coordinator to be the creator
        $creator = User::whereIn('role', [1, 4])->first();
        
        if (!$creator) {
            $this->command->warn('No chairperson or GE coordinator found. Skipping CO template seeding.');
            return;
        }

        // Universal Template 1: Standard 3 COs
        $template1 = CourseOutcomeTemplate::create([
            'template_name' => 'Standard 3 Course Outcomes',
            'description' => 'Basic template with 3 course outcomes for general education subjects',
            'created_by' => $creator->id,
            'course_id' => null,
            'is_universal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $coItems1 = [
            ['co_code' => 'CO1', 'description' => 'Demonstrate understanding of fundamental concepts and principles', 'order' => 1],
            ['co_code' => 'CO2', 'description' => 'Apply theoretical knowledge to practical situations', 'order' => 2],
            ['co_code' => 'CO3', 'description' => 'Analyze and evaluate information critically', 'order' => 3],
        ];

        foreach ($coItems1 as $item) {
            CourseOutcomeTemplateItem::create([
                'template_id' => $template1->id,
                'co_code' => $item['co_code'],
                'description' => $item['description'],
                'order' => $item['order'],
            ]);
        }

        // Universal Template 2: Comprehensive 5 COs
        $template2 = CourseOutcomeTemplate::create([
            'template_name' => 'Comprehensive 5 Course Outcomes',
            'description' => 'Detailed template with 5 course outcomes for major subjects',
            'created_by' => $creator->id,
            'course_id' => null,
            'is_universal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $coItems2 = [
            ['co_code' => 'CO1', 'description' => 'Demonstrate comprehensive understanding of core concepts and theories', 'order' => 1],
            ['co_code' => 'CO2', 'description' => 'Apply knowledge and skills to solve complex problems', 'order' => 2],
            ['co_code' => 'CO3', 'description' => 'Analyze and synthesize information from multiple sources', 'order' => 3],
            ['co_code' => 'CO4', 'description' => 'Evaluate and critique various approaches and methodologies', 'order' => 4],
            ['co_code' => 'CO5', 'description' => 'Create innovative solutions and demonstrate professional competence', 'order' => 5],
        ];

        foreach ($coItems2 as $item) {
            CourseOutcomeTemplateItem::create([
                'template_id' => $template2->id,
                'co_code' => $item['co_code'],
                'description' => $item['description'],
                'order' => $item['order'],
            ]);
        }

        // Universal Template 3: Advanced 6 COs
        $template3 = CourseOutcomeTemplate::create([
            'template_name' => 'Advanced 6 Course Outcomes',
            'description' => 'Advanced template with 6 course outcomes following Bloom\'s Taxonomy',
            'created_by' => $creator->id,
            'course_id' => null,
            'is_universal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $coItems3 = [
            ['co_code' => 'CO1', 'description' => 'Remember and recall fundamental facts, concepts, and principles', 'order' => 1],
            ['co_code' => 'CO2', 'description' => 'Understand and explain key ideas and relationships', 'order' => 2],
            ['co_code' => 'CO3', 'description' => 'Apply knowledge and techniques to practical scenarios', 'order' => 3],
            ['co_code' => 'CO4', 'description' => 'Analyze complex problems and identify patterns and relationships', 'order' => 4],
            ['co_code' => 'CO5', 'description' => 'Evaluate evidence and make informed judgments', 'order' => 5],
            ['co_code' => 'CO6', 'description' => 'Create original work and innovative solutions', 'order' => 6],
        ];

        foreach ($coItems3 as $item) {
            CourseOutcomeTemplateItem::create([
                'template_id' => $template3->id,
                'co_code' => $item['co_code'],
                'description' => $item['description'],
                'order' => $item['order'],
            ]);
        }

        // Try to create a course-specific template for BSIT
        $bsitCourse = Course::where('course_code', 'BSIT')->first();
        
        if ($bsitCourse) {
            $template4 = CourseOutcomeTemplate::create([
                'template_name' => 'BSIT Programming Course Outcomes',
                'description' => 'Specialized template for BSIT programming courses',
                'created_by' => $creator->id,
                'course_id' => $bsitCourse->id,
                'is_universal' => false,
                'is_active' => true,
                'is_deleted' => false,
            ]);

            $coItems4 = [
                ['co_code' => 'CO1', 'description' => 'Demonstrate proficiency in programming fundamentals and syntax', 'order' => 1],
                ['co_code' => 'CO2', 'description' => 'Design and implement algorithms to solve computational problems', 'order' => 2],
                ['co_code' => 'CO3', 'description' => 'Apply software development best practices and design patterns', 'order' => 3],
                ['co_code' => 'CO4', 'description' => 'Debug, test, and optimize code for performance and maintainability', 'order' => 4],
            ];

            foreach ($coItems4 as $item) {
                CourseOutcomeTemplateItem::create([
                    'template_id' => $template4->id,
                    'co_code' => $item['co_code'],
                    'description' => $item['description'],
                    'order' => $item['order'],
                ]);
            }
        }

        $this->command->info('Course Outcome Templates seeded successfully!');
    }
}
