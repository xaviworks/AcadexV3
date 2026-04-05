<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\FinalGrade;
use App\Models\Student;
use App\Models\StudentSubject;
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

    public function test_dean_grades_lists_primary_assigned_instructor_for_selected_course(): void
    {
        $department = Department::create([
            'department_code' => 'DGR-' . Str::upper(Str::random(4)),
            'department_description' => 'Dean Grades Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'DGC-' . Str::upper(Str::random(5)),
            'course_description' => 'Dean Grades Course',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $academicPeriod = AcademicPeriod::create([
            'academic_year' => '2030-2031',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $department->id,
        ]);

        $instructor = User::factory()->createOne([
            'role' => 0,
            'first_name' => 'Primary',
            'last_name' => 'Instructor',
            'department_id' => $department->id,
            'is_active' => true,
            'email' => 'dean-grades-primary@example.test',
        ]);

        // This intentionally uses only subjects.instructor_id (no instructor_subject pivot)
        // to cover the Dean grades regression where primary instructors were omitted.
        Subject::create([
            'subject_code' => 'DGS-' . Str::upper(Str::random(6)),
            'subject_description' => 'Dean Grades Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $academicPeriod->id,
            'instructor_id' => $instructor->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $academicPeriod->id])
            ->get(route('dean.grades', ['course_id' => $course->id]));

        $response->assertOk();
        $response->assertViewHas('instructors', function ($instructors) use ($instructor) {
            return $instructors->pluck('id')->contains($instructor->id);
        });
        $response->assertSeeText('Instructor, Primary');
        $response->assertDontSeeText('No Instructors Available');
    }

    public function test_dean_final_grades_includes_dropped_students_with_dropped_remarks(): void
    {
        $department = Department::create([
            'department_code' => 'DDG-' . Str::upper(Str::random(4)),
            'department_description' => 'Dean Dropped Grades Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'DDC-' . Str::upper(Str::random(5)),
            'course_description' => 'Dean Dropped Grades Course',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $academicPeriod = AcademicPeriod::create([
            'academic_year' => '2031-2032',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $department->id,
        ]);

        $instructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'subject_code' => 'DDS-' . Str::upper(Str::random(6)),
            'subject_description' => 'Dean Dropped Grades Subject',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $academicPeriod->id,
            'instructor_id' => $instructor->id,
            'is_deleted' => false,
        ]);

        $student = Student::create([
            'first_name' => 'Dropped',
            'last_name' => 'Student',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $academicPeriod->id,
            'year_level' => 1,
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
        ]);

        StudentSubject::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'is_deleted' => true,
        ]);

        FinalGrade::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $academicPeriod->id,
            'final_grade' => null,
            'remarks' => 'Dropped',
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $academicPeriod->id])
            ->get(route('dean.grades', [
                'course_id' => $course->id,
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
            ]));

        $response->assertOk();
        $response->assertSeeText('Student, Dropped');
        $response->assertSeeText('Dropped');
        $response->assertDontSeeText('No Students Found');
    }
}
