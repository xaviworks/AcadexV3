<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeanStudentsAcademicPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_students_view_only_shows_selected_academic_period(): void
    {
        $department = Department::create([
            'department_code' => 'DPT-' . Str::upper(Str::random(4)),
            'department_description' => 'Dean Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'CRS-' . Str::upper(Str::random(5)),
            'course_description' => 'Dean Course',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $periodOld = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $periodNew = AcademicPeriod::create([
            'academic_year' => '2028-2029',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $department->id,
        ]);

        Student::create([
            'first_name' => 'Current',
            'last_name' => 'Student',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodNew->id,
            'year_level' => 2,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Legacy',
            'last_name' => 'Student',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodOld->id,
            'year_level' => 2,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $periodNew->id])
            ->get(route('dean.students'));

        $response->assertOk();
        $response->assertViewHas('students', function ($students) {
            return $students->count() === 1
                && $students->first()->first_name === 'Current'
                && $students->first()->last_name === 'Student';
        });
    }

    public function test_dean_instructors_view_only_shows_teaching_instructors_for_selected_period(): void
    {
        $department = Department::create([
            'department_code' => 'DIN-' . Str::upper(Str::random(4)),
            'department_description' => 'Dean Instructor Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'DIR-' . Str::upper(Str::random(5)),
            'course_description' => 'Dean Instructor Course',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $periodOld = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $periodNew = AcademicPeriod::create([
            'academic_year' => '2028-2029',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $department->id,
        ]);

        $currentInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
            'email' => 'current-instructor@example.test',
        ]);

        $legacyInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
            'email' => 'legacy-instructor@example.test',
        ]);

        Subject::create([
            'subject_code' => 'DCI-' . Str::upper(Str::random(6)),
            'subject_description' => 'Current Period Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodNew->id,
            'instructor_id' => $currentInstructor->id,
            'is_deleted' => false,
        ]);

        Subject::create([
            'subject_code' => 'DLI-' . Str::upper(Str::random(6)),
            'subject_description' => 'Legacy Period Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodOld->id,
            'instructor_id' => $legacyInstructor->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $periodNew->id])
            ->get(route('dean.instructors'));

        $response->assertOk();
        $response->assertSee('current-instructor@example.test');
        $response->assertDontSee('legacy-instructor@example.test');
    }
}
