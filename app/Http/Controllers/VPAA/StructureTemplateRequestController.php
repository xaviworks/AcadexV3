<?php

namespace App\Http\Controllers\VPAA;

use App\Http\Controllers\Controller;
use App\Http\Controllers\VPAA\Concerns\ManagesGradingConfiguration;
use App\Models\StructureTemplate;
use App\Support\Grades\FormulaStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StructureTemplateRequestController extends Controller
{
    use ManagesGradingConfiguration;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        Gate::authorize('manage-grading-configuration');

        $status = $request->query('status', 'all');

        $query = \App\Models\StructureTemplateRequest::with(['chairperson', 'reviewer']);

        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'approved') {
            $query->approved();
        } elseif ($status === 'rejected') {
            $query->rejected();
        }

        $requests = $query->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 WHEN 'rejected' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = \App\Models\StructureTemplateRequest::pending()->count();

        return view('vpaa.grading-configuration.structure-template-requests', compact('requests', 'status', 'pendingCount'));
    }

    public function show(\App\Models\StructureTemplateRequest $request)
    {
        Gate::authorize('manage-grading-configuration');

        $request->load(['chairperson', 'reviewer']);
        $structureCatalog = \App\Support\Grades\FormulaStructure::getAllStructureDefinitions();

        return view('vpaa.grading-configuration.structure-template-request-show', compact('request', 'structureCatalog'));
    }

    public function approve(Request $request, \App\Models\StructureTemplateRequest $templateRequest)
    {
        Gate::authorize('manage-grading-configuration');

        if ($templateRequest->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be approved.',
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Only pending requests can be approved.']);
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $structureConfig = $templateRequest->structure_config;

            if (! is_array($structureConfig)) {
                throw new \InvalidArgumentException('The submitted structure is invalid.');
            }

            if ($this->isNewTemplateFormat($structureConfig)) {
                $structureConfig = $this->convertNewFormatToOld($structureConfig);
            }

            if (empty(data_get($structureConfig, 'children', []))) {
                throw new \InvalidArgumentException('The request does not contain any grading components.');
            }

            // Generate a unique template key
            $baseKey = Str::slug($templateRequest->label);
            if ($baseKey === '') {
                $baseKey = 'template-'.Str::random(6);
            }

            $templateKey = $baseKey;
            $counter = 1;

            while (StructureTemplate::where('template_key', $templateKey)->exists()) {
                $templateKey = $baseKey.'-'.$counter;
                $counter++;
            }

            $structureConfig = $templateRequest->structure_config ?? [];

            if ($this->isNewTemplateFormat(is_array($structureConfig) ? $structureConfig : [])) {
                $structureConfig = $this->convertNewFormatToOld($structureConfig);
            }

            try {
                $structureConfig = FormulaStructure::normalize($structureConfig);
            } catch (\Throwable $exception) {
                throw ValidationException::withMessages([
                    'structure_config' => 'The submitted template structure could not be normalized: '.$exception->getMessage(),
                ]);
            }

            // Create the structure template
            StructureTemplate::create([
                'template_key' => $templateKey,
                'label' => $templateRequest->label,
                'description' => $templateRequest->description,
                'structure_config' => $structureConfig,
                'is_system_default' => false,
                'is_deleted' => false,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Update the request status
            $templateRequest->status = 'approved';
            $templateRequest->admin_notes = $request->input('admin_notes');
            $templateRequest->reviewed_by = Auth::id();
            $templateRequest->reviewed_at = now();
            $templateRequest->save();

            DB::commit();

            $message = "Structure template '{$templateRequest->label}' approved and added to the catalog.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()
                ->route('vpaa.gradingConfiguration.templateRequests.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve template request: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to approve template request: '.$e->getMessage()]);
        }
    }

    public function reject(Request $request, \App\Models\StructureTemplateRequest $templateRequest)
    {
        Gate::authorize('manage-grading-configuration');

        if ($templateRequest->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be rejected.',
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Only pending requests can be rejected.']);
        }

        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $templateRequest->status = 'rejected';
        $templateRequest->admin_notes = $request->input('admin_notes');
        $templateRequest->reviewed_by = Auth::id();
        $templateRequest->reviewed_at = now();
        $templateRequest->save();

        $message = "Structure template request from {$templateRequest->chairperson->first_name} {$templateRequest->chairperson->last_name} has been rejected.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('vpaa.gradingConfiguration.templateRequests.index')
            ->with('success', $message);
    }
}
