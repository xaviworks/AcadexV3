<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\UnverifiedUser;
use App\Models\User;
use App\Notifications\InstructorPendingApproval;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GEProgramUnderASEPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_form_hides_legacy_ge_department_option_by_default(): void
    {
        $ase = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        $ge = Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'School of General Education',
            'is_deleted' => false,
        ]);

        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSeeText($ase->department_description);
        $response->assertDontSee('value="' . $ge->id . '"', false);
    }

    public function test_department_courses_api_includes_ge_program_when_loading_ase_department(): void
    {
        $ase = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        $geDepartment = Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'School of General Education',
            'is_deleted' => false,
        ]);

        $aseCourse = Course::create([
            'course_code' => 'TASEPSY',
            'course_description' => 'Test ASE Psychology',
            'department_id' => $ase->id,
            'is_deleted' => false,
        ]);

        $geCourse = Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $geDepartment->id,
            'is_deleted' => false,
        ]);

        $response = $this->getJson('/api/department/' . $ase->id . '/courses');

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $aseCourse->id,
            'name' => $aseCourse->course_description,
        ]);
        $response->assertJsonFragment([
            'id' => $geCourse->id,
            'name' => $geCourse->course_description,
            'is_ge_program' => true,
        ]);
    }

    public function test_ge_targeted_pending_registration_notifies_ge_coordinator_when_department_is_ase(): void
    {
        Notification::fake();

        $ase = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'School of General Education',
            'is_deleted' => false,
        ]);

        $geCourse = Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $ase->id,
            'is_deleted' => false,
        ]);

        /** @var User $coordinator */
        $coordinator = User::factory()->create([
            'role' => 4,
            'department_id' => $ase->id,
            'is_active' => true,
        ]);

        /** @var User $chairperson */
        $chairperson = User::factory()->create([
            'role' => 1,
            'department_id' => $ase->id,
            'course_id' => $geCourse->id,
            'is_active' => true,
        ]);

        $pendingUser = UnverifiedUser::create([
            'first_name' => 'Geo',
            'middle_name' => null,
            'last_name' => 'Instructor',
            'email' => 'geo.instructor@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => $ase->id,
            'course_id' => $geCourse->id,
            'email_verified_at' => now(),
        ]);

        NotificationService::notifyInstructorPending($pendingUser);

        Notification::assertSentTo($coordinator, InstructorPendingApproval::class);
        Notification::assertNotSentTo($chairperson, InstructorPendingApproval::class);
    }

    public function test_ge_coordinator_approval_queue_includes_ase_ge_program_registrations(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $ase = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        $geDepartment = Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'School of General Education',
            'is_deleted' => false,
        ]);

        $geCourse = Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $ase->id,
            'is_deleted' => false,
        ]);

        /** @var User $coordinator */
        $coordinator = User::factory()->create([
            'role' => 4,
            'department_id' => $geDepartment->id,
            'is_active' => true,
        ]);

        $pending = UnverifiedUser::create([
            'first_name' => 'Pending',
            'middle_name' => null,
            'last_name' => 'GE Program',
            'email' => 'pending.ge.program@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => $ase->id,
            'course_id' => $geCourse->id,
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('gecoordinator.accounts.index'));

        $response->assertOk();
        $response->assertViewHas('pendingAccounts', function ($pendingAccounts) use ($pending) {
            return $pendingAccounts->contains('id', $pending->id);
        });
    }

    public function test_vpaa_department_overview_hides_legacy_ge_department_card(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $ase = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        $geDepartment = Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'School of General Education',
            'is_deleted' => false,
        ]);

        Course::create([
            'course_code' => 'TVPAA01',
            'course_description' => 'VPAA Department Listing Program',
            'department_id' => $ase->id,
            'is_deleted' => false,
        ]);

        Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $geDepartment->id,
            'is_deleted' => false,
        ]);

        /** @var User $vpaa */
        $vpaa = User::factory()->create([
            'role' => 5,
            'department_id' => $ase->id,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('vpaa.departments'));

        $response->assertOk();
        $response->assertSeeText($ase->department_description);
        $response->assertDontSeeText($geDepartment->department_description);
    }
}
