<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstructorDeactivationSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip tests if session driver is not database
        if (config('session.driver') !== 'database') {
            $this->markTestSkipped('These tests require database session driver.');
        }

        // Skip tests if sessions table doesn't exist
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            $this->markTestSkipped('Sessions table with user_id column required.');
        }
    }

    /**
     * Helper to create a department directly.
     */
    protected function createDepartment(string $code, string $description = 'Test Department'): Department
    {
        return Department::create([
            'department_code' => $code,
            'department_description' => $description,
            'is_deleted' => false,
        ]);
    }

    /**
     * Create a mock session for a user in the database.
     */
    protected function createSessionForUser(User $user): string
    {
        $sessionId = bin2hex(random_bytes(20));

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'payload' => base64_encode(serialize([])),
            'last_activity' => time(),
        ]);

        return $sessionId;
    }

    public function test_session_service_invalidates_user_sessions()
    {
        // Create an instructor with active sessions
        $instructor = User::factory()->create([
            'role' => 0,
            'is_active' => true,
        ]);

        // Create multiple sessions for the instructor
        $this->createSessionForUser($instructor);
        $this->createSessionForUser($instructor);
        $this->createSessionForUser($instructor);

        // Verify sessions exist
        $this->assertEquals(3, SessionService::getActiveSessionCount($instructor));
        $this->assertTrue(SessionService::hasActiveSessions($instructor));

        // Invalidate sessions
        $result = SessionService::invalidateUserSessions($instructor);

        // Verify all sessions were deleted
        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['sessions_deleted']);
        $this->assertEquals(0, SessionService::getActiveSessionCount($instructor));
        $this->assertFalse(SessionService::hasActiveSessions($instructor));
    }

    public function test_chairperson_deactivating_instructor_invalidates_sessions()
    {
        // Create departments (non-GE)
        $department = $this->createDepartment('IT', 'Information Technology');
        $geDepartment = $this->createDepartment('GE', 'General Education');

        // Create chairperson
        $chairperson = User::factory()->create([
            'role' => 1,
            'is_active' => true,
            'department_id' => $department->id,
            'course_id' => 1,
        ]);

        // Create instructor in the same department
        $instructor = User::factory()->create([
            'role' => 0,
            'is_active' => true,
            'department_id' => $department->id,
            'course_id' => 1,
        ]);

        // Create sessions for the instructor
        $this->createSessionForUser($instructor);
        $this->createSessionForUser($instructor);

        // Verify sessions exist before deactivation
        $this->assertEquals(2, SessionService::getActiveSessionCount($instructor));

        // Deactivate the instructor
        $response = $this->actingAs($chairperson)
            ->post(route('chairperson.deactivateInstructor', $instructor->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify instructor is deactivated
        $instructor->refresh();
        $this->assertFalse($instructor->is_active);

        // Verify all sessions were invalidated
        $this->assertEquals(0, SessionService::getActiveSessionCount($instructor));
    }

    public function test_ge_coordinator_deactivating_ge_instructor_invalidates_sessions()
    {
        // Create GE department
        $geDepartment = $this->createDepartment('GE', 'General Education');

        // Create GE Coordinator
        $geCoordinator = User::factory()->create([
            'role' => 2,
            'is_active' => true,
            'department_id' => $geDepartment->id,
        ]);

        // Create instructor in GE department
        $instructor = User::factory()->create([
            'role' => 0,
            'is_active' => true,
            'department_id' => $geDepartment->id,
            'can_teach_ge' => true,
        ]);

        // Create sessions for the instructor
        $this->createSessionForUser($instructor);

        // Verify session exists before deactivation
        $this->assertEquals(1, SessionService::getActiveSessionCount($instructor));

        // Deactivate the instructor
        $response = $this->actingAs($geCoordinator)
            ->post(route('gecoordinator.deactivateInstructor', $instructor->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify instructor is deactivated
        $instructor->refresh();
        $this->assertFalse($instructor->is_active);

        // Verify all sessions were invalidated
        $this->assertEquals(0, SessionService::getActiveSessionCount($instructor));
    }

    public function test_ge_coordinator_removing_ge_access_does_not_invalidate_sessions()
    {
        // Create departments
        $itDepartment = $this->createDepartment('IT', 'Information Technology');
        $geDepartment = $this->createDepartment('GE', 'General Education');

        // Create GE Coordinator
        $geCoordinator = User::factory()->create([
            'role' => 2,
            'is_active' => true,
            'department_id' => $geDepartment->id,
        ]);

        // Create instructor in non-GE department with GE teaching access
        $instructor = User::factory()->create([
            'role' => 0,
            'is_active' => true,
            'department_id' => $itDepartment->id,
            'can_teach_ge' => true,
        ]);

        // Create sessions for the instructor
        $this->createSessionForUser($instructor);

        // Verify session exists before removing GE access
        $this->assertEquals(1, SessionService::getActiveSessionCount($instructor));

        // Remove GE access (not full deactivation)
        $response = $this->actingAs($geCoordinator)
            ->post(route('gecoordinator.deactivateInstructor', $instructor->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify instructor is still active (only GE access removed)
        $instructor->refresh();
        $this->assertTrue($instructor->is_active);
        $this->assertFalse($instructor->can_teach_ge);

        // Sessions should NOT be invalidated since account is still active
        $this->assertEquals(1, SessionService::getActiveSessionCount($instructor));
    }
}
