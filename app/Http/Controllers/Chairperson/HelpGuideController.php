<?php

namespace App\Http\Controllers\Chairperson;

use App\Http\Controllers\Controller;
use App\Models\HelpGuide;
use App\Models\HelpGuideAttachment;
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
     * Display help guides management page for the chairperson.
     */
    public function index()
    {
        Gate::authorize('chairperson');

        $guides = HelpGuide::with(['creator', 'updater', 'attachments'])
            ->where('created_by', Auth::id())
            ->ordered()
            ->get();

        $availableRoles = array_filter(
            HelpGuide::availableRoles(),
            fn($label, $roleId) => $roleId !== HelpGuide::ROLE_ADMIN,
            ARRAY_FILTER_USE_BOTH
        );

        return view('chairperson.help-guides.index', compact('guides', 'availableRoles'));
    }

    /**
     * Store a new help guide created by the chairperson.
     */
    public function store(Request $request)
    {
        Gate::authorize('chairperson');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visible_roles' => 'required|array|min:1',
            'visible_roles.*' => ['integer', Rule::in(array_keys(HelpGuide::availableRoles()))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:pdf',
        ], [
            'visible_roles.required' => 'Please select at least one role that can view this guide.',
            'visible_roles.min' => 'Please select at least one role that can view this guide.',
            'attachments.*.max' => 'Each attachment must not exceed 10MB.',
            'attachments.*.mimes' => 'Only PDF files are allowed.',
            'attachments.max' => 'Maximum 10 attachments allowed.',
        ]);

        $visibleRoles = array_map('intval', $validated['visible_roles']);

        $helpGuide = HelpGuide::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'visible_roles' => $visibleRoles,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->has('is_active'),
            'created_by' => Auth::id(),
        ]);

        if ($request->hasFile('attachments')) {
            $sortOrder = 0;
            foreach ($request->file('attachments') as $file) {
                $filePath = $file->store('help-guides', 'public');
                $helpGuide->attachments()->create([
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?? 'application/pdf',
                    'file_size' => $file->getSize(),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        return redirect()
            ->route('chairperson.help-guides.index')
            ->with('success', 'Help guide created successfully.');
    }

    /**
     * Update an existing help guide owned by the chairperson.
     */
    public function update(Request $request, HelpGuide $helpGuide)
    {
        Gate::authorize('chairperson');
        $this->authorizeOwnership($helpGuide);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visible_roles' => 'required|array|min:1',
            'visible_roles.*' => ['integer', Rule::in(array_keys(HelpGuide::availableRoles()))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:pdf',
            'remove_attachment' => 'boolean',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'integer|exists:help_guide_attachments,id',
        ], [
            'visible_roles.required' => 'Please select at least one role that can view this guide.',
            'visible_roles.min' => 'Please select at least one role that can view this guide.',
            'attachments.*.max' => 'Each attachment must not exceed 10MB.',
            'attachments.*.mimes' => 'Only PDF files are allowed.',
        ]);

        if ($request->boolean('remove_attachment')) {
            $helpGuide->deleteAttachment();
        }

        if ($request->has('delete_attachments')) {
            foreach ($request->input('delete_attachments') as $attachmentId) {
                $attachment = $helpGuide->attachments()->find($attachmentId);
                if ($attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                    $attachment->delete();
                }
            }
        }

        $visibleRoles = array_map('intval', $validated['visible_roles']);

        $helpGuide->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'visible_roles' => $visibleRoles,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->has('is_active'),
            'updated_by' => Auth::id(),
        ]);

        if ($request->hasFile('attachments')) {
            $maxSortOrder = $helpGuide->attachments()->max('sort_order') ?? -1;
            $sortOrder = $maxSortOrder + 1;
            foreach ($request->file('attachments') as $file) {
                $filePath = $file->store('help-guides', 'public');
                $helpGuide->attachments()->create([
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?? 'application/pdf',
                    'file_size' => $file->getSize(),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        return redirect()
            ->route('chairperson.help-guides.index')
            ->with('success', 'Help guide updated successfully.');
    }

    /**
     * Delete a help guide owned by the chairperson.
     */
    public function destroy(HelpGuide $helpGuide)
    {
        Gate::authorize('chairperson');
        $this->authorizeOwnership($helpGuide);

        $helpGuide->deleteAttachment();
        foreach ($helpGuide->attachments as $attachment) {
            $attachment->deleteFile();
        }
        $helpGuide->delete();

        return redirect()
            ->route('chairperson.help-guides.index')
            ->with('success', 'Help guide deleted successfully.');
    }

    /**
     * Delete a single attachment via AJAX.
     */
    public function deleteAttachment(HelpGuideAttachment $attachment)
    {
        Gate::authorize('chairperson');

        $helpGuide = $attachment->helpGuide;
        $this->authorizeOwnership($helpGuide);

        $attachment->deleteFile();
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully.',
        ]);
    }

    /**
     * Toggle the active status of a help guide.
     */
    public function toggleActive(HelpGuide $helpGuide)
    {
        Gate::authorize('chairperson');
        $this->authorizeOwnership($helpGuide);

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
     * Ensure the chairperson can only manage guides they created.
     */
    private function authorizeOwnership(HelpGuide $helpGuide): void
    {
        if ($helpGuide->created_by !== Auth::id()) {
            abort(403, 'You can only manage help guides you created.');
        }
    }
}
