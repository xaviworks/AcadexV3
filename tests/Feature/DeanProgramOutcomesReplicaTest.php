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

class DeanProgramOutcomesReplicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_co_program_route_shows_course_chooser_for_assigned_department(): void
    {
        $period = $this->createPeriod('2028-2029');

        $departmentA = Department::create([
            'department_code' => 'DPTA',
            'department_description' => 'Department A',
            'is_deleted' => false,
        ]);

        $departmentB = Department::create([
            'department_code' => 'DPTB',
            'department_description' => 'Department B',
            'is_deleted' => false,
        ]);

        $dean = $this->createDeanUser($departmentA->id);

        $courseInDepartment = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'Bachelor of Science in Information Technology',
            'department_id' => $departmentA->id,
            'is_deleted' => false,
        ]);

        $courseOutOfDepartment = Course::create([
            'course_code' => 'BSN',
            'course_description' => 'Bachelor of Science in Nursing',
            'department_id' => $departmentB->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('dean.reports.co-program'));

        $response->assertOk();
        $response->assertViewIs('dean.reports.co-program-courses');
        $response->assertSeeText($courseInDepartment->course_code);
        $response->assertDontSeeText($courseOutOfDepartment->course_code);
    }

    public function test_dean_co_program_route_renders_read_only_chairperson_style_summary_for_selected_program(): void
    {
        $period = $this->createPeriod('2028-2029');

        $department = Department::create([
            'department_code' => 'SBISM',
            'department_description' => 'School of Business, Information Science and Management',
            'is_deleted' => false,
        ]);

        $dean = $this->createDeanUser($department->id);

        $course = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'Bachelor of Science in Information Technology',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $subject = Subject::create([
            'subject_code' => 'IT101',
            'subject_description' => 'Introduction to Information Technology',
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $co = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT101.1',
            'description' => 'Explain fundamental IT concepts',
            'target_percentage' => 75,
            'created_by' => $dean->id,
            'updated_by' => $dean->id,
            'is_deleted' => false,
        ]);

        $student = Student::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
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

        $activity = Activity::create([
            'subject_id' => $subject->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'Quiz 1',
            'course_outcome_id' => $co->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'score' => 88,
            'is_deleted' => false,
        ]);

        $plo = ProgramLearningOutcome::create([
            'course_id' => $course->id,
            'plo_code' => 'PLO1',
            'title' => 'Program Learning Outcome 1',
            'display_order' => 1,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        ProgramLearningOutcomeMapping::create([
            'course_id' => $course->id,
            'program_learning_outcome_id' => $plo->id,
            'co_code' => 'CO1',
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('dean.reports.co-program', ['course_id' => $course->id]));

        $response->assertOk();
        $response->assertViewIs('dean.reports.co-program');
        $response->assertSeeText('Program Outcomes Summary');
        $response->assertSeeText($course->course_code);
        $response->assertSeeText('PLO1');
        $response->assertSeeText('88.00%');
        $response->assertSeeText('Target 75.00%');
        $response->assertSeeText('CO1');
        $response->assertDontSeeText('Configure PLOs');
        $response->assertDontSee('configurePloModal');
    }

    public function test_dean_co_program_route_returns_not_found_for_course_outside_assigned_department(): void
    {
        $period = $this->createPeriod('2028-2029');

        $departmentA = Department::create([
            'department_code' => 'DPTA',
            'department_description' => 'Department A',
            'is_deleted' => false,
        ]);

        $departmentB = Department::create([
            'department_code' => 'DPTB',
            'department_description' => 'Department B',
            'is_deleted' => false,
        ]);

        $dean = $this->createDeanUser($departmentA->id);

        $courseInDepartmentB = Course::create([
            'course_code' => 'BSN',
            'course_description' => 'Bachelor of Science in Nursing',
            'department_id' => $departmentB->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('dean.reports.co-program', ['course_id' => $courseInDepartmentB->id]));

        $response->assertNotFound();
    }

    private function createDeanUser(int $departmentId): User
    {
        return User::factory()->createOne([
            'role' => 2,
            'department_id' => $departmentId,
        ]);
    }

    private function createPeriod(string $academicYear): AcademicPeriod
    {
        return AcademicPeriod::create([
            'academic_year' => $academicYear,
            'semester' => '1st',
            'is_deleted' => false,
        ]);
    }
}
