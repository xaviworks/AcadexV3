<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleRouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleRoutes')]
    public function test_role_route_groups_reject_users_without_the_required_role(string $routeName, int $requiredRole): void
    {
        $unauthorizedRole = $requiredRole === 3 ? 0 : 3;
        $user = User::factory()->create(['role' => $unauthorizedRole]);

        $this->actingAs($user)
            ->get(route($routeName))
            ->assertForbidden();
    }

    public static function roleRoutes(): array
    {
        return [
            'chairperson' => ['chairperson.instructors', 1],
            'GE coordinator' => ['gecoordinator.instructors', 4],
            'instructor' => ['instructor.dashboard', 0],
            'dean' => ['dean.instructors', 2],
            'admin' => ['admin.departments', 3],
            'VPAA' => ['vpaa.dashboard', 5],
            'curriculum subject import' => ['curriculum.selectSubjects', 1],
        ];
    }
}
