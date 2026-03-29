<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $adminDepartment = Department::query()
            ->where('department_code', 'ASE')
            ->orWhere('department_code', 'SBISM')
            ->orderByRaw("CASE WHEN department_code = 'ASE' THEN 0 ELSE 1 END")
            ->first();

        if (! $adminDepartment) {
            throw new \RuntimeException('No valid department found for admin accounts.');
        }

        foreach (range(1, 5) as $index) {
            $email = "admin{$index}@brokenshire.edu.ph";
            $user = User::firstOrNew(['email' => $email]);

            $user->fill([
                'first_name' => 'Admin',
                'middle_name' => null,
                'last_name' => (string) $index,
                'role' => 3,
                'department_id' => $adminDepartment->id,
                'course_id' => null,
                'is_active' => true,
            ]);

            if (! $user->exists) {
                $user->password = Hash::make('password');
            }

            $user->save();
        }
    }
}
