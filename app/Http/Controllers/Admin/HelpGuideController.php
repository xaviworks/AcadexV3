<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HelpGuideController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of help guides (Admin).
     */
    public function index()
    {
        Gate::authorize('admin');

        $guides = HelpGuide::with(['creator', 'updater'])
            ->ordered()
            ->get();

        $availableRoles = HelpGuide::availableRoles();

        return view('admin.help-guides.index', compact('guides', 'availableRoles'));
    }

    /**
     * Show the form for creating a new help guide.
     */
    public function create()
    {
        Gate::authorize('admin');

        $availableRoles = HelpGuide::availableRoles();

        return view('admin.help-guides.create', compact('availableRoles'));
    }

    /**
     * Store a newly created help guide.
     */
    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visible_roles' => 'required|array|min:1',
            'visible_roles.*' => ['integer', Rule::in(array_keys(HelpGuide::availableRoles()))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'attachment' => 'nullable|file|max:10240|mimes:pdf',
        ], [
            'visible_roles.required' => 'Please select at least one role that can view this guide.',
            'visible_roles.min' => 'Please select at least one role that can view this guide.',
            'attachment.max' => 'The attachment must not exceed 10MB.',
            'attachment.mimes' => 'Only PDF files are allowed.',
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('help-guides', 'public');
        }

        // Cast visible_roles to integers
        $visibleRoles = array_map('intval', $validated['visible_roles']);

        HelpGuide::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'visible_roles' => $visibleRoles,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.help-guides.index')
            ->with('success', 'Help guide created successfully.');
    }

    /**
     * Show the form for editing a help guide.
     */
    public function edit(HelpGuide $helpGuide)
    {
        Gate::authorize('admin');

        $availableRoles = HelpGuide::availableRoles();

        return view('admin.help-guides.edit', compact('helpGuide', 'availableRoles'));
    }

    /**
     * Update the specified help guide.
     */
    public function update(Request $request, HelpGuide $helpGuide)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visible_roles' => 'required|array|min:1',
            'visible_roles.*' => ['integer', Rule::in(array_keys(HelpGuide::availableRoles()))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'attachment' => 'nullable|file|max:10240|mimes:pdf',
            'remove_attachment' => 'boolean',
        ], [
            'visible_roles.required' => 'Please select at least one role that can view this guide.',
            'visible_roles.min' => 'Please select at least one role that can view this guide.',
            'attachment.max' => 'The attachment must not exceed 10MB.',
            'attachment.mimes' => 'Only PDF files are allowed.',
        ]);

        // Handle attachment removal
        if ($request->boolean('remove_attachment')) {
            $helpGuide->deleteAttachment();
        }

        // Handle new attachment upload
        $attachmentPath = $helpGuide->attachment_path;
        $attachmentName = $helpGuide->attachment_name;

        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            $helpGuide->deleteAttachment();
            
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('help-guides', 'public');
        }

        // Cast visible_roles to integers
        $visibleRoles = array_map('intval', $validated['visible_roles']);

        $helpGuide->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'visible_roles' => $visibleRoles,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.help-guides.index')
            ->with('success', 'Help guide updated successfully.');
    }

    /**
     * Remove the specified help guide.
     */
    public function destroy(HelpGuide $helpGuide)
    {
        Gate::authorize('admin');

        // Delete attachment if exists
        $helpGuide->deleteAttachment();
        
        $helpGuide->delete();

        return redirect()
            ->route('admin.help-guides.index')
            ->with('success', 'Help guide deleted successfully.');
    }

    /**
     * Update sort order (AJAX).
     */
    public function updateOrder(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'guides' => 'required|array',
            'guides.*.id' => 'required|integer|exists:help_guides,id',
            'guides.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->guides as $guideData) {
            HelpGuide::where('id', $guideData['id'])
                ->update(['sort_order' => $guideData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle guide active status (AJAX).
     */
    public function toggleActive(HelpGuide $helpGuide)
    {
        Gate::authorize('admin');

        $helpGuide->update([
            'is_active' => !$helpGuide->is_active,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $helpGuide->is_active,
        ]);
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment(HelpGuide $helpGuide)
    {
        // Allow any authenticated user to download if they can view the guide
        if (!$helpGuide->hasAttachment()) {
            abort(404, 'Attachment not found.');
        }

        $user = Auth::user();
        
        // Admin can always download
        if ($user->role !== 3 && !$helpGuide->isVisibleToRole($user->role)) {
            abort(403, 'You do not have permission to access this attachment.');
        }

        if (!Storage::disk('public')->exists($helpGuide->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->download(
            $helpGuide->attachment_path,
            $helpGuide->attachment_name
        );
    }
}
