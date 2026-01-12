<?php

namespace App\Http\Controllers\Chairperson;

use App\Http\Controllers\Controller;
use App\Models\CourseOutcomeTemplate;
use App\Models\CourseOutcomeTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseOutcomeTemplateController extends Controller
{
    /**
     * Display a listing of CO templates
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get templates based on role
        if ($user->role === 1) { // Chairperson
            $templates = CourseOutcomeTemplate::with(['items', 'creator', 'course'])
                ->where(function ($query) use ($user) {
                    $query->where('course_id', $user->course_id)
                          ->orWhere('is_universal', true);
                })
                ->active()
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->role === 4) { // GE Coordinator
            $templates = CourseOutcomeTemplate::with(['items', 'creator'])
                ->where('is_universal', true)
                ->active()
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        return view('chairperson.co-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new CO template
     */
    public function create()
    {
        $user = Auth::user();
        
        if ($user->role !== 1 && $user->role !== 4) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        return view('chairperson.co-templates.create');
    }

    /**
     * Store a newly created CO template
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 1 && $user->role !== 4) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_universal' => 'sometimes|boolean',
            'items' => 'required|array|min:1',
            'items.*.co_code' => 'required|string|max:50',
            'items.*.description' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // For chairperson, template is for their course only unless universal
            $isUniversal = ($user->role === 4) || ($request->has('is_universal') && $request->is_universal);
            
            $template = CourseOutcomeTemplate::create([
                'template_name' => $validated['template_name'],
                'description' => $validated['description'] ?? null,
                'created_by' => $user->id,
                'course_id' => $isUniversal ? null : $user->course_id,
                'is_universal' => $isUniversal,
                'is_active' => true,
            ]);

            // Create template items
            foreach ($validated['items'] as $index => $item) {
                CourseOutcomeTemplateItem::create([
                    'template_id' => $template->id,
                    'co_code' => $item['co_code'],
                    'description' => $item['description'],
                    'order' => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('chairperson.co-templates.index')
                ->with('success', 'Course Outcome template created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create template: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified CO template
     */
    public function show(CourseOutcomeTemplate $coTemplate)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && !$coTemplate->is_universal && $coTemplate->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        if ($user->role === 4 && !$coTemplate->is_universal) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        $coTemplate->load(['items', 'creator', 'course']);

        return view('chairperson.co-templates.show', compact('coTemplate'));
    }

    /**
     * Show the form for editing the specified CO template
     */
    public function edit(CourseOutcomeTemplate $coTemplate)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && !$coTemplate->is_universal && $coTemplate->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        if ($user->role === 4 && !$coTemplate->is_universal) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        $coTemplate->load('items');

        return view('chairperson.co-templates.edit', compact('coTemplate'));
    }

    /**
     * Update the specified CO template
     */
    public function update(Request $request, CourseOutcomeTemplate $coTemplate)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && !$coTemplate->is_universal && $coTemplate->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        if ($user->role === 4 && !$coTemplate->is_universal) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.co_code' => 'required|string|max:50',
            'items.*.description' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $coTemplate->update([
                'template_name' => $validated['template_name'],
                'description' => $validated['description'] ?? null,
            ]);

            // Delete old items and create new ones
            $coTemplate->items()->delete();
            
            foreach ($validated['items'] as $index => $item) {
                CourseOutcomeTemplateItem::create([
                    'template_id' => $coTemplate->id,
                    'co_code' => $item['co_code'],
                    'description' => $item['description'],
                    'order' => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('chairperson.co-templates.index')
                ->with('success', 'Course Outcome template updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified CO template (soft delete)
     */
    public function destroy(CourseOutcomeTemplate $coTemplate)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && !$coTemplate->is_universal && $coTemplate->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        if ($user->role === 4 && !$coTemplate->is_universal) {
            return redirect()->back()->with('error', 'Unauthorized access to this template.');
        }

        // Check if template is in use
        if ($coTemplate->batchDrafts()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete template: it is being used by one or more batch drafts.');
        }

        $coTemplate->update(['is_deleted' => true]);

        return redirect()
            ->route('chairperson.co-templates.index')
            ->with('success', 'Course Outcome template deleted successfully.');
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(CourseOutcomeTemplate $coTemplate)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && !$coTemplate->is_universal && $coTemplate->course_id !== $user->course_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($user->role === 4 && !$coTemplate->is_universal) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $coTemplate->update(['is_active' => !$coTemplate->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $coTemplate->is_active,
            'message' => 'Template status updated successfully.',
        ]);
    }
}
