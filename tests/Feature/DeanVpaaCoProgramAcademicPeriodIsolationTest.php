<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseOutcomes;
use App\Models\Department;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeanVpaaCoProgramAcademicPeriodIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vpaa_co_program_report_only_uses_selected_academic_period_data(): void
    {
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'VPP-' . Str::upper(Str::random(4)),
            'department_description' => 'VPAA Program Report Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'VPR-' . Str::upper(Str::random(5)),
            'course_description' => 'VPAA Program Report Course',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $oldPeriod = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $newPeriod = AcademicPeriod::create([
            'academic_year' => '2028-2029',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $this->seedCourseOutcomeDataForPeriod($course, $department, $newPeriod, 80, $vpaa->id);
        $this->seedCourseOutcomeDataForPeriod($course, $department, $oldPeriod, 10, $vpaa->id);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $newPeriod->id])
            ->get(route('vpaa.reports.co-program', ['department_id' => $department->id]));

        $response->assertOk();
        $response->assertSeeText($course->course_code);
        $response->assertSeeText('80.00%');
        $response->assertSeeText('80/100 | target 75%');
        $response->assertDontSeeText('45.00%');
        $response->assertDontSeeText('90/200 | target 75%');
    }

    public function test_dean_co_program_report_only_uses_selected_academic_period_data(): void
    {
        $department = Department::create([
            'department_code' => 'DPP-' . Str::upper(Str::random(4)),
            'department_description' => 'Dean Program Report Department',
            'is_deleted' => false,
        ]);

        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $department->id,
        ]);

        $course = Course::create([
            'course_code' => 'DPR-' . Str::upper(Str::random(5)),
            'course_description' => 'Dean Program Report Course',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $oldPeriod = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $newPeriod = AcademicPeriod::create([
            'academic_year' => '2028-2029',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $this->seedCourseOutcomeDataForPeriod($course, $department, $newPeriod, 80, $dean->id);
        $this->seedCourseOutcomeDataForPeriod($course, $department, $oldPeriod, 10, $dean->id);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $newPeriod->id])
            ->get(route('dean.reports.co-program'));

        $response->assertOk();
        $response->assertSeeText($course->course_code);
        $response->assertSeeText('80.0%');
        $response->assertSeeText('80/100 | target 75%');
        $response->assertDontSeeText('45.0%');
        $response->assertDontSeeText('90/200 | target 75%');
    }

    private function seedCourseOutcomeDataForPeriod(
        Course $course,
        Department $department,
        AcademicPeriod $period,
        int $score,
        int $actorId
    ): void {
        $suffix = Str::upper(Str::random(4));

        $subject = Subject::create([
            'subject_code' => 'CPR-' . $suffix,
            'subject_description' => 'Program report subject ' . $suffix,
            'academic_period_id' => $period->id,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $courseOutcome = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'CO1-' . $suffix,
            'description' => 'Program report CO',
            'target_percentage' => 75,
            'created_by' => $actorId,
            'updated_by' => $actorId,
            'is_deleted' => false,
        ]);

        $student = Student::create([
            'first_name' => 'Student' . $suffix,
            'last_name' => 'Program',
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
            'title' => 'Quiz ' . $suffix,
            'course_outcome_id' => $courseOutcome->id,
            'number_of_items' => 100,
            'is_deleted' => false,
        ]);

        Score::create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'score' => $score,
            'is_deleted' => false,
        ]);
    }
}
