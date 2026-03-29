<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\UnverifiedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GECanonicalRegistrationModeTest extends TestCase
{
    use RefreshDatabase;

    private const LEGACY_GE_PAYLOAD_ERROR = 'Legacy GE-department registration payloads are no longer eligible. Use ASE department with the GE program selection.';

    public function test_canonical_mode_ge_queue_excludes_legacy_ge_department_non_ge_course_registrations(): void
    {
        [$period, $aseDepartment, $geCourse, $legacyCourse, $coordinator] = $this->seedCanonicalContext();

        $canonicalPending = UnverifiedUser::create([
            'first_name' => 'Canonical',
            'middle_name' => null,
            'last_name' => 'Pending',
            'email' => 'canonical.pending@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => $aseDepartment->id,
            'course_id' => $geCourse->id,
            'email_verified_at' => now(),
        ]);

        $legacyPending = UnverifiedUser::create([
            'first_name' => 'Legacy',
            'middle_name' => null,
            'last_name' => 'Pending',
            'email' => 'legacy.pending.queue@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => Department::generalEducation()->id,
            'course_id' => $legacyCourse->id,
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('gecoordinator.accounts.index'));

        $response->assertOk();
        $response->assertViewHas('pendingAccounts', function ($pendingAccounts) use ($canonicalPending, $legacyPending) {
            return $pendingAccounts->contains('id', $canonicalPending->id)
                && !$pendingAccounts->contains('id', $legacyPending->id);
        });
    }

    public function test_canonical_mode_approve_allows_canonical_alias_but_blocks_legacy_department_only_registration(): void
    {
        [$period, $aseDepartment, $geCourse, $legacyCourse, $coordinator] = $this->seedCanonicalContext();

        $canonicalPending = UnverifiedUser::create([
            'first_name' => 'Canonical',
            'middle_name' => null,
            'last_name' => 'Approve',
            'email' => 'canonical.approve@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => $aseDepartment->id,
            'course_id' => $geCourse->id,
            'email_verified_at' => now(),
        ]);

        $legacyPending = UnverifiedUser::create([
            'first_name' => 'Legacy',
            'middle_name' => null,
            'last_name' => 'Approve',
            'email' => 'legacy.approve@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => Department::generalEducation()->id,
            'course_id' => $legacyCourse->id,
            'email_verified_at' => now(),
        ]);

        $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.accounts.approve', ['id' => $canonicalPending->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('unverified_users', ['id' => $canonicalPending->id]);
        $this->assertDatabaseHas('users', ['email' => 'canonical.approve@brokenshire.edu.ph']);

        $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.accounts.approve', ['id' => $legacyPending->id]))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('unverified_users', ['id' => $legacyPending->id]);
        $this->assertDatabaseMissing('users', ['email' => 'legacy.approve@brokenshire.edu.ph']);
    }

    public function test_canonical_mode_reject_blocks_legacy_department_only_registration(): void
    {
        [$period, $aseDepartment, $geCourse, $legacyCourse, $coordinator] = $this->seedCanonicalContext();

        $canonicalPending = UnverifiedUser::create([
            'first_name' => 'Canonical',
            'middle_name' => null,
            'last_name' => 'Reject',
            'email' => 'canonical.reject@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => $aseDepartment->id,
            'course_id' => $geCourse->id,
            'email_verified_at' => now(),
        ]);

        $legacyPending = UnverifiedUser::create([
            'first_name' => 'Legacy',
            'middle_name' => null,
            'last_name' => 'Reject',
            'email' => 'legacy.reject@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => Department::generalEducation()->id,
            'course_id' => $legacyCourse->id,
            'email_verified_at' => now(),
        ]);

        $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.accounts.reject', ['id' => $canonicalPending->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('unverified_users', ['id' => $canonicalPending->id]);

        $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.accounts.reject', ['id' => $legacyPending->id]))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('unverified_users', ['id' => $legacyPending->id]);
    }

    public function test_canonical_mode_unverified_email_verification_uses_chairperson_message_for_legacy_department_only_registration(): void
    {
        $aseDepartment = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'Arts and Sciences Education',
            'is_deleted' => false,
        ]);

        $geDepartment = Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'General Education',
            'is_deleted' => false,
        ]);

        Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $aseDepartment->id,
            'is_deleted' => false,
        ]);

        $legacyCourse = Course::create([
            'course_code' => 'LEGACYMAIL' . random_int(100, 999),
            'course_description' => 'Legacy Email Verification Course',
            'department_id' => $geDepartment->id,
            'is_deleted' => false,
        ]);

        $pending = UnverifiedUser::create([
            'first_name' => 'Legacy',
            'middle_name' => null,
            'last_name' => 'Mail',
            'email' => 'legacy.mail@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => $geDepartment->id,
            'course_id' => $legacyCourse->id,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'unverified.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $pending->id,
                'hash' => sha1($pending->email),
            ]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas(
            'status',
            'Your email has been verified successfully! Your account request is now pending Department Chairperson approval.'
        );
    }

    public function test_canonical_mode_approve_returns_explicit_error_for_legacy_payload(): void
    {
        [$period, , , $legacyCourse, $coordinator] = $this->seedCanonicalContext();

        $legacyPending = UnverifiedUser::create([
            'first_name' => 'Legacy',
            'middle_name' => null,
            'last_name' => 'Approve Message',
            'email' => 'legacy.approve.message@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => Department::generalEducation()->id,
            'course_id' => $legacyCourse->id,
            'email_verified_at' => now(),
        ]);

        $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.accounts.approve', ['id' => $legacyPending->id]))
            ->assertSessionHasErrors([
                'error' => self::LEGACY_GE_PAYLOAD_ERROR,
            ]);

        $this->assertDatabaseHas('unverified_users', ['id' => $legacyPending->id]);
    }

    public function test_canonical_mode_reject_returns_explicit_error_for_legacy_payload(): void
    {
        [$period, , , $legacyCourse, $coordinator] = $this->seedCanonicalContext();

        $legacyPending = UnverifiedUser::create([
            'first_name' => 'Legacy',
            'middle_name' => null,
            'last_name' => 'Reject Message',
            'email' => 'legacy.reject.message@brokenshire.edu.ph',
            'password' => Hash::make('Password1!'),
            'department_id' => Department::generalEducation()->id,
            'course_id' => $legacyCourse->id,
            'email_verified_at' => now(),
        ]);

        $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.accounts.reject', ['id' => $legacyPending->id]))
            ->assertSessionHasErrors([
                'error' => self::LEGACY_GE_PAYLOAD_ERROR,
            ]);

        $this->assertDatabaseHas('unverified_users', ['id' => $legacyPending->id]);
    }

    /**
     * @return array{0: AcademicPeriod, 1: Department, 2: Course, 3: Course, 4: User}
     */
    private function seedCanonicalContext(): array
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $aseDepartment = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'Arts and Sciences Education',
            'is_deleted' => false,
        ]);

        $geDepartment = Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'General Education',
            'is_deleted' => false,
        ]);

        $geCourse = Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $aseDepartment->id,
            'is_deleted' => false,
        ]);

        $legacyCourse = Course::create([
            'course_code' => 'LEGACY' . random_int(1000, 9999),
            'course_description' => 'Legacy GE Department Course',
            'department_id' => $geDepartment->id,
            'is_deleted' => false,
        ]);

        $coordinator = User::factory()->create([
            'role' => 4,
            'department_id' => $geDepartment->id,
            'is_active' => true,
        ]);

        return [$period, $aseDepartment, $geCourse, $legacyCourse, $coordinator];
    }
}
