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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StructureTemplateController extends Controller
{
    use ManagesGradingConfiguration;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-grading-configuration');

        $validated = $request->validate([
            'password' => ['required', 'string'],
            'template_label' => ['required', 'string', 'max:100'],
            'template_key' => ['required', 'string', 'max:50', Rule::unique('structure_templates', 'template_key')->where('is_deleted', false)],
            'template_description' => ['nullable', 'string', 'max:500'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.activity_type' => ['required', 'string', 'max:100'],
            'components.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'components.*.label' => ['required', 'string', 'max:100'],
            'components.*.max_items' => ['nullable', 'integer', 'min:1', 'max:5'],
            'components.*.is_main' => ['nullable', 'boolean'],
            'components.*.parent_id' => ['nullable', 'integer'],
        ]);

        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput()
                ->with('reopen_structure_template_modal', true)
                ->with('structure_template_error', true)
                ->with('structure_template_mode', 'create');
        }

        try {
            $structureConfig = $this->buildStructureTemplateConfig($validated['components']);
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput()
                ->with('reopen_structure_template_modal', true)
                ->with('structure_template_error', true)
                ->with('structure_template_mode', 'create');
        }

        DB::beginTransaction();

        try {
            $template = new StructureTemplate;
            $template->template_key = $validated['template_key'];
            $template->label = $validated['template_label'];
            $template->description = $validated['template_description'] !== '' ? $validated['template_description'] : null;
            $template->structure_config = $structureConfig;
            $template->is_system_default = false;
            $template->is_deleted = false;
            $template->created_by = Auth::id();
            $template->updated_by = Auth::id();
            $template->save();

            DB::commit();

            return redirect()
                ->route('vpaa.gradingConfiguration.index', array_merge($this->formulaQueryParams(), ['view' => 'formulas']))
                ->with('success', "Structure template '{$template->label}' created successfully.");
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to create structure template: '.$exception->getMessage()])
                ->withInput()
                ->with('reopen_structure_template_modal', true)
                ->with('structure_template_error', true)
                ->with('structure_template_mode', 'create');
        }
    }

    public function update(Request $request, StructureTemplate $template)
    {
        Gate::authorize('manage-grading-configuration');

        if ($template->is_deleted) {
            abort(404);
        }

        if ($template->is_system_default) {
            abort(403, 'System templates cannot be modified.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string'],
            'template_label' => ['required', 'string', 'max:100'],
            'template_key' => [
                'required',
                'string',
                'max:50',
                Rule::unique('structure_templates', 'template_key')->ignore($template->id)->where('is_deleted', false),
            ],
            'template_description' => ['nullable', 'string', 'max:500'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.activity_type' => ['required', 'string', 'max:100'],
            'components.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'components.*.label' => ['required', 'string', 'max:100'],
            'components.*.max_items' => ['nullable', 'integer', 'min:1', 'max:5'],
            'components.*.is_main' => ['nullable', 'boolean'],
            'components.*.parent_id' => ['nullable', 'integer'],
        ]);

        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput()
                ->with('reopen_structure_template_modal', true)
                ->with('structure_template_error', true)
                ->with('structure_template_mode', 'edit')
                ->with('structure_template_edit_id', $template->id);
        }

        try {
            $structureConfig = $this->buildStructureTemplateConfig($validated['components']);
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput()
                ->with('reopen_structure_template_modal', true)
                ->with('structure_template_error', true)
                ->with('structure_template_mode', 'edit')
                ->with('structure_template_edit_id', $template->id);
        }

        $template->template_key = $validated['template_key'];
        $template->label = $validated['template_label'];
        $template->description = $validated['template_description'] !== '' ? $validated['template_description'] : null;
        $template->structure_config = $structureConfig;
        $template->updated_by = Auth::id();
        $template->save();

        return redirect()
            ->route('vpaa.gradingConfiguration.index', array_merge($this->formulaQueryParams(), ['view' => 'formulas']))
            ->with('success', "Structure template '{$template->label}' updated successfully.");
    }

    public function edit(StructureTemplate $template)
    {
        Gate::authorize('manage-grading-configuration');

        if ($template->is_deleted) {
            abort(404);
        }

        if ($template->is_system_default) {
            abort(403, 'System templates cannot be modified.');
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $structureConfig = $template->structure_config ?? [];

        if ($this->isNewTemplateFormat(is_array($structureConfig) ? $structureConfig : [])) {
            $structureConfig = $this->convertNewFormatToOld($structureConfig);
        }

        try {
            $structureConfig = FormulaStructure::normalize($structureConfig);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'structure_config' => 'The stored template structure could not be rendered: '.$exception->getMessage(),
            ]);
        }

        $template->structure_config = $structureConfig;

        return view('vpaa.grading-configuration.structure-template-edit', [
            'template' => $template,
            'semester' => $periodContext['semester'] ?? null,
            'academicPeriods' => $periodContext['academic_periods'] ?? collect(),
            'academicYears' => $periodContext['academic_years'] ?? collect(),
            'selectedAcademicYear' => $periodContext['academic_year'] ?? null,
            'selectedAcademicPeriodId' => $periodContext['academic_period_id'] ?? null,
            'availableSemesters' => $periodContext['available_semesters'] ?? collect(),
        ]);
    }

    public function destroy(Request $request, StructureTemplate $template)
    {
        Gate::authorize('manage-grading-configuration');

        if ($template->is_deleted) {
            abort(404);
        }

        if ($template->is_system_default) {
            abort(403, 'System templates cannot be deleted.');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput()
                ->with('reopen_structure_template_delete_modal', $template->id);
        }

        $template->is_deleted = true;
        $template->updated_by = Auth::id();
        $template->save();

        return redirect()
            ->route('vpaa.gradingConfiguration.index', array_merge($this->formulaQueryParams(), ['view' => 'formulas']))
            ->with('success', "Structure template '{$template->label}' deleted successfully.");
    }
}
