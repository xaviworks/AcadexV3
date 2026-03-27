<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseOutcomes;
use App\Models\Department;
use App\Models\ProgramLearningOutcome;
use App\Models\ProgramLearningOutcomeMapping;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChairpersonProgramOutcomesPloTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_visit_creates_default_plos_for_chairperson_program_report(): void
    {
        [$chairperson, $period, $course] = $this->createChairpersonContext();

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('Program');
        $response->assertSee('PLO1');
        $response->assertSee('PLO5');
        $response->assertSee('Configure PLOs');

        $this->assertDatabaseCount('program_learning_outcomes', 5);
        $this->assertDatabaseHas('program_learning_outcomes', [
            'course_id' => $course->id,
            'plo_code' => 'PLO1',
            'title' => 'Program Learning Outcome 1',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    public function test_program_report_displays_mapped_plo_average_and_dash_for_unmapped_plo(): void
    {
        [$chairperson, $period, $course, $department] = $this->createChairpersonContext();

        $plo1 = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'PLO1',
            'title' => 'Engineering Knowledge',
            'display_order' => 1,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $plo2 = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'PLO2',
            'title' => 'Problem Analysis',
            'display_order' => 2,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $plo1->id,
            'co_code' => 'CO1',
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $plo1->id,
            'co_code' => 'CO2',
        ]);

        $subject = Subject::create([
            'subject_code' => 'BSIT-101',
            'subject_description' => 'Introduction to Programming',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $co1 = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'BSIT101.1',
            'description' => 'Understand concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $co2 = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO2',
            'co_identifier' => 'BSIT101.2',
            'description' => 'Build simple programs',
            'target_percentage' => 70,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $student = Student::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        DB::table('student_subjects')->insert([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'section' => 'A',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $activity1 = Activity::create([
            'subject_id' => $subject->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'Quiz 1',
            'course_outcome_id' => $co1->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        $activity2 = Activity::create([
            'subject_id' => $subject->id,
            'term' => 'midterm',
            'type' => 'quiz',
            'title' => 'Quiz 2',
            'course_outcome_id' => $co2->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activity1->id,
            'student_id' => $student->id,
            'score' => 75,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activity2->id,
            'student_id' => $student->id,
            'score' => 80,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('77.50%');
        $response->assertSee('Target 75.00%');
        $response->assertSee('CO1');
        $response->assertSee('CO2');
        $response->assertSee('Engineering Knowledge');
        $response->assertSee('Problem Analysis');
        $response->assertSee('—');
    }

    public function test_chairperson_cannot_save_more_than_twenty_plos(): void
    {
        [$chairperson, $period] = $this->createChairpersonContext();

        $payload = ['plos' => []];
        for ($i = 1; $i <= 21; $i++) {
            $payload['plos'][] = [
                'code' => 'PLO' . $i,
                'title' => 'Program Learning Outcome ' . $i,
                'display_order' => $i,
                'is_active' => '1',
                'delete' => '0',
            ];
        }

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('chairperson.reports.co-program.plos.save'), $payload);

        $response->assertSessionHasErrors('plos');
    }

    public function test_program_co_rollup_only_counts_students_enrolled_in_each_subject(): void
    {
        [$chairperson, $period, $course, $department] = $this->createChairpersonContext();

        $plo = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'PLO1',
            'title' => 'Engineering Knowledge',
            'display_order' => 1,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $plo->id,
            'co_code' => 'CO1',
        ]);

        $subjectOne = Subject::create([
            'subject_code' => 'IT101',
            'subject_description' => 'Intro to IT',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $subjectTwo = Subject::create([
            'subject_code' => 'IT102',
            'subject_description' => 'Programming 1',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $coSubjectOne = CourseOutcomes::create([
            'subject_id' => $subjectOne->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT101.1',
            'description' => 'Understand concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $coSubjectTwo = CourseOutcomes::create([
            'subject_id' => $subjectTwo->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT102.1',
            'description' => 'Apply concepts',
            'target_percentage' => 80,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $studentOne = Student::create([
            'first_name' => 'Ana',
            'last_name' => 'Lopez',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $studentTwo = Student::create([
            'first_name' => 'Ben',
            'last_name' => 'Reyes',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        DB::table('student_subjects')->insert([
            [
                'student_id' => $studentOne->id,
                'subject_id' => $subjectOne->id,
                'section' => 'A',
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $studentTwo->id,
                'subject_id' => $subjectTwo->id,
                'section' => 'A',
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $activityOne = Activity::create([
            'subject_id' => $subjectOne->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'IT101 Quiz',
            'course_outcome_id' => $coSubjectOne->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        $activityTwo = Activity::create([
            'subject_id' => $subjectTwo->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'IT102 Quiz',
            'course_outcome_id' => $coSubjectTwo->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activityOne->id,
            'student_id' => $studentOne->id,
            'score' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activityTwo->id,
            'student_id' => $studentTwo->id,
            'score' => 50,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-program'));

        $response->assertOk();
        $response->assertSee('75.00%');
        $response->assertSee('Target 80.00%');
        $response->assertSee('Met Expected Outcome');
    }

    private function createChairpersonContext(): array
    {
        $department = Department::create([
            'department_code' => 'SBISM',
            'department_description' => 'School of Business, Information Science and Management',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'Bachelor of Science in Information Technology',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $chairperson = User::factory()->create([
            'role' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        return [$chairperson, $period, $course, $department];
    }
}
