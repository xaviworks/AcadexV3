<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\FinalGrade;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\TermGrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstructorStudentDropBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_drop_marks_enrollment_deleted_and_sets_final_grade_remarks_to_dropped(): void
    {
        [$instructor, $subject, $student, $period] = $this->seedInstructorSubjectStudent();

        FinalGrade::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'final_grade' => 91.50,
            'remarks' => 'Passed',
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
        ]);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->delete(route('instructor.students.drop', $student->id), [
                'subject_id' => $subject->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('student_subjects', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'is_deleted' => true,
        ]);

        $this->assertDatabaseHas('final_grades', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'remarks' => 'Dropped',
            'is_deleted' => false,
        ]);
    }

    public function test_dropped_student_remains_visible_in_manage_students_list_with_dropped_status(): void
    {
        [$instructor, $subject, $student, $period] = $this->seedInstructorSubjectStudent();

        StudentSubject::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->update(['is_deleted' => true]);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('instructor.students.index', [
                'subject_id' => $subject->id,
            ]));

        $response->assertOk();
        $response->assertSeeText($student->last_name . ', ' . $student->first_name);
        $response->assertSeeText('Dropped');
    }

    public function test_reenroll_restores_student_enrollment_and_clears_dropped_remark(): void
    {
        [$instructor, $subject, $student, $period] = $this->seedInstructorSubjectStudent();

        StudentSubject::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->update(['is_deleted' => true]);

        FinalGrade::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'final_grade' => null,
            'remarks' => 'Dropped',
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
        ]);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->patch(route('instructor.students.reenroll', $student->id), [
                'subject_id' => $subject->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('student_subjects', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'is_deleted' => false,
        ]);

        $this->assertDatabaseHas('final_grades', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'remarks' => null,
        ]);
    }

    public function test_dropped_student_is_excluded_from_grade_computation_student_list(): void
    {
        [$instructor, $subject, $student, $period] = $this->seedInstructorSubjectStudent();

        StudentSubject::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->update(['is_deleted' => true]);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('instructor.grades.index', [
                'subject_id' => $subject->id,
                'term' => 'midterm',
            ]));

        $response->assertOk();
        $response->assertDontSeeText($student->last_name . ', ' . $student->first_name);
    }

    public function test_dropped_student_keeps_prelim_grade_in_final_grades_with_dropped_remark(): void
    {
        [$instructor, $subject, $student, $period] = $this->seedInstructorSubjectStudent();

        $this->seedTerms();

        TermGrade::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'term_id' => 1,
            'term_grade' => 88.00,
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
        ]);

        FinalGrade::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
            ],
            [
                'academic_period_id' => $period->id,
                'remarks' => 'Dropped',
                'is_deleted' => false,
                'created_by' => $instructor->id,
                'updated_by' => $instructor->id,
            ]
        );

        StudentSubject::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->update(['is_deleted' => true]);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('instructor.final-grades.index', [
                'subject_id' => $subject->id,
            ]));

        $response->assertOk();
        $response->assertSeeText($student->last_name . ', ' . $student->first_name);
        $response->assertSeeText('88');
        $response->assertSeeText('Dropped');

        $finalData = $response->viewData('finalData');
        $row = collect($finalData)->first(fn($item) => $item['student']->id === $student->id);

        $this->assertNotNull($row);
        $this->assertSame(88.00, (float) $row['prelim']);
        $this->assertNull($row['midterm']);
        $this->assertNull($row['prefinal']);
        $this->assertNull($row['final']);
        $this->assertNull($row['final_average']);
        $this->assertSame('Dropped', $row['remarks']);
    }

    public function test_dropped_student_appears_in_term_report_with_saved_prelim_grade(): void
    {
        [$instructor, $subject, $student, $period] = $this->seedInstructorSubjectStudent();

        $this->seedTerms();

        TermGrade::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'term_id' => 1,
            'term_grade' => 86.00,
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
        ]);

        StudentSubject::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->update(['is_deleted' => true]);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('instructor.final-grades.term-report', [
                'subject_id' => $subject->id,
                'term' => 'prelim',
            ]));

        $response->assertOk();
        $response->assertSeeText($student->last_name . ', ' . $student->first_name);
        $response->assertSeeText('86');
    }

    private function seedInstructorSubjectStudent(): array
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2028-2029',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $department = Department::create([
            'department_code' => 'ASE',
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'BSPSY',
            'course_description' => 'Bachelor of Science in Psychology',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        /** @var User $instructor */
        $instructor = User::factory()->create([
            'role' => 0,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'subject_code' => 'PSY-101',
            'subject_description' => 'Introduction to Psychology',
            'year_level' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'instructor_id' => $instructor->id,
            'is_deleted' => false,
        ]);

        $student = Student::create([
            'first_name' => 'Jane',
            'middle_name' => 'Q',
            'last_name' => 'Doe',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
        ]);

        StudentSubject::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'is_deleted' => false,
        ]);

        return [$instructor, $subject, $student, $period];
    }

    private function seedTerms(): void
    {
        $terms = [
            1 => ['term_name' => 'Prelim', 'name' => 'Prelim'],
            2 => ['term_name' => 'Midterm', 'name' => 'Midterm'],
            3 => ['term_name' => 'Prefinal', 'name' => 'Prefinal'],
            4 => ['term_name' => 'Final', 'name' => 'Final'],
        ];

        foreach ($terms as $id => $payload) {
            DB::table('terms')->updateOrInsert(
                ['id' => $id],
                $payload + [
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
