<?php

namespace Tests\Feature\Auth;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $department = Department::create([
            'department_code' => 'BSIT',
            'department_description' => 'BS Information Technology',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'BS Information Technology',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'middle_name' => 'QA',
            'last_name' => 'User',
            'email' => 'testuser',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $this->assertAuthenticated('unverified');
        $response->assertRedirect(route('unverified.verification.notice', absolute: false));
        $this->assertDatabaseHas('unverified_users', [
            'email' => 'testuser@brokenshire.edu.ph',
            'department_id' => $department->id,
            'course_id' => $course->id,
        ]);
    }
}
