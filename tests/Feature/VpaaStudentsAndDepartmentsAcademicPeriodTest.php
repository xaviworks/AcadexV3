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

class VpaaStudentsAndDepartmentsAcademicPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_vpaa_students_view_only_shows_selected_academic_period(): void
    {
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'VPAA-' . Str::upper(Str::random(4)),
            'department_description' => 'VPAA Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'VC-' . Str::upper(Str::random(6)),
            'course_description' => 'VPAA Course',
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

        Student::create([
            'first_name' => 'Current',
            'last_name' => 'Learner',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodNew->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Legacy',
            'last_name' => 'Learner',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodOld->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $periodNew->id])
            ->get(route('vpaa.students', ['department_id' => $department->id]));

        $response->assertOk();
        $response->assertViewHas('students', function ($students) {
            return $students->count() === 1
                && $students->first()->first_name === 'Current'
                && $students->first()->last_name === 'Learner';
        });
    }

    public function test_vpaa_departments_view_uses_period_scoped_student_and_instructor_counts(): void
    {
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'CNT-' . Str::upper(Str::random(4)),
            'department_description' => 'Counted Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'COUNT-' . Str::upper(Str::random(5)),
            'course_description' => 'Counting Course',
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

        Student::create([
            'first_name' => 'Current',
            'last_name' => 'DeptStudent',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodNew->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Legacy',
            'last_name' => 'DeptStudent',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodOld->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $directInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $pivotInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $oldInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        Subject::create([
            'subject_code' => 'DNEW-' . Str::upper(Str::random(6)),
            'subject_description' => 'Direct New Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodNew->id,
            'instructor_id' => $directInstructor->id,
            'is_deleted' => false,
        ]);

        $pivotSubject = Subject::create([
            'subject_code' => 'PNEW-' . Str::upper(Str::random(6)),
            'subject_description' => 'Pivot New Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodNew->id,
            'instructor_id' => null,
            'is_deleted' => false,
        ]);

        $pivotSubject->instructors()->attach($pivotInstructor->id);

        Subject::create([
            'subject_code' => 'DOLD-' . Str::upper(Str::random(6)),
            'subject_description' => 'Direct Old Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodOld->id,
            'instructor_id' => $oldInstructor->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $periodNew->id])
            ->get(route('vpaa.departments'));

        $response->assertOk();

        /** @var \Illuminate\Support\Collection $departments */
        $departments = $response->viewData('departments');
        $targetDepartment = $departments->firstWhere('id', $department->id);

        $this->assertNotNull($targetDepartment);
        $this->assertSame(1, $targetDepartment->student_count);
        $this->assertSame(2, $targetDepartment->instructor_count);
    }

    public function test_vpaa_instructors_view_only_lists_teaching_users_in_selected_period(): void
    {
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'INS-' . Str::upper(Str::random(4)),
            'department_description' => 'Instructor Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'IAC-' . Str::upper(Str::random(5)),
            'course_description' => 'Instructor Assignment Course',
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

        $currentInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
            'email' => 'vpaa-current@example.test',
        ]);

        $legacyInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
            'email' => 'vpaa-legacy@example.test',
        ]);

        $nonTeachingInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
            'email' => 'vpaa-nonteaching@example.test',
        ]);

        Subject::create([
            'subject_code' => 'VIN-' . Str::upper(Str::random(6)),
            'subject_description' => 'Current Instructor Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodNew->id,
            'instructor_id' => $currentInstructor->id,
            'is_deleted' => false,
        ]);

        Subject::create([
            'subject_code' => 'VIO-' . Str::upper(Str::random(6)),
            'subject_description' => 'Legacy Instructor Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodOld->id,
            'instructor_id' => $legacyInstructor->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $periodNew->id])
            ->get(route('vpaa.instructors', ['department_id' => $department->id]));

        $response->assertOk();
        $response->assertSee('vpaa-current@example.test');
        $response->assertDontSee('vpaa-legacy@example.test');
        $response->assertDontSee('vpaa-nonteaching@example.test');
    }
}
