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

class StudentFormattedQuerySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_vpaa_student_search_matches_formatted_last_first_middle_query(): void
    {
        [$department, $course, $student] = $this->seedStudentWithSubject();

        /** @var User $vpaa */
        $vpaa = User::factory()->create([
            'role' => 5,
            'department_id' => $department->id,
            'course_id' => $course->id,
        ]);

        $response = $this
            ->actingAs($vpaa)
            ->get(route('vpaa.reports.co-student', [
                'student_query' => 'Aquino, Carlos Torres',
            ]));

        $response->assertOk();
        $response->assertSeeText('Search Results');
        $response->assertSeeText($student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name);
        $response->assertDontSeeText('No Students Found');
    }

    public function test_dean_student_search_matches_formatted_last_first_middle_query(): void
    {
        [$department, $course, $student] = $this->seedStudentWithSubject();

        /** @var User $dean */
        $dean = User::factory()->create([
            'role' => 2,
            'department_id' => $department->id,
            'course_id' => $course->id,
        ]);

        $response = $this
            ->actingAs($dean)
            ->get(route('dean.reports.co-student', [
                'student_query' => 'Aquino, Carlos Torres',
            ]));

        $response->assertOk();
        $response->assertSeeText('Search Results');
        $response->assertSeeText($student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name);
        $response->assertDontSeeText('No Students Found');
    }

    private function seedStudentWithSubject(): array
    {
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

        return [$department, $course, $student];
    }
}
