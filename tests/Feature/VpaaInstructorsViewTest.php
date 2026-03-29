<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VpaaInstructorsViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_instructor_role_is_listed_in_vpaa_instructor_management(): void
    {
        /** @var User $vpaa */
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'SBISM',
            'department_description' => 'School of Business and Information Systems Management',
            'is_deleted' => false,
        ]);

        $activeInstructor = User::factory()->createOne([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
            'first_name' => 'Active',
            'last_name' => 'Instructor',
            'email' => 'active.instructor@example.test',
        ]);

        $chairperson = User::factory()->createOne([
            'role' => 1,
            'department_id' => $department->id,
            'is_active' => true,
            'first_name' => 'Chair',
            'last_name' => 'User',
            'email' => 'chair.user@example.test',
        ]);

        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $department->id,
            'is_active' => true,
            'first_name' => 'Dean',
            'last_name' => 'User',
            'email' => 'dean.user@example.test',
        ]);

        $geCoordinator = User::factory()->createOne([
            'role' => 4,
            'department_id' => $department->id,
            'is_active' => true,
            'first_name' => 'GE',
            'last_name' => 'Coordinator',
            'email' => 'ge.coordinator@example.test',
        ]);

        $response = $this->actingAs($vpaa)->get(route('vpaa.instructors'));

        $response->assertOk();
        $response->assertSee($activeInstructor->name);
        $response->assertSee($activeInstructor->email);
        $response->assertDontSee($chairperson->email);
        $response->assertDontSee($dean->email);
        $response->assertDontSee($geCoordinator->email);
        $response->assertSee('Active Instructors');
        $response->assertSee('<span class="badge bg-light text-muted ms-2">1</span>', false);
    }

    public function test_department_column_is_visible_for_all_departments_filter(): void
    {
        /** @var User $vpaa */
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'ASE',
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        User::factory()->create([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($vpaa)->get(route('vpaa.instructors'));

        $response->assertOk();
        $response->assertSee('<th scope="col" class="px-4 py-3 fw-semibold">Department</th>', false);
        $response->assertSee('ASE');
    }

    public function test_department_column_is_hidden_when_specific_department_is_selected(): void
    {
        /** @var User $vpaa */
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'ASE',
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        User::factory()->create([
            'role' => 0,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($vpaa)->get(route('vpaa.instructors', ['department_id' => $department->id]));

        $response->assertOk();
        $response->assertDontSee('<th scope="col" class="px-4 py-3 fw-semibold">Department</th>', false);
        $response->assertSee('<th scope="col" class="px-4 py-3 fw-semibold">Email</th>', false);
        $response->assertSee('<th scope="col" class="px-4 py-3 fw-semibold">Status</th>', false);
        $response->assertSee('Department');
        $response->assertSee('Clear Filter');
    }
}
