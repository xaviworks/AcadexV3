<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use App\Notifications\AdminAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    /**
     * Role mapping for display and querying.
     */
    protected array $roleLabels = [
        0 => 'Instructor',
        1 => 'Chairperson',
        2 => 'Dean',
        3 => 'Admin',
        4 => 'GE Coordinator',
        5 => 'VPAA',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the announcement creation form.
     */
    public function create()
    {
        Gate::authorize('admin');

        // Department model stores description/code; no department_name column
        $departments = Department::where('is_deleted', false)
            ->orderBy('department_description')
            ->get();

        // Course model stores course_code/description; no course_name column
        $programs = Course::orderBy('course_description')->get();

        $roles = collect($this->roleLabels)->map(function ($label, $value) {
            return ['value' => $value, 'label' => $label];
        })->values();

        $users = User::where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $this->roleLabels[$user->role] ?? 'Unknown',
                ];
            });

        return view('admin.announcements.create', compact('departments', 'programs', 'roles', 'users'));
    }

    /**
     * Send the announcement to selected recipients.
     */
    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:65535', // HTML content from rich text editor
            'priority' => 'required|in:low,normal,high,urgent',
            'target_type' => 'required|in:specific_user,department,program,role',
            'target_id' => 'required',
        ]);

        // Validate plain text length (strip HTML tags)
        $plainTextLength = strlen(strip_tags($validated['message']));
        if ($plainTextLength > 2000) {
            return back()->withErrors(['message' => 'The message field must not be greater than 2000 characters.'])->withInput();
        }

        $sender = Auth::user();
        $recipients = collect();
        $targetName = '';

        switch ($validated['target_type']) {
            case 'specific_user':
                $targetUser = User::where('id', $validated['target_id'])
                    ->where('is_active', true)
                    ->first();
                
                if (!$targetUser) {
                    return back()->withErrors(['target_id' => 'Selected user not found or inactive.'])->withInput();
                }
                
                $recipients->push($targetUser);
                $targetName = $targetUser->full_name;
                break;

            case 'department':
                $department = Department::find($validated['target_id']);
                
                if (!$department) {
                    return back()->withErrors(['target_id' => 'Selected department not found.'])->withInput();
                }
                
                $recipients = User::where('department_id', $department->id)
                    ->where('is_active', true)
                    ->get();
                $targetName = $department->department_description;
                break;

            case 'program':
                $program = Course::find($validated['target_id']);
                
                if (!$program) {
                    return back()->withErrors(['target_id' => 'Selected program not found.'])->withInput();
                }
                
                $recipients = User::where('course_id', $program->id)
                    ->where('is_active', true)
                    ->get();
                $targetName = $program->course_description;
                break;

            case 'role':
                $roleValue = (int) $validated['target_id'];
                
                if (!isset($this->roleLabels[$roleValue])) {
                    return back()->withErrors(['target_id' => 'Invalid role selected.'])->withInput();
                }
                
                $recipients = User::where('role', $roleValue)
                    ->where('is_active', true)
                    ->get();
                $targetName = $this->roleLabels[$roleValue];
                break;
        }

        if ($recipients->isEmpty()) {
            return back()->withErrors(['target_id' => 'No active users found for the selected target.'])->withInput();
        }

        // Don't send to the sender themselves
        $recipients = $recipients->filter(fn($user) => $user->id !== $sender->id);

        if ($recipients->isEmpty()) {
            return back()->withErrors(['target_id' => 'No recipients available (you cannot send announcements only to yourself).'])->withInput();
        }

        try {
            $metadata = [
                'recipient_count' => $recipients->count(),
                'sent_at' => now()->toIso8601String(),
            ];

            Notification::send(
                $recipients,
                new AdminAnnouncement(
                    $sender,
                    $validated['title'],
                    $validated['message'],
                    $validated['target_type'],
                    $targetName,
                    $validated['priority'],
                    null, // action_url removed
                    null, // action_text removed
                    $metadata
                )
            );

            Log::info('Admin announcement sent', [
                'admin_id' => $sender->id,
                'title' => $validated['title'],
                'target_type' => $validated['target_type'],
                'target_name' => $targetName,
                'recipient_count' => $recipients->count(),
            ]);

            return redirect()
                ->route('admin.announcements.create')
                ->with('success', "Announcement sent successfully to {$recipients->count()} recipient(s).");

        } catch (\Exception $e) {
            Log::error('Failed to send admin announcement', [
                'error' => $e->getMessage(),
                'admin_id' => $sender->id,
            ]);

            return back()
                ->withErrors(['error' => 'Failed to send announcement. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Get users for a specific target type (AJAX endpoint).
     */
    public function getTargetUsers(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'target_type' => 'required|in:specific_user,department,program,role',
            'target_id' => 'required',
        ]);

        $count = 0;
        $preview = [];

        switch ($validated['target_type']) {
            case 'specific_user':
                $user = User::where('id', $validated['target_id'])
                    ->where('is_active', true)
                    ->first();
                
                if ($user) {
                    $count = 1;
                    $preview = [['name' => $user->full_name, 'email' => $user->email]];
                }
                break;

            case 'department':
                $users = User::where('department_id', $validated['target_id'])
                    ->where('is_active', true)
                    ->where('id', '!=', Auth::id())
                    ->get();
                
                $count = $users->count();
                $preview = $users->take(5)->map(fn($u) => ['name' => $u->full_name, 'email' => $u->email])->toArray();
                break;

            case 'program':
                $users = User::where('course_id', $validated['target_id'])
                    ->where('is_active', true)
                    ->where('id', '!=', Auth::id())
                    ->get();
                
                $count = $users->count();
                $preview = $users->take(5)->map(fn($u) => ['name' => $u->full_name, 'email' => $u->email])->toArray();
                break;

            case 'role':
                $users = User::where('role', (int) $validated['target_id'])
                    ->where('is_active', true)
                    ->where('id', '!=', Auth::id())
                    ->get();
                
                $count = $users->count();
                $preview = $users->take(5)->map(fn($u) => ['name' => $u->full_name, 'email' => $u->email])->toArray();
                break;
        }

        return response()->json([
            'count' => $count,
            'preview' => $preview,
        ]);
    }
}
