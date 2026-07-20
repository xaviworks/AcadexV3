<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use App\Models\UnverifiedUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GEInstructorSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo instructor seeding is only allowed in local or testing environments.');
        }

        $geDepartment = Department::where('department_code', 'GE')->first();
        $course = Course::first(); // Get any course

        if (! $geDepartment) {
            echo "GE Department not found. Please run DepartmentsTableSeeder first.\n";

            return;
        }

        if (! $course) {
            echo "No courses found. Please run CoursesTableSeeder first.\n";

            return;
        }

        $user = UnverifiedUser::firstOrNew(['email' => 'geinstructor@brokenshire.edu.ph']);

        $user->fill([
            'first_name' => 'GE',
            'last_name' => 'Instructor',
            'middle_name' => null,
            'department_id' => $geDepartment->id,
            'course_id' => $course->id,
            'email_verified_at' => now(), // Mark as verified so it appears in pending approvals
        ]);

        if (! $user->exists) {
            $user->password = Str::password(32);
        }

        $user->save();

        echo "GE Instructor pending account created/updated successfully.\n";
        echo "Email: geinstructor@brokenshire.edu.ph\n";
        echo "Status: Pending approval\n";
    }
}
