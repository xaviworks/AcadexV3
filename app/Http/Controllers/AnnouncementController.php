<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get active announcements for current user (AJAX)
     */
    public function getActive()
    {
        $user = Auth::user();
        
        $announcements = Announcement::current()
            ->forRole($user->role)
            ->orderByPriority('desc')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn($announcement) => $announcement->shouldShowToUser($user))
            ->values();

        return response()->json($announcements);
    }

    /**
     * Mark announcement as viewed
     */
    public function markAsViewed(Announcement $announcement)
    {
        $announcement->markAsViewedBy(Auth::user());
        
        return response()->json(['success' => true]);
    }

    /**
     * Admin: List all announcements
     */
    public function index()
    {
        Gate::authorize('admin');

        $announcements = Announcement::with('creator')
            ->orderByPriority('desc')
            ->orderByDesc('created_at')
            ->paginate(20);

        $templates = AnnouncementTemplate::active()
            ->ordered()
            ->get();

        return view('admin.announcements.index', compact('announcements', 'templates'));
    }

    /**
     * Admin: Create announcement form
     */
    public function create()
    {
        Gate::authorize('admin');

        return view('admin.announcements.create');
    }

    /**
     * Admin: Store new announcement
     */
    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'priority' => 'required|in:low,normal,high,urgent',
            'icon' => 'nullable|string|max:50',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'integer|in:0,1,2,4,5',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'show_once' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        
        // If target_roles is not in request or is empty, set to null (meaning "All Users")
        if (!isset($validated['target_roles']) || empty($validated['target_roles'])) {
            $validated['target_roles'] = null;
        } else {
            // Ensure target_roles are integers, not strings
            $validated['target_roles'] = array_map('intval', $validated['target_roles']);
        }

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Admin: Edit announcement form
     */
    public function edit(Announcement $announcement)
    {
        Gate::authorize('admin');

        return view('admin.announcements.edit', compact('announcement'));
    }

    /**
     * Admin: Update announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'priority' => 'required|in:low,normal,high,urgent',
            'icon' => 'nullable|string|max:50',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'integer|in:0,1,2,4,5',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'show_once' => 'boolean',
        ]);
        
        // If target_roles is not in request or is empty, set to null (meaning "All Users")
        if (!isset($validated['target_roles']) || empty($validated['target_roles'])) {
            $validated['target_roles'] = null;
        } else {
            // Ensure target_roles are integers, not strings
            $validated['target_roles'] = array_map('intval', $validated['target_roles']);
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Admin: Delete announcement
     */
    public function destroy(Announcement $announcement)
    {
        Gate::authorize('admin');

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    /**
     * Admin: Toggle active status
     */
    public function toggleActive(Announcement $announcement)
    {
        Gate::authorize('admin');

        $announcement->update(['is_active' => !$announcement->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $announcement->is_active,
        ]);
    }
}
