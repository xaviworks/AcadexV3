<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class InstructorDashboardTest extends TestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/instructor/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_instructor_is_redirected_to_dashboard_route(): void
    {
        // Use a non-persisted user instance to avoid running migrations in this test
        $user = User::factory()->make(['role' => 0]);

        $response = $this->actingAs($user)->get('/instructor/dashboard');

        // The dashboard route will redirect to the academic period selection page if no academic period is set
        $response->assertRedirect(route('select.academicPeriod', absolute: false));
    }
}
