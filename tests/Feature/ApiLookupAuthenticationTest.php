<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLookupAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_courses_api_requires_authentication(): void
    {
        $department = Department::create([
            'department_code' => 'SIT',
            'department_description' => 'School of Information Technology',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'Bachelor of Science in Information Technology',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $this->getJson("/api/department/{$department->id}/courses")
            ->assertUnauthorized();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/department/{$department->id}/courses")
            ->assertOk()
            ->assertJson([
                [
                    'id' => $course->id,
                    'name' => 'Bachelor of Science in Information Technology',
                ],
            ]);
    }

    public function test_duplicate_name_api_requires_authentication(): void
    {
        $this->getJson('/api/check-duplicate-name?first_name=Ada&last_name=Lovelace&email=ada')
            ->assertUnauthorized();

        $this->actingAs(User::factory()->create())
            ->getJson('/api/check-duplicate-name?first_name=Ada&last_name=Lovelace&email=ada')
            ->assertOk()
            ->assertJson(['exists' => false]);
    }
}
