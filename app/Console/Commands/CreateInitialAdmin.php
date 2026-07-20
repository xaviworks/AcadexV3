<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateInitialAdmin extends Command
{
    protected $signature = 'app:create-initial-admin
        {--email=}
        {--first-name=}
        {--last-name=}
        {--password= : Initial password for local/non-production setup only}';

    protected $description = 'Create the first administrator account without exposing a default password';

    public function handle(): int
    {
        if (User::where('role', 3)->exists()) {
            $this->error('An administrator account already exists. Refusing to create another initial admin.');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->confirm('Create the initial administrator in production?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        if (app()->environment('production') && $this->option('password')) {
            $this->error('The --password option is disabled in production because command-line passwords can be exposed in shell history or process lists.');

            return self::FAILURE;
        }

        $email = $this->option('email') ?: $this->ask('Administrator email');
        $firstName = $this->option('first-name') ?: $this->ask('First name', 'Initial');
        $lastName = $this->option('last-name') ?: $this->ask('Last name', 'Administrator');
        $password = $this->option('password') ?? $this->secret('Initial password (leave blank to require password reset)');

        $validator = Validator::make([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'unique:users,email'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'password' => [
                'nullable',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $department = Department::query()
            ->where('department_code', 'ASE')
            ->orWhere('department_code', 'SBISM')
            ->orderByRaw("CASE WHEN department_code = 'ASE' THEN 0 ELSE 1 END")
            ->first();

        if (! $department) {
            $this->error('No department is available for the initial admin. Run ReferenceDataSeeder first.');

            return self::FAILURE;
        }

        User::create([
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => $password ?: Str::password(32),
            'must_change_password' => $password === null || $password === '',
            'role' => 3,
            'department_id' => $department->id,
            'course_id' => null,
            'is_active' => true,
        ]);

        $this->info('Initial administrator created.');

        if ($password === null || $password === '') {
            $this->warn('The temporary password was not displayed. Use the password reset flow or an administrator-controlled reset before first login.');
        }

        return self::SUCCESS;
    }
}
