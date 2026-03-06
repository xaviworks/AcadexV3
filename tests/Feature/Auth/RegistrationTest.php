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
        // The app requires a department and course to exist.
        $department = Department::create([
            'department_code' => 'TST',
            'department_description' => 'Test Department',
        ]);
        $course = Course::create([
            'department_id' => $department->id,
            'course_code' => 'BTST',
            'course_description' => 'Bachelor of Testing',
        ]);

        // Registration creates an UnverifiedUser (not a User) and redirects
        // to the email-verification notice for the unverified guard.
        // The email field is the username part only; the controller appends
        // the institutional domain (@brokenshire.edu.ph).
        $response = $this->post('/register', [
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'email'                 => 'testuser123',
            'department_id'         => $department->id,
            'course_id'             => $course->id,
            'password'              => 'Passw0rd!',
            'password_confirmation' => 'Passw0rd!',
        ]);

        $response->assertRedirect(route('unverified.verification.notice', absolute: false));
    }
}
