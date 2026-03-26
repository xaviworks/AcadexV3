<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VpaaInstructorsViewTest extends TestCase
{
    use RefreshDatabase;

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
        $response->assertSee('Department');
        $response->assertSee('School of Arts and Science and Education');
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
        $response->assertSee('Filter by Department');
    }
}
