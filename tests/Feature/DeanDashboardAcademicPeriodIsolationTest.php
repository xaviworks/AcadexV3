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

class DeanDashboardAcademicPeriodIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_dashboard_and_poll_only_use_selected_period_and_department_data(): void
    {
        $deanDepartment = Department::create([
            'department_code' => 'DEAN-' . Str::upper(Str::random(4)),
            'department_description' => 'Dean Department',
            'is_deleted' => false,
        ]);

        $otherDepartment = Department::create([
            'department_code' => 'OTHR-' . Str::upper(Str::random(4)),
            'department_description' => 'Other Department',
            'is_deleted' => false,
        ]);

        $deanCourse = Course::create([
            'course_code' => 'DC-' . Str::upper(Str::random(6)),
            'course_description' => 'Dean Course',
            'department_id' => $deanDepartment->id,
            'is_deleted' => false,
        ]);

        $otherCourse = Course::create([
            'course_code' => 'OC-' . Str::upper(Str::random(6)),
            'course_description' => 'Other Course',
            'department_id' => $otherDepartment->id,
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
            'department_id' => $deanDepartment->id,
        ]);

        Student::create([
            'first_name' => 'Current',
            'last_name' => 'One',
            'department_id' => $deanDepartment->id,
            'course_id' => $deanCourse->id,
            'academic_period_id' => $periodNew->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Current',
            'last_name' => 'Two',
            'department_id' => $deanDepartment->id,
            'course_id' => $deanCourse->id,
            'academic_period_id' => $periodNew->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Legacy',
            'last_name' => 'Dean',
            'department_id' => $deanDepartment->id,
            'course_id' => $deanCourse->id,
            'academic_period_id' => $periodOld->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Other',
            'last_name' => 'Department',
            'department_id' => $otherDepartment->id,
            'course_id' => $otherCourse->id,
            'academic_period_id' => $periodNew->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $instructorDirect = User::factory()->createOne([
            'role' => 0,
            'department_id' => $deanDepartment->id,
            'is_active' => true,
        ]);

        $instructorPivot = User::factory()->createOne([
            'role' => 0,
            'department_id' => $deanDepartment->id,
            'is_active' => true,
        ]);

        $instructorOldPeriod = User::factory()->createOne([
            'role' => 0,
            'department_id' => $deanDepartment->id,
            'is_active' => true,
        ]);

        $instructorOtherDepartment = User::factory()->createOne([
            'role' => 0,
            'department_id' => $otherDepartment->id,
            'is_active' => true,
        ]);

        Subject::create([
            'subject_code' => 'SUBD-' . Str::upper(Str::random(6)),
            'subject_description' => 'Dean Current Subject',
            'year_level' => 1,
            'department_id' => $deanDepartment->id,
            'course_id' => $deanCourse->id,
            'academic_period_id' => $periodNew->id,
            'instructor_id' => $instructorDirect->id,
            'is_deleted' => false,
        ]);

        $pivotSubject = Subject::create([
            'subject_code' => 'SUBP-' . Str::upper(Str::random(6)),
            'subject_description' => 'Dean Pivot Subject',
            'year_level' => 1,
            'department_id' => $deanDepartment->id,
            'course_id' => $deanCourse->id,
            'academic_period_id' => $periodNew->id,
            'instructor_id' => null,
            'is_deleted' => false,
        ]);

        $pivotSubject->instructors()->attach($instructorPivot->id);

        Subject::create([
            'subject_code' => 'SUBO-' . Str::upper(Str::random(6)),
            'subject_description' => 'Dean Old Subject',
            'year_level' => 1,
            'department_id' => $deanDepartment->id,
            'course_id' => $deanCourse->id,
            'academic_period_id' => $periodOld->id,
            'instructor_id' => $instructorOldPeriod->id,
            'is_deleted' => false,
        ]);

        Subject::create([
            'subject_code' => 'SUBX-' . Str::upper(Str::random(6)),
            'subject_description' => 'Other Department Subject',
            'year_level' => 1,
            'department_id' => $otherDepartment->id,
            'course_id' => $otherCourse->id,
            'academic_period_id' => $periodNew->id,
            'instructor_id' => $instructorOtherDepartment->id,
            'is_deleted' => false,
        ]);

        $dashboardResponse = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $periodNew->id])
            ->get(route('dashboard'));

        $dashboardResponse->assertOk();

        $studentsPerDepartment = $dashboardResponse->viewData('studentsPerDepartment');
        $studentsPerCourse = $dashboardResponse->viewData('studentsPerCourse');

        $this->assertSame(2, $studentsPerDepartment->sum());
        $this->assertSame(1, $studentsPerDepartment->count());
        $this->assertSame(2, $studentsPerCourse->sum());
        $this->assertSame(1, $studentsPerCourse->count());
        $this->assertSame(2, $dashboardResponse->viewData('totalInstructors'));

        $pollResponse = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $periodNew->id])
            ->getJson(route('dashboard.poll'));

        $pollResponse->assertOk();
        $pollResponse->assertJsonPath('totalStudents', 2);
        $pollResponse->assertJsonPath('totalInstructors', 2);
        $pollResponse->assertJsonPath('totalCourses', 1);
        $pollResponse->assertJsonPath('totalDepartments', 1);
    }
}
