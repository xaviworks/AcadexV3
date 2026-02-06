@extends('layouts.app')

@php
    $request = request();
    $queryParams = $request->query();

    $allowedSections = ['overview', 'formulas'];
    $initialSection = $request->query('view');
    if (! in_array($initialSection, $allowedSections, true)) {
        $initialSection = 'overview';
    }

    $structureTemplateError = session('structure_template_error', false);
    $reopenTemplateModalFlag = session('reopen_structure_template_modal', false);
    $errorMessages = $errors->getMessages();

    $templateErrorMessages = collect($errorMessages)
        ->filter(function ($messages, $field) use ($structureTemplateError) {
            if (str_starts_with($field, 'components')) {
                return true;
            }

            if (in_array($field, ['template_label', 'template_key', 'template_description'], true)) {
                return true;
            }

            if ($structureTemplateError && in_array($field, ['password', 'error'], true)) {
                return true;
            }

            return false;
        })
        ->flatten()
        ->map(fn ($message) => (string) $message)
        ->values()
        ->all();

    $oldTemplateInputs = [
        'label' => old('template_label'),
        'key' => old('template_key'),
        'description' => old('template_description'),
        'components' => old('components', []),
    ];

    $oldGlobalFormulaInputs = old('scope_level') === 'global'
        ? [
            'label' => old('label'),
            'template_key' => old('template_key'),
            'context_type' => old('context_type'),
            'semester' => old('semester'),
            'academic_year' => old('academic_year'),
        ]
        : [
            'label' => null,
            'template_key' => null,
            'context_type' => null,
            'semester' => null,
            'academic_year' => null,
        ];

    $shouldReopenCreateFormulaModal = old('scope_level') === 'global' && $errors->any();
    $globalFormulaPasswordError = $shouldReopenCreateFormulaModal ? $errors->first('password') : null;

    $templateModalMode = session('structure_template_mode');
    if ($templateModalMode === null) {
        $templateModalMode = old('template_id') ? 'edit' : 'create';
    }

    $templateModalEditId = session('structure_template_edit_id', old('template_id'));
    $reopenTemplateDeleteId = session('reopen_structure_template_delete_modal');
    $deleteTemplatePasswordError = $reopenTemplateDeleteId ? $errors->first('password') : null;

    $hasOldTemplateData = collect($oldTemplateInputs)
        ->filter(function ($value, $key) {
            if ($key === 'components') {
                return is_array($value) && ! empty($value);
            }

            return $value !== null && $value !== '';
        })
        ->isNotEmpty();

    $shouldReopenTemplateModal = $reopenTemplateModalFlag || $structureTemplateError || ! empty($templateErrorMessages) || $hasOldTemplateData;

    $errorFields = array_keys($errorMessages);

    if ($shouldReopenTemplateModal || $shouldReopenCreateFormulaModal) {
        $initialSection = 'formulas';
    }

    $overviewActive = $initialSection === 'overview';
    $formulasActive = $initialSection === 'formulas';

    $departmentCount = $departmentsSummary->count();
    $overrideCount = $departmentsSummary->filter(fn ($summary) => ($summary['catalog_count'] ?? 0) > 0)->count();
    $defaultCount = max($departmentCount - $overrideCount, 0);

    $preservedQuery = \Illuminate\Support\Arr::only($queryParams, ['semester', 'academic_year', 'academic_period_id']);

    $buildRoute = function (string $name, array $parameters = []) use ($preservedQuery) {
        return route($name, array_merge($parameters, $preservedQuery));
    };

    $periodLookup = collect($academicPeriods ?? [])->keyBy('id');

    $structureTemplates = collect($structureCatalog ?? []);
    $structureTemplatePayload = $structureTemplates->map(function ($template) {
        return [
            'id' => $template['id'] ?? null,
            'template_key' => $template['template_key'] ?? ($template['key'] ?? ''),
            'key' => $template['key'] ?? '',
            'label' => $template['label'] ?? '',
            'description' => $template['description'] ?? '',
            'weights' => collect($template['weights'] ?? [])->map(function ($weight) {
                return [
                    'type' => $weight['type'] ?? '',
                    'percent' => (int) ($weight['percent'] ?? 0),
                ];
            })->values()->all(),
            'structure' => $template['structure'] ?? [],
            'is_custom' => (bool) ($template['is_custom'] ?? false),
            'is_system_default' => (bool) ($template['is_system_default'] ?? false),
        ];
    })->values();

    $formulaCount = $structureTemplates->count();
@endphp

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-sliders-fill text-success me-2"></i>Grades Formula Management</h1>
            <p class="text-muted mb-0">Select a wildcard to manage its grading scale</p>
        </div>
        <form method="GET" action="{{ route('admin.gradesFormula') }}" class="d-flex align-items-center gap-2">
            <label class="text-success fw-semibold mb-0"><i class="bi bi-calendar-week me-1"></i>Academic Period:</label>
            <select name="academic_period_id" class="form-select form-select-sm" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
                <option value="">All Periods</option>
                @foreach($academicPeriods ?? [] as $period)
                    <option value="{{ $period->id }}" {{ request('academic_period_id') == $period->id ? 'selected' : '' }}>
                        {{ $period->academic_year }} - {{ $period->semester }}
                    </option>
                @endforeach
            </select>
            @if(request('view'))
                <input type="hidden" name="view" value="{{ request('view') }}">
            @endif
        </form>
    </div>

    @if (session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => window.notify?.success(@json(session('success'))));</script>
    @endif

    @unless($departments->flatMap->courses->flatMap->subjects->isNotEmpty())
        <div class="alert alert-info shadow-sm">
            <i class="bi bi-journal-x me-2"></i>No subjects are registered yet. Add subjects under Courses to start configuring subject-level formulas.
        </div>
    @endunless

    <div class="card border-0 shadow-sm mb-3 bg-gradient-green-card">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle bg-gradient-overlay">
                        <i class="bi bi-collection text-white icon-md"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Wildcard Summary</h6>
                        <small class="opacity-90">{{ $departmentCount }} departments · {{ $overrideCount }} with catalogs · {{ $defaultCount }} using baseline</small>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="bg-white bg-opacity-25 rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2">
                        <small class="fw-semibold text-dark mb-0">
                            <i class="bi bi-lightbulb me-1"></i>Click a card to review or edit its formula
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-layout-three-columns text-success"></i>
                    <span class="fw-semibold text-success">Workspace views</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm rounded-pill wildcard-section-btn {{ $overviewActive ? 'btn-success active' : 'btn-outline-success' }}" data-section-target="overview">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i>Overview
                        <span class="badge bg-white text-success ms-1">{{ $departmentCount }}</span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill wildcard-section-btn {{ $formulasActive ? 'btn-success active' : 'btn-outline-success' }}" data-section-target="formulas">
                        <i class="bi bi-star-fill me-1"></i>Formulas
                        <span class="badge bg-success text-white ms-1">{{ $formulaCount }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div data-section-container data-initial-section="{{ $initialSection }}">
        <div data-section="overview" class="{{ $overviewActive ? '' : 'd-none' }}">
            <div class="section-scroll">
                <div class="d-flex justify-content-end mb-3">
                <form method="GET" action="{{ route('admin.gradesFormula') }}" class="d-flex align-items-center gap-2">
                    <label class="text-success small mb-0">Semester</label>
                    <select name="semester" class="form-select form-select-sm max-w-180" onchange="this.form.submit()">
                        <option value="" {{ request('semester') ? '' : 'selected' }}>All/Default</option>
                        <option value="1st" {{ request('semester')==='1st' ? 'selected' : '' }}>1st</option>
                        <option value="2nd" {{ request('semester')==='2nd' ? 'selected' : '' }}>2nd</option>
                        <option value="Summer" {{ request('semester')==='Summer' ? 'selected' : '' }}>Summer</option>
                    </select>
                    @foreach ($queryParams as $key => $value)
                        @if($key !== 'semester')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                </form>
                </div>

            <div class="row g-4 mb-4" id="overview-department-grid">
                @php
                    $defaultBadgeLabel = optional($globalFormula)->label ?? 'System Default';
                @endphp
                @foreach($departmentsSummary as $summary)
                    @php
                        $department = $summary['department'];
                        $status = $summary['status'];
                    @endphp
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <a href="{{ $buildRoute('admin.gradesFormula.department', ['department' => $department->id]) }}" class="text-decoration-none text-reset">
                            <div class="wildcard-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden cursor-pointer transition-transform-shadow" data-status="{{ $status }}" data-url="{{ $buildRoute('admin.gradesFormula.department', ['department' => $department->id]) }}">
                            {{-- Top header --}}
                            <div class="position-relative header-height-80 bg-gradient-green-soft">
                                <div class="wildcard-circle-positioned">
                                    <span class="text-white fw-bold">{{ $department->department_code }}</span>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="card-body pt-5 text-center">
                                <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $department->department_description }}">
                                    {{ $department->department_description }}
                                </h6>
                                <p class="text-muted small mb-3">{{ $summary['scope_text'] }}</p>

                                {{-- Footer badges --}}
                                <div class="d-flex flex-column gap-2 mt-4">
                                    @if($summary['missing_course_count'] > 0)
                                        <span class="badge bg-danger text-white px-3 py-2 rounded-pill">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $summary['missing_course_count'] }} course{{ $summary['missing_course_count'] === 1 ? '' : 's' }} pending
                                        </span>
                                    @endif
                                    @if($summary['missing_subject_count'] > 0)
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $summary['missing_subject_count'] }} subject{{ $summary['missing_subject_count'] === 1 ? '' : 's' }} pending
                                        </span>
                                    @endif
                                    <span class="badge px-3 py-2 fw-semibold rounded-pill {{ $summary['catalog_count'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                                        @if($summary['catalog_count'] > 0)
                                            ✓ Department Baseline
                                        @else
                                            Department Baseline
                                        @endif
                                    </span>
                                </div>
                            </div>
                            </div>
                        </a>
                    </div>
                @endforeach

                @if($departmentCount === 0)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-5 text-center">
                                <div class="text-muted mb-3">
                                    <i class="bi bi-collection fs-1 opacity-50"></i>
                                </div>
                                <h5 class="text-muted mb-2">No departments available</h5>
                                <p class="text-muted mb-0">Add at least one department to configure formulas.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </div>

        <div data-section="formulas" class="{{ $formulasActive ? '' : 'd-none' }}">
            <div class="section-scroll">
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-semibold text-dark">Structure Templates</h4>
                    <div class="d-flex gap-2">
                        <button type="button" id="open-create-template" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#create-template-modal">
                            <i class="bi bi-plus-circle me-2"></i>Create Structure Template
                        </button>
                        <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#create-formula-modal">
                            <i class="bi bi-globe2 me-2"></i>Create Global Formula
                        </button>
                    </div>
                </div>
                <p class="text-muted small mb-0">Pre-defined grading structures for common course types</p>
            </div>

            <div class="row g-4 mb-4">
                @forelse($structureTemplates as $template)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="structure-card card h-100 border-0 shadow-lg rounded-4">
                            <div class="card-body p-4 d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <h5 class="fw-semibold text-dark mb-1">{{ $template['label'] }}</h5>
                                        <p class="text-muted small mb-0">{{ $template['description'] }}</p>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <span class="badge bg-success-subtle text-success">Structure</span>
                                        @if(!empty($template['id']))
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Manage structure template">
                                                <a href="{{ route('admin.gradesFormula.structureTemplate.edit', array_merge(['template' => $template['id']], $preservedQuery)) }}" class="btn btn-outline-secondary">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                                </a>
                                                <button type="button" class="btn btn-outline-danger js-delete-structure-template" data-template-id="{{ $template['id'] }}" data-template-label="{{ $template['label'] }}">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($template['weights'] as $weight)
                                        @if(!empty($weight['is_composite']))
                                            {{-- Main composite component (e.g., Lecture Component 60%) --}}
                                            <span class="badge bg-primary text-white fw-semibold">{{ $weight['type'] }} {{ $weight['percent'] }}%</span>
                                        @elseif(!empty($weight['is_sub']))
                                            {{-- Sub-component (e.g., Lecture Quiz 40%) --}}
                                            <span class="badge bg-success-subtle text-success ps-3">
                                                <i class="bi bi-arrow-return-right me-1"></i>{{ $weight['type'] }} {{ $weight['percent'] }}%
                                                @if(isset($weight['max_items']) && $weight['max_items'] !== null)
                                                    · max {{ $weight['max_items'] }}
                                                @endif
                                            </span>
                                        @else
                                            {{-- Simple activity --}}
                                            <span class="badge bg-success-subtle text-success">
                                                {{ $weight['type'] }} {{ $weight['percent'] }}%
                                                @if(isset($weight['max_items']) && $weight['max_items'] !== null)
                                                    · max {{ $weight['max_items'] }}
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="structure-card-footer mt-auto">
                                    <small class="text-muted">Select this template when creating a new formula to start with the recommended weights.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info shadow-sm mb-0">
                            <i class="bi bi-info-circle me-2"></i>No structure templates available yet.
                        </div>
                    </div>
                @endforelse
            </div>

            @php
                // REMOVED: $allDepartmentFormulas - department blueprints no longer exist
                $allDepartmentFormulas = collect([]);

                // Fetch global formulas from the controller data
                $globalFormulas = collect($globalFormulasList ?? [])->map(function ($formula) {
                    // Build hierarchical weight display matching structure template format
                    $weights = [];
                    if (is_array($formula->structure_config) && !empty($formula->structure_config)) {
                        $structure = $formula->structure_config;
                        
                        // Walk through the structure to build badges
                        $walkStructure = function($node, $parentWeight = 1.0) use (&$walkStructure, &$weights) {
                            $children = $node['children'] ?? [];
                            
                            foreach ($children as $child) {
                                $childType = $child['type'] ?? 'activity';
                                $childWeight = (float) ($child['weight'] ?? 0);
                                $childLabel = $child['label'] ?? \App\Support\Grades\FormulaStructure::formatLabel($child['key'] ?? '');
                                $relativePercent = (int) round($childWeight * 100);
                                
                                if ($childType === 'composite') {
                                    // This is a composite component (e.g., "Lecture Component 60%")
                                    $weights[] = [
                                        'type' => $childLabel,
                                        'percent' => $relativePercent,
                                        'is_composite' => true,
                                    ];
                                    
                                    // Recursively process children with indentation marker
                                    $walkStructure($child, $parentWeight * $childWeight);
                                } else {
                                    // This is an activity (e.g., "Lecture Quizzes 40%")
                                    $weights[] = [
                                        'type' => $childLabel,
                                        'percent' => $relativePercent,
                                        'is_sub' => isset($node['type']) && $node['type'] === 'composite' && $node['key'] !== 'period_grade',
                                    ];
                                }
                            }
                        };
                        
                        $walkStructure($structure);
                    }

                    return [
                        'id' => $formula->id,
                        'label' => $formula->label ?? 'Global Formula',
                        'is_fallback' => false,
                        'context_label' => $formula->academic_period_id 
                            ? ($formula->semester ? "{$formula->semester} Semester" : 'Period-specific')
                            : 'Applies to all periods',
                        'weights' => $weights,
                        'structure_type' => $formula->structure_type ?? 'lecture_only',
                    ];
                });
            @endphp

            @if($globalFormulas->isNotEmpty())
                <div class="mb-4">
                    <h4 class="mb-3 fw-semibold text-dark">
                        <i class="bi bi-globe2 me-2"></i>Global Formulas
                    </h4>
                    <p class="text-muted small mb-3">Department-independent formulas that can be applied across all departments</p>
                </div>

                <div class="row g-4 mb-4">
                    @foreach($globalFormulas as $formula)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="formula-card card h-100 border-0 shadow-lg rounded-4 border-info" data-formula-id="{{ $formula['id'] }}">
                                <div class="card-body p-4 d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <h5 class="fw-semibold text-dark mb-1">{{ $formula['label'] }}</h5>
                                            <p class="text-muted small mb-2">
                                                <i class="bi bi-globe2 me-1"></i>Global Formula · {{ $formula['context_label'] }}
                                            </p>
                                            <span class="badge bg-info-subtle text-info">
                                                <i class="bi bi-diagram-3 me-1"></i>Department-Independent
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if(!empty($formula['weights']))
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($formula['weights'] as $weight)
                                                @if(!empty($weight['is_composite']))
                                                    {{-- Main composite component (e.g., Lecture Component 60%) --}}
                                                    <span class="badge bg-primary text-white fw-semibold">{{ $weight['type'] }} {{ $weight['percent'] }}%</span>
                                                @elseif(!empty($weight['is_sub']))
                                                    {{-- Sub-component under a composite (e.g., Lecture Quizzes 40%) --}}
                                                    <span class="badge bg-info-subtle text-info ps-3">
                                                        <i class="bi bi-arrow-return-right me-1"></i>{{ $weight['type'] }} {{ $weight['percent'] }}%
                                                    </span>
                                                @else
                                                    {{-- Simple activity (for lecture-only structure) --}}
                                                    <span class="badge bg-info-subtle text-info">{{ $weight['type'] }} {{ $weight['percent'] }}%</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-auto d-flex gap-2">
                                        <a href="{{ route('admin.gradesFormula.edit', ['formula' => $formula['id']]) }}" 
                                           class="btn btn-sm btn-outline-primary flex-grow-1">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger js-delete-global-formula" 
                                                data-formula-id="{{ $formula['id'] }}"
                                                data-formula-label="{{ $formula['label'] }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#delete-global-formula-modal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Department Baseline Formulas Section --}}
            <div class="mb-4">
                <h4 class="mb-3 fw-semibold text-dark">
                    <i class="bi bi-building me-2"></i>Department Baseline Formulas
                </h4>
                <p class="text-muted small mb-3">Configure the default grading formula for each department</p>
            </div>

            <div class="row g-4 mb-4">
                @forelse($departments as $dept)
                    @php
                        // Check if this department has a baseline formula in departmentCatalogs
                        $deptFormulas = $departmentCatalogs->get($dept->id, collect());
                        $baselineFormula = $deptFormulas->first();
                        $hasCustomBaseline = $baselineFormula !== null;
                        $formulaLabel = $hasCustomBaseline ? ($baselineFormula->label ?? 'Unnamed Formula') : null;
                        $globalLabel = optional($globalFormula)->label ?? 'System Default';
                        
                        // Build hierarchical weight display for department baseline
                        $weights = [];
                        if ($hasCustomBaseline && $baselineFormula) {
                            if (is_array($baselineFormula->structure_config) && !empty($baselineFormula->structure_config)) {
                                $structure = $baselineFormula->structure_config;
                                
                                // Walk through the structure to build badges
                                $walkStructure = function($node, $parentWeight = 1.0) use (&$walkStructure, &$weights) {
                                    $children = $node['children'] ?? [];
                                    
                                    foreach ($children as $child) {
                                        $childType = $child['type'] ?? 'activity';
                                        $childWeight = (float) ($child['weight'] ?? 0);
                                        $childLabel = $child['label'] ?? \App\Support\Grades\FormulaStructure::formatLabel($child['key'] ?? '');
                                        $relativePercent = (int) round($childWeight * 100);
                                        
                                        if ($childType === 'composite') {
                                            // Composite component
                                            $weights[] = [
                                                'type' => $childLabel,
                                                'percent' => $relativePercent,
                                                'is_composite' => true,
                                            ];
                                            
                                            // Recursively process children
                                            $walkStructure($child, $parentWeight * $childWeight);
                                        } else {
                                            // Activity
                                            $weights[] = [
                                                'type' => $childLabel,
                                                'percent' => $relativePercent,
                                                'is_sub' => isset($node['type']) && $node['type'] === 'composite' && $node['key'] !== 'period_grade',
                                            ];
                                        }
                                    }
                                };
                                
                                $walkStructure($structure);
                            }
                        }
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="formula-card card h-100 border-0 shadow-lg rounded-4 {{ $hasCustomBaseline ? 'border-success' : '' }}" data-department-id="{{ $dept->id }}">
                            <div class="card-body p-4 d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-success fw-semibold">{{ $dept->department_code }}</span>
                                            <h5 class="fw-semibold text-dark mb-0">{{ $dept->department_description }}</h5>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-shield-check me-1"></i>Department Baseline
                                        </p>
                                        @if($hasCustomBaseline)
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bi bi-check-circle-fill me-1"></i>Custom: {{ $formulaLabel }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary">
                                                <i class="bi bi-globe2 me-1"></i>Using Global: {{ $globalLabel }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($hasCustomBaseline && !empty($weights))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($weights as $weight)
                                            @if(!empty($weight['is_composite']))
                                                {{-- Main composite component --}}
                                                <span class="badge bg-success text-white fw-semibold">{{ $weight['type'] }} {{ $weight['percent'] }}%</span>
                                            @elseif(!empty($weight['is_sub']))
                                                {{-- Sub-component under a composite --}}
                                                <span class="badge bg-success-subtle text-success ps-3">
                                                    <i class="bi bi-arrow-return-right me-1"></i>{{ $weight['type'] }} {{ $weight['percent'] }}%
                                                </span>
                                            @else
                                                {{-- Simple activity --}}
                                                <span class="badge bg-success-subtle text-success">{{ $weight['type'] }} {{ $weight['percent'] }}%</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-auto">
                                    <a href="{{ $buildRoute('admin.gradesFormula.edit.department', ['department' => $dept->id]) }}" 
                                       class="btn btn-sm btn-outline-success w-100">
                                        <i class="bi bi-pencil me-1"></i>Configure Baseline
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info shadow-sm mb-0">
                            <i class="bi bi-info-circle me-2"></i>No departments available to configure
                        </div>
                    </div>
                @endforelse
            </div>

            @if($allDepartmentFormulas->isNotEmpty())
                <div class="mb-4">
                    <h4 class="mb-3 fw-semibold text-dark">Custom Department Formulas</h4>
                    <p class="text-muted small mb-3">Department-specific grading formulas with custom weights</p>
                </div>

                <div class="row g-4">
                    @foreach($allDepartmentFormulas as $formula)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="formula-card card h-100 border-0 shadow-lg rounded-4" data-formula-id="{{ $formula['id'] }}">
                                <div class="card-body p-4 d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <h5 class="fw-semibold text-dark mb-1">{{ $formula['label'] }}</h5>
                                            <p class="text-muted small mb-2">
                                                <i class="bi bi-building me-1"></i>{{ $formula['department_code'] }} - {{ $formula['department_name'] }}
                                            </p>
                                            @if($formula['is_fallback'])
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <i class="bi bi-shield-check me-1"></i>Department Baseline
                                                </span>
                                            @endif
                                        </div>
                                        @if($formula['context_label'])
                                            <span class="badge bg-info-subtle text-info">{{ $formula['context_label'] }}</span>
                                        @endif
                                    </div>
                                    
                                    @if(!empty($formula['weights']))
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($formula['weights'] as $weight)
                                                <span class="badge bg-success-subtle text-success">{{ $weight['type'] }} {{ $weight['percent'] }}%</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-auto d-flex gap-2">
                                        <a href="{{ route('admin.gradesFormula.department.formulas.edit', ['department' => $formula['department_id'], 'formula' => $formula['id']]) }}" 
                                           class="btn btn-sm btn-outline-primary flex-grow-1">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        @if(!$formula['is_fallback'])
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger js-delete-formula" 
                                                    data-formula-id="{{ $formula['id'] }}"
                                                    data-formula-label="{{ $formula['label'] }}"
                                                    data-department-id="{{ $formula['department_id'] }}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#delete-formula-modal">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            </div>
        </div>

        </div>
    </div>
</div>

<div class="modal fade" id="create-formula-modal" tabindex="-1" aria-labelledby="create-formula-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="create-formula-form" method="POST" action="{{ route('admin.gradesFormula.store', $preservedQuery) }}">
                @csrf
                <input type="hidden" name="scope_level" value="global">
                <input type="hidden" name="base_score" value="60">
                <input type="hidden" name="scale_multiplier" value="40">
                <input type="hidden" name="passing_grade" value="75">
                <input type="hidden" id="create-formula-structure-type" name="structure_type" value="">
                <input type="hidden" id="create-formula-structure-config" name="structure_config" value="">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold text-success" id="create-formula-modal-label">
                        <i class="bi bi-globe2 me-2"></i>Create Global Formula
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <small><strong>What are Global Formulas?</strong> Global formulas are custom grading structures you create that can be reused across all departments. Unlike structure templates (which are pre-defined), these are fully customizable formulas that you define.</small>
                    </div>

                    <div class="mb-3">
                        <label for="create-formula-label" class="form-label fw-semibold">Formula Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create-formula-label" name="label" placeholder="e.g., ASBME Engineering Standard" value="{{ $oldGlobalFormulaInputs['label'] }}" required>
                        <small class="text-muted">Choose a descriptive name that indicates the formula's purpose</small>
                    </div>

                    <div class="mb-3">
                        <label for="create-formula-template" class="form-label fw-semibold">Base Structure Template <span class="text-danger">*</span></label>
                        <select class="form-select" id="create-formula-template" name="template_key" required>
                            <option value="">Select a structure template to start</option>
                            @foreach($structureTemplates as $template)
                                <option value="{{ $template['key'] }}" 
                                        data-structure-type="{{ $template['key'] }}"
                                        data-structure="{{ json_encode($template['structure'] ?? []) }}"
                                        data-weights="{{ json_encode($template['weights'] ?? []) }}"
                                        {{ $oldGlobalFormulaInputs['template_key'] === $template['key'] ? 'selected' : '' }}>
                                    {{ $template['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Choose a pre-defined structure template as your starting point. You can edit weights after creation.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Scope</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope_type" id="create-formula-scope-global" value="global" checked>
                            <label class="form-check-label" for="create-formula-scope-global">
                                <strong>Global Formula</strong>
                                <div class="text-muted small">Department-independent, can be applied to any department</div>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="create-formula-context" class="form-label fw-semibold">Context (Optional)</label>
                        <select class="form-select" id="create-formula-context" name="context_type">
                            <option value="" {{ $oldGlobalFormulaInputs['context_type'] ? '' : 'selected' }}>No specific context (Applies to all periods)</option>
                            <option value="semester" {{ $oldGlobalFormulaInputs['context_type'] === 'semester' ? 'selected' : '' }}>Semester-specific</option>
                            <option value="academic_year" {{ $oldGlobalFormulaInputs['context_type'] === 'academic_year' ? 'selected' : '' }}>Academic Year-specific</option>
                        </select>
                        <small class="text-muted">Leave blank to make this formula available for all academic periods</small>
                    </div>

                    <div id="create-formula-context-semester" class="mb-3 d-none">
                        <label for="create-formula-semester" class="form-label fw-semibold">Semester</label>
                        <select class="form-select" id="create-formula-semester" name="semester">
                            <option value="">Select Semester</option>
                            <option value="1" {{ $oldGlobalFormulaInputs['semester'] === '1' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2" {{ $oldGlobalFormulaInputs['semester'] === '2' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="3" {{ $oldGlobalFormulaInputs['semester'] === '3' ? 'selected' : '' }}>Summer</option>
                        </select>
                    </div>

                    <div id="create-formula-context-year" class="mb-3 d-none">
                        <label for="create-formula-year" class="form-label fw-semibold">Academic Year</label>
                        <input type="text" class="form-control" id="create-formula-year" name="academic_year" placeholder="e.g., 2025-2026" value="{{ $oldGlobalFormulaInputs['academic_year'] }}">
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label for="create-formula-password" class="form-label fw-semibold text-danger">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control {{ $globalFormulaPasswordError ? 'is-invalid' : '' }}" id="create-formula-password" name="password" autocomplete="current-password" placeholder="Enter your password to confirm" required>
                        @if($globalFormulaPasswordError)
                            <div class="invalid-feedback">{{ $globalFormulaPasswordError }}</div>
                        @endif
                        <small class="text-muted">Enter your account password to authorize this action</small>
                    </div>

                    <div id="create-formula-error" class="text-danger small d-none" role="alert"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="create-formula-submit">
                        <i class="bi bi-check-circle me-1"></i>Create Formula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-formula-modal" tabindex="-1" aria-labelledby="delete-formula-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="delete-formula-form" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header border-0 pb-0 bg-danger-subtle">
                    <h5 class="modal-title fw-semibold text-danger" id="delete-formula-modal-label">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Formula
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>

                    <p class="mb-3">Are you sure you want to delete this formula?</p>
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <strong id="delete-formula-name">Formula Name</strong>
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label for="delete-formula-password" class="form-label fw-semibold text-danger">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="delete-formula-password" name="password" autocomplete="current-password" placeholder="Enter your password to confirm" required>
                        <small class="text-muted">Enter your account password to authorize this deletion</small>
                    </div>

                    <div id="delete-formula-error" class="text-danger small d-none" role="alert"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="delete-formula-submit">
                        <i class="bi bi-trash me-1"></i>Delete Formula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-global-formula-modal" tabindex="-1" aria-labelledby="delete-global-formula-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="delete-global-formula-form" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header border-0 pb-0 bg-danger-subtle">
                    <h5 class="modal-title fw-semibold text-danger" id="delete-global-formula-modal-label">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Global Formula
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will permanently delete this global formula!
                    </div>

                    <p class="mb-3">Are you sure you want to delete this global formula?</p>
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <strong id="delete-global-formula-name">Formula Name</strong>
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label for="delete-global-formula-password" class="form-label fw-semibold text-danger">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="delete-global-formula-password" name="password" autocomplete="current-password" placeholder="Enter your password to confirm" required>
                        <small class="text-muted">Enter your account password to authorize this deletion</small>
                    </div>

                    <div id="delete-global-formula-error" class="text-danger small d-none" role="alert"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="delete-global-formula-submit">
                        <i class="bi bi-trash me-1"></i>Delete Global Formula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="modal fade" id="delete-structure-template-modal" tabindex="-1" aria-labelledby="delete-structure-template-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form
                    id="delete-structure-template-form"
                    method="POST"
                    data-action="{{ route('admin.gradesFormula.structureTemplate.destroy', array_merge(['template' => 'TEMPLATE_ID'], $preservedQuery)) }}"
                >
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0 bg-danger-subtle">
                        <h5 class="modal-title fw-semibold text-danger" id="delete-structure-template-modal-label">
                            <i class="bi bi-exclamation-octagon me-2"></i>Delete Structure Template
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                            <div>
                                <strong id="delete-template-name" class="d-block mb-1"></strong>
                                <span class="small d-block">Existing formulas keep their saved structure. This only removes the reusable template.</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="delete-template-password" class="form-label fw-semibold text-danger">Account Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="delete-template-password" name="password" autocomplete="current-password" placeholder="Enter your password">
                            <div class="invalid-feedback" id="delete-template-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="delete-template-confirm">
                            <i class="bi bi-trash me-1"></i>Delete Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<div class="modal fade" id="create-template-modal" tabindex="-1" aria-labelledby="create-template-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form
                id="create-template-form"
                method="POST"
                action="{{ route('admin.gradesFormula.structureTemplate.store', $preservedQuery) }}"
                data-store-action="{{ route('admin.gradesFormula.structureTemplate.store', $preservedQuery) }}"
                data-update-action="{{ route('admin.gradesFormula.structureTemplate.update', array_merge(['template' => 'TEMPLATE_ID'], $preservedQuery)) }}"
                data-initial-mode="{{ $templateModalMode }}"
            >
                @csrf
                <input type="hidden" id="template-method-field" name="_method" value="PUT" disabled>
                <input type="hidden" id="template-id-field" name="template_id" value="{{ $templateModalEditId }}">
                <div class="modal-header border-0 pb-0 bg-primary-subtle">
                    <h5 class="modal-title fw-semibold text-primary" id="create-template-modal-label">
                        <i class="bi bi-diagram-3 me-2"></i>Create Structure Template
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <div>
                            <strong>What are Structure Templates?</strong>
                            <p class="mb-2 mt-1 small">Structure templates are reusable grading structures that define how different assessment types contribute to the final grade.</p>
                            <p class="mb-0 small"><strong>Tip:</strong> You can create complex structures like "Lecture + Laboratory" by adding main components (e.g., Lecture 60%, Laboratory 40%) and then clicking "Sub-Component" to add nested assessments (quizzes, exams, OCR) within each.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template-label" class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="template-label" name="template_label" placeholder="e.g., Lecture + Clinical" required>
                                <small class="text-muted">Choose a descriptive name for this grading structure</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="template-key" class="form-label fw-semibold">Template Key <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="template-key" name="template_key" placeholder="e.g., lecture_clinical" pattern="[a-z_]+" required>
                                <small class="text-muted">Unique identifier (lowercase, underscores only)</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="template-description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="template-description" name="template_description" rows="2" placeholder="Describe when this structure should be used..."></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-semibold mb-0">Grade Components</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-component-btn">
                                <i class="bi bi-plus-circle me-1"></i>Add Component
                            </button>
                        </div>
                        <p class="text-muted small mb-3">Define the assessment types and their weights. Total must equal 100%.</p>
                    </div>

                    <div id="components-container" class="mb-4">
                        <!-- Components will be added here dynamically -->
                    </div>

                    <div class="alert alert-warning mb-4 d-none-important" id="weight-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>Total weight must equal <strong>100%</strong>. Current total: <span id="total-weight">0</span>%</small>
                    </div>

                    <input type="hidden" id="template-password-hidden" name="password">
                    <div id="template-error" class="text-danger small d-none" role="alert"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="create-template-submit">
                        <i class="bi bi-check-circle me-1"></i>Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="template-password-modal" tabindex="-1" aria-labelledby="template-password-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 bg-primary-subtle">
                <h5 class="modal-title fw-semibold text-primary" id="template-password-modal-label">
                    <i class="bi bi-shield-lock me-2"></i>Confirm Template Creation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Enter your account password to confirm creating this structure template.</p>
                <div class="mb-3">
                    <label for="template-password-input" class="form-label fw-semibold text-primary">Account Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="template-password-input" autocomplete="current-password" placeholder="Enter your password">
                    <div class="invalid-feedback">Password is required.</div>
                </div>
                <div id="template-password-modal-error" class="text-danger small d-none" role="alert" aria-live="assertive"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="template-password-confirm">
                    <i class="bi bi-check-circle me-1"></i>Confirm and Create
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/admin/grades-formula-wildcards.js --}}
<script>
    window.pageData = {
        shouldReopenTemplateModal: @json($shouldReopenTemplateModal),
        shouldReopenCreateFormulaModal: @json($shouldReopenCreateFormulaModal),
        templateModalMode: @json($templateModalMode),
        templateModalEditId: @json($templateModalEditId),
        reopenTemplateDeleteId: @json($reopenTemplateDeleteId),
        deleteTemplatePasswordError: @json($deleteTemplatePasswordError),
        structureTemplates: @json($structureTemplatePayload),
        templateErrorMessages: @json($templateErrorMessages ?? []),
        oldTemplateInputs: @json($oldTemplateInputs ?? [])
    };
</script>
@endpush

{{-- Styles: resources/css/admin/grades-formula.css --}}
@push('styles')
@endpush
