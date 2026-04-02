@extends('layouts.app')

@section('content')
@php
    $outcomeCodePrefix = strtoupper((string) ($outcomeCodePrefix ?? 'PO'));
    $defaultOutcomeCount = (int) ($defaultOutcomeCount ?? \App\Services\CourseOutcomeReportingService::DEFAULT_PLO_COUNT);
    $visiblePloDefinitions = collect($ploDefinitions ?? [])->filter(fn ($plo) => !$plo->is_deleted)->values();
    $availableCourseOutcomeRows = collect($availableCourseOutcomeRows ?? [])->values()->all();

    $definitionRows = old('plos');
    if ($definitionRows === null) {
        $definitionRows = $visiblePloDefinitions->map(function ($plo) {
            return [
                'id' => $plo->id,
                'code' => $plo->plo_code,
                'title' => $plo->title,
                'is_active' => $plo->is_active,
                'delete' => false,
            ];
        })->values()->all();
    }

    $selectedMappings = old('mappings', $ploMappingCourseOutcomeIds ?? []);
    $shouldOpenPloModal = session('openPloModal') || $errors->any();
    $availableCoSummary = match (count($availableCourseOutcomeRows)) {
        0 => 'No course outcomes available',
        1 => '1 course outcome available',
        default => count($availableCourseOutcomeRows) . ' course outcomes available',
    };
    $defaultOutcomeEnd = str_pad((string) $defaultOutcomeCount, 2, '0', STR_PAD_LEFT);
    $activePloTab = session('ploTab', 'definitions');
    $matrixSubjectOptions = collect($availableCourseOutcomeRows)
        ->map(function (array $row) {
            $code = (string) ($row['subject_code'] ?? 'Unknown Subject');
            $description = trim((string) ($row['subject_description'] ?? ''));

            return [
                'code' => $code,
                'label' => $description !== '' ? $code . ' - ' . $description : $code,
            ];
        })
        ->unique('code')
        ->values();
    $mappedCourseOutcomeIds = collect($selectedMappings)
        ->flatMap(fn ($mappingIds) => collect($mappingIds))
        ->map(fn ($id) => (string) $id)
        ->unique()
        ->all();
@endphp

<div class="container-fluid px-4 py-5">
    @include('chairperson.partials.toast-notifications')

    @include('chairperson.partials.reports-header', [
        'title' => 'Program Outcomes Summary',
        'subtitle' => 'Track Program Learning Outcome attainment for ' . ($program->course_code ?? 'your assigned program'),
        'icon' => 'bi-diagram-3',
        'academicYear' => $academicYear,
        'semester' => $semester
    ])

    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Program Outcomes Reports']
    ]" />

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mt-4 mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>Please review the PLO configuration details and try again.
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold mb-1">Assigned Program</div>
                    <div class="fw-semibold text-dark">{{ $program->course_code ?? 'N/A' }}</div>
                    <div class="text-muted small">{{ $program->course_description ?? 'No program description available.' }}</div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <div class="badge text-bg-light px-3 py-2 rounded-pill">
                        {{ collect($activePloDefinitions ?? [])->count() }} active PLO{{ collect($activePloDefinitions ?? [])->count() === 1 ? '' : 's' }}
                    </div>
                    <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#configurePloModal">
                        <i class="bi bi-sliders2 me-2"></i>Configure PLOs
                    </button>
                </div>
            </div>

            @if (collect($activePloDefinitions ?? [])->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-diagram-3 text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold">No active PLOs configured yet</h5>
                    <p class="text-muted mb-4">Set up your Program Learning Outcomes to start viewing the summary table.</p>
                    <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#configurePloModal">
                        Configure PLOs
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start align-middle" style="min-width: 280px;">
                                    <i class="bi bi-mortarboard text-primary me-2"></i>Program
                                </th>
                                @foreach($activePloDefinitions as $plo)
                                    <th class="text-center align-middle" style="min-width: 170px;" title="{{ $plo->title }}">
                                        <div class="fw-semibold">{{ $plo->plo_code }}</div>
                                        <small class="text-muted d-block mt-1">{{ \Illuminate\Support\Str::limit($plo->title, 80) }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byProgram as $programId => $row)
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-semibold">{{ $row['program']->course_code ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $row['program']->course_description ?? '' }}</small>
                                    </td>
                                    @foreach($activePloDefinitions as $plo)
                                        @php($value = $row['plos'][$plo->id] ?? null)
                                        <td class="text-center">
                                            @if($value)
                                                @php($threshold = (float) ($value['target_percentage'] ?? 0))
                                                @php($level = $value['level'] ?? ['label' => '', 'tone' => 'success'])
                                                @php($toneClass = match($level['tone'] ?? 'success') {
                                                    'danger' => 'bg-danger-subtle text-danger-emphasis',
                                                    'warning' => 'bg-warning-subtle text-warning-emphasis',
                                                    default => 'bg-success-subtle text-success-emphasis',
                                                })
                                                @php($levelBannerClass = match($level['tone'] ?? 'success') {
                                                    'danger' => 'plo-level-banner-danger',
                                                    'warning' => 'plo-level-banner-warning',
                                                    default => 'plo-level-banner-success',
                                                })
                                                <span class="badge {{ $toneClass }} px-3 py-2 rounded-pill">
                                                    {{ number_format((float) $value['percent'], 2) }}%
                                                </span>
                                                <div class="mt-2 plo-result-meta">
                                                    <div class="plo-result-chips">
                                                        @foreach($value['co_codes'] as $coCode)
                                                            <span class="plo-result-chip">{{ $coCode }}</span>
                                                        @endforeach
                                                    </div>
                                                    <div class="plo-target-text">Target {{ number_format($threshold, 2) }}%</div>
                                                    @if(!empty($level['label']))
                                                        <div class="plo-level-banner {{ $levelBannerClass }}">{{ $level['label'] }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted fs-5">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ collect($activePloDefinitions)->count() + 1 }}" class="text-center py-5">
                                        <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                                        <p class="text-muted mb-0">No assessed program outcomes found for this academic period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="configurePloModal" tabindex="-1" aria-labelledby="configurePloModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable plo-config-modal-dialog">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header bg-success text-white">
                <div>
                    <h4 class="modal-title fw-bold" id="configurePloModalLabel">Configure Program Learning Outcomes</h4>
                    <p class="mb-0 text-white-50">Set up the PLOs for <span class="fw-semibold text-white">{{ $program->course_code }}</span> and choose which CO slots contribute to each one.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body modal-plo-body">
                <div class="plo-workflow-guide mb-4" aria-label="PLO configuration workflow">
                    <div class="plo-guide-step">
                        <span class="plo-guide-badge">Step 1</span>
                        <div>
                            <div class="fw-semibold text-dark">Define outcomes for the summary table</div>
                            <small class="text-muted">First define the PLOs you want to show in the summary table.</small>
                        </div>
                    </div>
                    <div class="plo-guide-step">
                        <span class="plo-guide-badge">Step 2</span>
                        <div>
                            <div class="fw-semibold text-dark">Link CO rows to each PLO</div>
                            <small class="text-muted">Acadex automatically computes each PLO by averaging only the COs linked to that PLO.</small>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs plo-config-tabs mb-0" id="ploConfigTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link {{ $activePloTab === 'definitions' ? 'active' : '' }}"
                            id="plo-definitions-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#plo-definitions-panel"
                            type="button"
                            role="tab"
                            aria-controls="plo-definitions-panel"
                            aria-selected="{{ $activePloTab === 'definitions' ? 'true' : 'false' }}"
                        >
                            PLO Definitions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link {{ $activePloTab === 'mapping' ? 'active' : '' }}"
                            id="plo-mapping-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#plo-mapping-panel"
                            type="button"
                            role="tab"
                            aria-controls="plo-mapping-panel"
                            aria-selected="{{ $activePloTab === 'mapping' ? 'true' : 'false' }}"
                        >
                            CO to PLO Mapping
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-4" id="ploConfigTabContent">
                    <div
                        class="tab-pane fade {{ $activePloTab === 'definitions' ? 'show active' : '' }}"
                        id="plo-definitions-panel"
                        role="tabpanel"
                        aria-labelledby="plo-definitions-tab"
                    >
                        <form method="POST" action="{{ route('chairperson.reports.co-program.plos.save') }}" id="ploDefinitionsForm">
                            @csrf

                            <div class="border rounded-4 bg-white p-3 p-lg-4 plo-definitions-card">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1">PLO Definitions</h5>
                                        <p class="text-muted mb-0">Keep the codes short and use titles that are easy to recognize in the report.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-success rounded-pill" id="addPloRowButton">
                                        <i class="bi bi-plus-circle me-2"></i>Add PLO
                                    </button>
                                </div>

                                <div class="plo-definition-guidance mb-3" role="note" aria-label="PLO definition reminders">
                                    <span>Default: {{ $outcomeCodePrefix }}01 to {{ $outcomeCodePrefix }}{{ $defaultOutcomeEnd }}</span>
                                    <span>Maximum: 20 PLOs</span>
                                    <span>Inactive PLOs are hidden from the table</span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0 plo-definition-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 140px;">Code</th>
                                                <th scope="col">Title</th>
                                                <th scope="col" class="text-center" style="width: 120px;">Active</th>
                                                <th scope="col" class="text-end" style="width: 100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ploDefinitionRows">
                                            @foreach($definitionRows as $index => $row)
                                                @php($isDeleted = filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL))
                                                <tr class="plo-definition-row {{ $isDeleted ? 'd-none' : '' }}">
                                                    <td>
                                                        <input type="hidden" name="plos[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                                                        <input type="hidden" name="plos[{{ $index }}][delete]" value="{{ $isDeleted ? 1 : 0 }}" class="plo-delete-input">
                                                        <input type="text" name="plos[{{ $index }}][code]" value="{{ $row['code'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ $outcomeCodePrefix }}01" aria-label="Program learning outcome code" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="plos[{{ $index }}][title]" value="{{ $row['title'] ?? '' }}" class="form-control form-control-sm" placeholder="Program Outcome description" aria-label="Program learning outcome title" required>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="hidden" name="plos[{{ $index }}][is_active]" value="0">
                                                        <div class="form-check d-inline-flex justify-content-center">
                                                            <input class="form-check-input mt-0" type="checkbox" name="plos[{{ $index }}][is_active]" value="1" aria-label="Set PLO as active" {{ !empty($row['is_active']) ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-plo-row">
                                                            <i class="bi bi-trash3 me-1"></i>Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                    <small class="text-muted">Save definitions first before mapping newly added PLOs.</small>
                                    <button type="submit" class="btn btn-success rounded-pill px-4">
                                        Save Definitions
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div
                        class="tab-pane fade {{ $activePloTab === 'mapping' ? 'show active' : '' }}"
                        id="plo-mapping-panel"
                        role="tabpanel"
                        aria-labelledby="plo-mapping-tab"
                    >
                        <form method="POST" action="{{ route('chairperson.reports.co-program.plos.mappings.save') }}">
                            @csrf

                            <div class="border rounded-4 bg-white p-3 p-lg-4 plo-mapping-card">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1">CO to PLO Mapping</h5>
                                        <p class="text-muted mb-0">Choose which COs belong to each PLO. The system will automatically average the COs you select for that PLO.</p>
                                    </div>
                                    <div class="badge text-bg-light rounded-pill px-3 py-2 plo-co-summary-badge">Available COs: {{ $availableCoSummary }}</div>
                                </div>

                                @if(empty($availableCourseOutcomeRows))
                                    <div class="alert alert-light border rounded-4 mb-0">
                                        <div class="fw-semibold text-dark mb-1">No course outcomes available yet</div>
                                        <small class="text-muted">
                                            This program has no COs configured for the current academic period yet. Set up Course Outcomes first, then return here to link them to PLOs.
                                        </small>
                                    </div>
                                @elseif($visiblePloDefinitions->isEmpty())
                                    <div class="alert alert-light border rounded-4 mb-0">
                                        <div class="fw-semibold text-dark mb-1">No outcomes configured for mapping</div>
                                        <small class="text-muted">
                                            Add or restore at least one program outcome in the definitions tab before setting matrix links.
                                        </small>
                                    </div>
                                @else
                                    <div class="plo-mapping-toolbar mb-3" role="region" aria-label="Mapping tools">
                                        <div class="po-matrix-toolbar-head">
                                            <small class="text-muted po-matrix-toolbar-hint">Use the matrix checkboxes to link each course outcome row to one or more program outcomes. Computation still uses average values of selected mapped outcomes.</small>
                                            <div class="po-matrix-context-chip" id="poMatrixContextChip" aria-live="polite">
                                                Viewing: All subjects
                                            </div>
                                        </div>

                                        <div class="po-matrix-control-grid" role="group" aria-label="Course outcome matrix filters">
                                            <div class="po-matrix-control">
                                                <label for="poMatrixSubjectFilter" class="visually-hidden">Subject</label>
                                                <select id="poMatrixSubjectFilter" class="form-select form-select-sm" aria-label="Filter by subject">
                                                    <option value="">All subjects</option>
                                                    @foreach($matrixSubjectOptions as $subjectOption)
                                                        <option value="{{ strtolower($subjectOption['code']) }}">{{ $subjectOption['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="po-matrix-control">
                                                <label for="poMatrixSearch" class="visually-hidden">Search CO row</label>
                                                <input
                                                    type="search"
                                                    id="poMatrixSearch"
                                                    class="form-control form-control-sm"
                                                    placeholder="Search subject, CO code, or description"
                                                    aria-label="Search course outcome rows"
                                                    autocomplete="off"
                                                >
                                            </div>

                                            <div class="po-matrix-control">
                                                <label for="poMatrixStateFilter" class="visually-hidden">Row state</label>
                                                <select id="poMatrixStateFilter" class="form-select form-select-sm" aria-label="Filter by row state">
                                                    <option value="all">All rows</option>
                                                    <option value="mapped">Mapped rows</option>
                                                    <option value="unmapped">Unmapped rows</option>
                                                </select>
                                            </div>

                                            <div class="po-matrix-control po-matrix-control-action">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="poMatrixClearFilters">
                                                    Clear filters
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive po-matrix-wrap">
                                        <table class="table table-bordered align-middle mb-0 po-matrix-table" aria-describedby="poMatrixLegend">
                                            <caption class="visually-hidden">Matrix mapping course outcome rows to program learning outcomes.</caption>
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-start po-matrix-sticky-col" style="min-width: 130px;">
                                                        Course
                                                    </th>
                                                    <th class="text-start po-matrix-sticky-col-secondary" style="min-width: 220px;">
                                                        Course Outcome Row
                                                    </th>
                                                    @foreach($visiblePloDefinitions as $plo)
                                                        <th class="text-center po-matrix-outcome-col" style="min-width: 110px;" title="{{ $plo->title }}" data-plo-key="{{ strtolower($plo->plo_code) }}">
                                                            <span class="po-matrix-code">{{ $plo->plo_code }}</span>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($currentSubjectCode = null)
                                                @foreach($availableCourseOutcomeRows as $row)
                                                    @php($subjectCode = (string) ($row['subject_code'] ?? 'Unknown Subject'))
                                                    @php($subjectDescription = trim((string) ($row['subject_description'] ?? '')))
                                                    @php($subjectLabel = $subjectDescription !== '' ? $subjectCode . ' - ' . $subjectDescription : $subjectCode)
                                                    @php($coIdentifier = (string) ($row['co_identifier'] ?: $row['co_code']))
                                                    @php($searchIndex = strtolower(trim($subjectCode . ' ' . $subjectDescription . ' ' . $coIdentifier . ' ' . (string) ($row['co_code'] ?? '') . ' ' . (string) ($row['description'] ?? ''))))
                                                    @php($rowMapped = in_array((string) $row['id'], $mappedCourseOutcomeIds, true))

                                                    @if($currentSubjectCode !== $subjectCode)
                                                        <tr class="po-matrix-group-row" data-subject="{{ strtolower($subjectCode) }}">
                                                            <td colspan="{{ $visiblePloDefinitions->count() + 2 }}">
                                                                <div class="po-matrix-group-label">
                                                                    <span class="po-matrix-group-code">{{ $subjectCode }}</span>
                                                                    @if($subjectDescription !== '')
                                                                        <span class="po-matrix-group-description">{{ $subjectDescription }}</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @php($currentSubjectCode = $subjectCode)
                                                    @endif

                                                    <tr
                                                        class="po-matrix-data-row {{ $rowMapped ? 'is-mapped' : '' }}"
                                                        data-subject="{{ strtolower($subjectCode) }}"
                                                        data-subject-label="{{ $subjectLabel }}"
                                                        data-search="{{ $searchIndex }}"
                                                        data-row-mapped="{{ $rowMapped ? 'mapped' : 'unmapped' }}"
                                                    >
                                                        <td class="text-start po-matrix-sticky-col">
                                                            <div class="fw-semibold">{{ $subjectCode }}</div>
                                                        </td>
                                                        <td class="text-start po-matrix-sticky-col-secondary">
                                                            <div class="fw-semibold po-row-identifier">{{ $coIdentifier }}</div>
                                                            <div class="text-muted small">{{ $row['co_code'] }} | Target {{ number_format((float) ($row['target_percentage'] ?? 75), 2) }}%</div>
                                                            <div class="small text-muted mt-1 po-row-description">{{ $row['description'] }}</div>
                                                        </td>
                                                        @foreach($visiblePloDefinitions as $plo)
                                                            @php($checkedMappings = collect($selectedMappings[$plo->id] ?? [])->map(fn ($value) => (string) $value)->all())
                                                            @php($isChecked = in_array((string) $row['id'], $checkedMappings, true))
                                                            <td class="text-center po-matrix-cell">
                                                                <input
                                                                    class="form-check-input po-matrix-input"
                                                                    type="checkbox"
                                                                    name="mappings[{{ $plo->id }}][]"
                                                                    value="{{ $row['id'] }}"
                                                                    {{ $isChecked ? 'checked' : '' }}
                                                                    aria-label="Map {{ $row['co_identifier'] ?: $row['co_code'] }} to {{ $plo->plo_code }}"
                                                                >
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="po-matrix-legend mt-3" id="poMatrixLegend">
                                        <div class="po-matrix-legend-title"><i class="bi bi-bookmark-star-fill me-2" aria-hidden="true"></i>PLO Legend Reference</div>
                                        <div class="po-matrix-legend-subtitle">Use these labels as quick references for matrix headers while mapping rows.</div>
                                        <div class="po-matrix-legend-grid">
                                            @foreach($visiblePloDefinitions as $plo)
                                                <div class="po-matrix-legend-item" data-plo-key="{{ strtolower($plo->plo_code) }}" tabindex="0" aria-label="Highlight {{ $plo->plo_code }} column">
                                                    <span class="po-matrix-legend-code">{{ $plo->plo_code }}</span>
                                                    <span class="po-matrix-legend-text">{{ $plo->title }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-4">
                                        <small class="text-muted">Review filters before saving to avoid missing hidden rows.</small>
                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                            Save Mapping
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<template id="ploDefinitionRowTemplate">
    <tr class="plo-definition-row">
        <td>
            <input type="hidden" name="plos[__INDEX__][id]" value="">
            <input type="hidden" name="plos[__INDEX__][delete]" value="0" class="plo-delete-input">
            <input type="text" name="plos[__INDEX__][code]" value="__CODE__" class="form-control form-control-sm" placeholder="{{ $outcomeCodePrefix }}01" aria-label="Program learning outcome code" required>
        </td>
        <td>
            <input type="text" name="plos[__INDEX__][title]" value="Program Outcome __NUMBER__" class="form-control form-control-sm" placeholder="Program Outcome description" aria-label="Program learning outcome title" required>
        </td>
        <td class="text-center">
            <input type="hidden" name="plos[__INDEX__][is_active]" value="0">
            <div class="form-check d-inline-flex justify-content-center">
                <input class="form-check-input mt-0" type="checkbox" name="plos[__INDEX__][is_active]" value="1" aria-label="Set PLO as active" checked>
            </div>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-plo-row">
                <i class="bi bi-trash3 me-1"></i>Remove
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('configurePloModal');
    const rowsContainer = document.getElementById('ploDefinitionRows');
    const addButton = document.getElementById('addPloRowButton');
    const template = document.getElementById('ploDefinitionRowTemplate');
    const matrixSubjectFilter = document.getElementById('poMatrixSubjectFilter');
    const matrixSearchInput = document.getElementById('poMatrixSearch');
    const matrixStateFilter = document.getElementById('poMatrixStateFilter');
    const matrixClearButton = document.getElementById('poMatrixClearFilters');
    const matrixContextChip = document.getElementById('poMatrixContextChip');
    const matrixRows = Array.from(document.querySelectorAll('.po-matrix-data-row'));
    const matrixGroupRows = Array.from(document.querySelectorAll('.po-matrix-group-row'));
    const matrixInputs = Array.from(document.querySelectorAll('.po-matrix-input'));
    const matrixOutcomeHeaders = Array.from(document.querySelectorAll('.po-matrix-outcome-col[data-plo-key]'));
    const matrixLegendItems = Array.from(document.querySelectorAll('.po-matrix-legend-item[data-plo-key]'));
    const outcomePrefix = @json($outcomeCodePrefix);

    if (!modalElement || !rowsContainer || !addButton || !template) {
        return;
    }

    const bootstrapModal = window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
    const shouldOpen = @json($shouldOpenPloModal);

    if (shouldOpen && bootstrapModal) {
        bootstrapModal.show();
    }

    const getVisibleRows = () => Array.from(rowsContainer.querySelectorAll('.plo-definition-row'))
        .filter((row) => !row.classList.contains('d-none'));

    const escapedPrefix = String(outcomePrefix).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const codePattern = new RegExp(`^${escapedPrefix}(\\d{2})$`);

    const getUsedNumbers = () => getVisibleRows()
        .map((row) => row.querySelector('input[name$="[code]"]'))
        .filter(Boolean)
        .map((input) => {
            const match = (input.value || '').toUpperCase().match(codePattern);
            return match ? Number(match[1]) : null;
        })
        .filter((value) => Number.isInteger(value));

    const nextPloNumber = () => {
        const used = new Set(getUsedNumbers());
        for (let i = 1; i <= 20; i += 1) {
            if (!used.has(i)) {
                return i;
            }
        }

        return null;
    };

    const refreshAddButtonState = () => {
        addButton.disabled = getVisibleRows().length >= 20;
    };

    addButton.addEventListener('click', function () {
        const nextNumber = nextPloNumber();
        if (nextNumber === null) {
            refreshAddButtonState();
            return;
        }

        const index = rowsContainer.querySelectorAll('.plo-definition-row').length;
        const paddedNumber = String(nextNumber).padStart(2, '0');
        const generatedCode = `${outcomePrefix}${paddedNumber}`;
        const html = template.innerHTML
            .replaceAll('__INDEX__', String(index))
            .replaceAll('__NUMBER__', paddedNumber)
            .replaceAll('__CODE__', generatedCode);

        rowsContainer.insertAdjacentHTML('beforeend', html);
        refreshAddButtonState();
    });

    rowsContainer.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-plo-row');
        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('.plo-definition-row');
        if (!row) {
            return;
        }

        const idInput = row.querySelector('input[name$="[id]"]');
        const deleteInput = row.querySelector('.plo-delete-input');

        if (idInput && idInput.value) {
            if (deleteInput) {
                deleteInput.value = '1';
            }
            row.classList.add('d-none');
        } else {
            row.remove();
        }

        refreshAddButtonState();
    });

    const normalize = (value) => String(value || '').trim().toLowerCase();

    const setPloHighlight = (ploKey, shouldHighlight) => {
        const normalizedKey = normalize(ploKey);

        matrixOutcomeHeaders.forEach((header) => {
            const isMatch = normalize(header.dataset.ploKey) === normalizedKey;
            header.classList.toggle('is-highlighted', shouldHighlight && isMatch);
        });

        matrixLegendItems.forEach((item) => {
            const isMatch = normalize(item.dataset.ploKey) === normalizedKey;
            item.classList.toggle('is-highlighted', shouldHighlight && isMatch);
        });
    };

    const updateMappedRowState = () => {
        matrixRows.forEach((row) => {
            const checkedCount = row.querySelectorAll('.po-matrix-input:checked').length;
            const mappedState = checkedCount > 0 ? 'mapped' : 'unmapped';
            row.dataset.rowMapped = mappedState;
            row.classList.toggle('is-mapped', mappedState === 'mapped');
        });
    };

    const applyMatrixFilters = () => {
        if (matrixRows.length === 0) {
            return;
        }

        updateMappedRowState();

        const selectedSubject = normalize(matrixSubjectFilter ? matrixSubjectFilter.value : '');
        const searchValue = normalize(matrixSearchInput ? matrixSearchInput.value : '');
        const selectedState = matrixStateFilter ? matrixStateFilter.value : 'all';
        const visibleBySubject = new Map();

        matrixRows.forEach((row) => {
            const rowSubject = normalize(row.dataset.subject);
            const rowSearch = normalize(row.dataset.search);
            const rowState = row.dataset.rowMapped || 'unmapped';
            const matchesSubject = selectedSubject === '' || rowSubject === selectedSubject;
            const matchesSearch = searchValue === '' || rowSearch.includes(searchValue);
            const matchesState = selectedState === 'all' || rowState === selectedState;
            const isVisible = matchesSubject && matchesSearch && matchesState;

            row.classList.toggle('d-none', !isVisible);

            const subjectKey = row.dataset.subject || '';
            if (!visibleBySubject.has(subjectKey)) {
                visibleBySubject.set(subjectKey, false);
            }

            if (isVisible) {
                visibleBySubject.set(subjectKey, true);
            }
        });

        matrixGroupRows.forEach((groupRow) => {
            const subjectKey = groupRow.dataset.subject || '';
            groupRow.classList.toggle('d-none', visibleBySubject.get(subjectKey) !== true);
        });

        if (matrixContextChip) {
            const visibleRows = matrixRows.filter((row) => !row.classList.contains('d-none'));
            const firstVisibleRow = visibleRows[0];
            matrixContextChip.textContent = firstVisibleRow
                ? `Viewing: ${firstVisibleRow.dataset.subjectLabel || firstVisibleRow.dataset.subject || 'All subjects'} (${visibleRows.length}/${matrixRows.length})`
                : 'Viewing: No matching course outcomes';
        }
    };

    if (matrixSubjectFilter) {
        matrixSubjectFilter.addEventListener('change', applyMatrixFilters);
    }

    if (matrixSearchInput) {
        matrixSearchInput.addEventListener('input', applyMatrixFilters);
    }

    if (matrixStateFilter) {
        matrixStateFilter.addEventListener('change', applyMatrixFilters);
    }

    if (matrixClearButton) {
        matrixClearButton.addEventListener('click', function () {
            if (matrixSubjectFilter) {
                matrixSubjectFilter.value = '';
            }

            if (matrixSearchInput) {
                matrixSearchInput.value = '';
            }

            if (matrixStateFilter) {
                matrixStateFilter.value = 'all';
            }

            applyMatrixFilters();
        });
    }

    matrixInputs.forEach((input) => {
        input.addEventListener('change', applyMatrixFilters);
    });

    matrixLegendItems.forEach((item) => {
        const ploKey = item.dataset.ploKey;
        if (!ploKey) {
            return;
        }

        item.addEventListener('mouseenter', function () {
            setPloHighlight(ploKey, true);
        });

        item.addEventListener('mouseleave', function () {
            setPloHighlight(ploKey, false);
        });

        item.addEventListener('focus', function () {
            setPloHighlight(ploKey, true);
        });

        item.addEventListener('blur', function () {
            setPloHighlight(ploKey, false);
        });
    });

    matrixOutcomeHeaders.forEach((header) => {
        const ploKey = header.dataset.ploKey;
        if (!ploKey) {
            return;
        }

        header.addEventListener('mouseenter', function () {
            setPloHighlight(ploKey, true);
        });

        header.addEventListener('mouseleave', function () {
            setPloHighlight(ploKey, false);
        });
    });

    refreshAddButtonState();
    applyMatrixFilters();
});
</script>
@endpush
@endsection
