<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChairpersonStudentSuggestionSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_chairperson_can_resolve_selected_student_by_student_id_even_with_formatted_query(): void
    {
        $geDepartment = Department::create([
            'department_code' => 'GE',
            'department_description' => 'General Education',
            'is_deleted' => false,
        ]);

        $department = Department::create([
            'department_code' => 'BSIT',
            'department_description' => 'BSIT Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'BS Information Technology',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        /** @var User $chairperson */
        $chairperson = User::factory()->create([
            'role' => 1,
            'department_id' => $department->id,
            'course_id' => $course->id,
        ]);

        $student = Student::create([
            'first_name' => 'Carlos',
            'middle_name' => 'Torres',
            'last_name' => 'Aquino',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        $subject = Subject::create([
            'subject_code' => 'IT-101',
            'subject_description' => 'Introduction to Computing',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'is_deleted' => false,
        ]);

        StudentSubject::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'is_deleted' => false,
        ]);

        $formattedQuery = 'Aquino, Carlos Torres';

        $response = $this
            ->actingAs($chairperson)
            ->get(route('chairperson.reports.co-student', [
                'student_query' => $formattedQuery,
                'student_id' => $student->id,
            ]));

        $response->assertOk();
        $response->assertSeeText('Selected Student');
        $response->assertSeeText($formattedQuery);
        $response->assertDontSeeText('No Students Found');
    }
}
