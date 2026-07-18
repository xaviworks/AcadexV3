<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Gate::authorize('admin');

        // Show all users, including instructors (role 0)
        $users = User::orderBy('role', 'asc')->get();

        $departments = Cache::remember('departments:all', 3600, fn () => Department::all());
        $courses = Cache::remember('courses:all', 3600, fn () => Course::all());

        // Detect if the disabled_until column exists so the view can surface a migration notice
        $hasDisabledUntilColumn = Schema::hasColumn('users', 'disabled_until');

        return view('admin.users', compact('users', 'departments', 'courses', 'hasDisabledUntilColumn'));
    }

    public function confirmCreationPassword(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'confirm_password' => 'required|string',
        ]);

        // Get the currently authenticated user
        $user = Auth::user();

        // Check if the entered password matches the stored password
        if (Hash::check($request->confirm_password, $user->password)) {
            // If password matches, proceed with the action (e.g., store the new user or perform other actions)
            // Return a success response for AJAX
            return response()->json(['success' => true, 'message' => 'Password confirmed successfully']);
        }

        // If password is incorrect, return an error message
        return response()->json(['success' => false, 'message' => 'The password you entered is incorrect.']);
    }

    public function store(Request $request)
    {
        $validationRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'regex:/^[^@]+$/', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:1,2,3,5'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
        ];

        // Add department validation for non-admin and non-VPAA roles
        if ($request->role != 3 && $request->role != 5) {
            $validationRules['department_id'] = ['required', 'exists:departments,id'];

            // Course validation based on role
            if ($request->role == 1) { // Chairperson
                $validationRules['course_id'] = ['required', 'exists:courses,id'];
            } elseif ($request->role == 2) { // Dean
                $validationRules['course_id'] = ['nullable', 'exists:courses,id'];
            }
        }

        $request->validate($validationRules);

        $fullEmail = $request->email.'@brokenshire.edu.ph';

        $userData = [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $fullEmail,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ];

        // Add department for non-admin and non-VPAA roles
        if ($request->role != 3 && $request->role != 5) {
            $userData['department_id'] = $request->department_id;

            // Add course_id only if it's provided (for Dean) or required (for Chairperson)
            if ($request->role == 1 || ($request->role == 2 && $request->has('course_id'))) {
                $userData['course_id'] = $request->course_id;
            }
        }

        $newUser = User::create($userData);

        // Send security notification to admins about new user creation
        \App\Listeners\NotifyUserCreated::handle($newUser, Auth::user());

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function disable(Request $request, User $user)
    {
        Gate::authorize('admin');

        // Prevent an admin from disabling their own account
        if (Auth::id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot disable your own account.',
            ], 403);
        }

        $request->validate([
            'duration' => 'required|in:1_week,1_month,indefinite,custom',
            'custom_disable_datetime' => 'required_if:duration,custom|date|after:now',
        ]);

        try {
            $duration = $request->duration;
            $now = now();

            // Calculate disabled_until based on duration
            switch ($duration) {
                case '1_week':
                    if (Schema::hasColumn('users', 'disabled_until')) {
                        $user->disabled_until = $now->copy()->addWeek();
                    }
                    break;
                case '1_month':
                    if (Schema::hasColumn('users', 'disabled_until')) {
                        $user->disabled_until = $now->copy()->addMonth();
                    }
                    break;
                case 'indefinite':
                    if (Schema::hasColumn('users', 'disabled_until')) {
                        // Use a sentinel far-future date that reliably indicates 'indefinite' and fits DATETIME range
                        $user->disabled_until = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '9999-12-31 23:59:59');
                    }
                    break;
                case 'custom':
                    if (Schema::hasColumn('users', 'disabled_until')) {
                        $user->disabled_until = $request->custom_disable_datetime;
                    }
                    break;
            }

            $user->is_active = false;
            $user->save();

            // Force logout the user from all devices if database sessions are enabled
            $driver = config('session.driver');
            if ($driver === 'database' && Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
                // Invalidate remember_token so "remember me" cookie cannot silently recreate the session
                $user->forceFill(['remember_token' => null])->save();
            } else {
                \Illuminate\Support\Facades\Log::warning("Skipping session deletion for user {$user->id} (session driver: {$driver}).");
            }

            $userName = trim("{$user->first_name} {$user->last_name}");
            $message = "Account for {$userName} has been disabled";

            if (Schema::hasColumn('users', 'disabled_until') && $user->disabled_until) {
                if ($duration === 'indefinite') {
                    $message .= ' indefinitely.';
                } else {
                    $message .= ' until '.(new \Carbon\Carbon($user->disabled_until))->format('M d, Y h:i A').'.';
                }
            } else {
                // If the column is missing or value isn't set, append a generic message
                $message .= ' (no re-enable time recorded. Ensure migrations have been run.)';
            }

            $disabledAt = null;
            if (Schema::hasColumn('users', 'disabled_until') && $user->disabled_until) {
                $carbonUntil = new \Carbon\Carbon($user->disabled_until);
                // Return a special string for sentinel indefinite values
                $disabledAt = $carbonUntil->year >= 9999 ? 'indefinite' : $carbonUntil->toISOString();
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'disabled_until' => $disabledAt,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Disable user failed: '.$e->getMessage(), ['exception' => $e]);
            // If disabled_until column is missing, provide actionable advice
            if (! Schema::hasColumn('users', 'disabled_until')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The disabled_until column is missing in the users table. Please run the latest migrations.',
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable user. Please try again.',
            ], 500);
        }
    }

    public function enable(Request $request, User $user)
    {
        Gate::authorize('admin');

        try {
            $user->is_active = true;
            if (Schema::hasColumn('users', 'disabled_until')) {
                $user->disabled_until = null;
            }
            $user->save();

            return response()->json([
                'success' => true,
                'message' => "Account for {$user->first_name} {$user->last_name} has been re-enabled.",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Enable user failed: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable user. Please try again.',
            ], 500);
        }
    }
}
