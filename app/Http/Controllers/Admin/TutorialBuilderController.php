<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use App\Models\TutorialStep;
use App\Models\TutorialDataCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TutorialBuilderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Tutorial::class, 'tutorial');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tutorials = Tutorial::with(['creator', 'steps'])
            ->orderBy('role')
            ->orderBy('priority', 'desc')
            ->get();

        return view('admin.tutorials.index', compact('tutorials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = ['admin', 'dean', 'vpaa', 'chairperson', 'instructor'];
        $positions = ['top', 'bottom', 'left', 'right'];
        
        return view('admin.tutorials.create', compact('roles', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,dean,vpaa,chairperson,instructor',
            'page_identifier' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            
            // Steps (optional for initial creation via modal)
            'steps' => 'nullable|array|min:1',
            'steps.*.title' => 'required_with:steps|string|max:255',
            'steps.*.content' => 'required_with:steps|string',
            'steps.*.target_selector' => 'required_with:steps|string',
            'steps.*.position' => 'required_with:steps|in:top,bottom,left,right',
            'steps.*.is_optional' => 'boolean',
            'steps.*.requires_data' => 'boolean',
            
            // Data check (optional)
            'has_data_check' => 'boolean',
            'data_check.selector' => 'nullable|string',
            'data_check.empty_selectors' => 'nullable|array',
            'data_check.entity_name' => 'nullable|string|max:255',
            'data_check.add_button_selector' => 'nullable|string',
            'data_check.no_add_button' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Create tutorial
            $tutorial = Tutorial::create([
                'role' => $validated['role'],
                'page_identifier' => $validated['page_identifier'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'priority' => $validated['priority'] ?? 0,
                'created_by' => Auth::id(),
            ]);

            // Create steps if provided
            if (!empty($validated['steps'])) {
                foreach ($validated['steps'] as $index => $stepData) {
                    TutorialStep::create([
                        'tutorial_id' => $tutorial->id,
                        'step_order' => $index,
                        'title' => $stepData['title'],
                        'content' => $stepData['content'],
                        'target_selector' => $stepData['target_selector'],
                        'position' => $stepData['position'],
                        'is_optional' => $stepData['is_optional'] ?? false,
                        'requires_data' => $stepData['requires_data'] ?? false,
                    ]);
                }
            }

            // Create data check if provided
            if ($request->has_data_check && !empty($validated['data_check'])) {
                TutorialDataCheck::create([
                    'tutorial_id' => $tutorial->id,
                    'selector' => $validated['data_check']['selector'] ?? null,
                    'empty_selectors' => $validated['data_check']['empty_selectors'] ?? [],
                    'entity_name' => $validated['data_check']['entity_name'] ?? 'records',
                    'add_button_selector' => $validated['data_check']['add_button_selector'] ?? null,
                    'no_add_button' => $validated['data_check']['no_add_button'] ?? false,
                ]);
            }

            DB::commit();

            $message = empty($validated['steps']) 
                ? 'Tutorial created successfully! Click "Edit" to add tutorial steps.' 
                : 'Tutorial created successfully!';

            return redirect()
                ->route('admin.tutorials.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create tutorial: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tutorial $tutorial)
    {
        $tutorial->load(['steps', 'dataCheck', 'creator']);
        return view('admin.tutorials.show', compact('tutorial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tutorial $tutorial)
    {
        $tutorial->load(['steps', 'dataCheck']);
        $roles = ['admin', 'dean', 'vpaa', 'chairperson', 'instructor'];
        $positions = ['top', 'bottom', 'left', 'right'];
        
        return view('admin.tutorials.edit', compact('tutorial', 'roles', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tutorial $tutorial)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,dean,vpaa,chairperson,instructor',
            'page_identifier' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            
            // Steps (optional for metadata-only updates)
            'steps' => 'nullable|array|min:1',
            'steps.*.id' => 'nullable|exists:tutorial_steps,id',
            'steps.*.title' => 'required_with:steps|string|max:255',
            'steps.*.content' => 'required_with:steps|string',
            'steps.*.target_selector' => 'required_with:steps|string',
            'steps.*.position' => 'required_with:steps|in:top,bottom,left,right',
            'steps.*.is_optional' => 'boolean',
            'steps.*.requires_data' => 'boolean',
            
            // Data check
            'has_data_check' => 'boolean',
            'data_check.selector' => 'nullable|string',
            'data_check.empty_selectors' => 'nullable|array',
            'data_check.entity_name' => 'nullable|string|max:255',
            'data_check.add_button_selector' => 'nullable|string',
            'data_check.no_add_button' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update tutorial
            $tutorial->update([
                'role' => $validated['role'],
                'page_identifier' => $validated['page_identifier'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? false,
                'priority' => $validated['priority'] ?? 0,
            ]);

            // Only update steps if provided (full edit)
            if (!empty($validated['steps'])) {
                // Delete old steps
                $tutorial->steps()->delete();

                // Create new steps
                foreach ($validated['steps'] as $index => $stepData) {
                    TutorialStep::create([
                        'tutorial_id' => $tutorial->id,
                        'step_order' => $index,
                        'title' => $stepData['title'],
                        'content' => $stepData['content'],
                        'target_selector' => $stepData['target_selector'],
                        'position' => $stepData['position'],
                        'is_optional' => $stepData['is_optional'] ?? false,
                        'requires_data' => $stepData['requires_data'] ?? false,
                    ]);
                }
            }

            // Update or create data check (only if steps are provided)
            if (!empty($validated['steps'])) {
                $tutorial->dataCheck()?->delete();
                if ($request->has_data_check && !empty($validated['data_check'])) {
                    TutorialDataCheck::create([
                        'tutorial_id' => $tutorial->id,
                        'selector' => $validated['data_check']['selector'] ?? null,
                        'empty_selectors' => $validated['data_check']['empty_selectors'] ?? [],
                        'entity_name' => $validated['data_check']['entity_name'] ?? 'records',
                        'add_button_selector' => $validated['data_check']['add_button_selector'] ?? null,
                        'no_add_button' => $validated['data_check']['no_add_button'] ?? false,
                    ]);
                }
            }

            DB::commit();

            $message = empty($validated['steps']) 
                ? 'Tutorial metadata updated successfully!' 
                : 'Tutorial updated successfully!';

            return redirect()
                ->route('admin.tutorials.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update tutorial: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tutorial $tutorial)
    {
        try {
            $tutorial->delete();
            
            return redirect()
                ->route('admin.tutorials.index')
                ->with('success', 'Tutorial deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete tutorial: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a tutorial
     */
    public function duplicate(Tutorial $tutorial)
    {
        DB::beginTransaction();
        try {
            $newTutorial = $tutorial->replicate();
            $newTutorial->title = $tutorial->title . ' (Copy)';
            $newTutorial->created_by = Auth::id();
            $newTutorial->save();

            // Duplicate steps
            foreach ($tutorial->steps as $step) {
                $newStep = $step->replicate();
                $newStep->tutorial_id = $newTutorial->id;
                $newStep->save();
            }

            // Duplicate data check
            if ($tutorial->dataCheck) {
                $newDataCheck = $tutorial->dataCheck->replicate();
                $newDataCheck->tutorial_id = $newTutorial->id;
                $newDataCheck->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.tutorials.edit', $newTutorial)
                ->with('success', 'Tutorial duplicated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to duplicate tutorial: ' . $e->getMessage());
        }
    }

    /**
     * Toggle tutorial active status
     */
    public function toggleActive(Tutorial $tutorial)
    {
        $tutorial->update(['is_active' => !$tutorial->is_active]);
        
        $status = $tutorial->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Tutorial {$status} successfully!");
    }
}
