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
    $availableCoSummary = match (count($availableCourseOutcomeRows)) {
        0 => '0 COs',
        1 => '1 CO',
        default => count($availableCourseOutcomeRows) . ' COs',
    };
    $defaultOutcomeEnd = str_pad((string) $defaultOutcomeCount, 2, '0', STR_PAD_LEFT);
    $requestedPloTab = (string) session('ploTab', 'reports');
    $activePloTab = in_array($requestedPloTab, ['reports', 'definitions', 'mapping'], true)
        ? $requestedPloTab
        : 'reports';
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
    $matrixSubjectCounts = collect($availableCourseOutcomeRows)
        ->countBy(function (array $row) {
            return strtolower((string) ($row['subject_code'] ?? 'Unknown Subject'));
        });
    $mappedCourseOutcomeIds = collect($selectedMappings)
        ->flatMap(fn ($mappingIds) => collect($mappingIds))
        ->map(fn ($id) => (string) $id)
        ->unique()
        ->all();
@endphp

@push('styles')
<style>
/* Fallback: keep resize control visibly button-like even if bundled CSS is stale. */
#poMatrixResizeHandle {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.28rem;
    width: 100%;
    min-height: 64px;
    padding: 0.55rem 1rem;
    border: 2px solid #1f5f42;
    border-radius: 0.9rem;
    background: linear-gradient(180deg, #eaf7ef 0%, #d5ecdd 100%);
    color: #1f4f39;
    cursor: ns-resize;
    user-select: none;
    touch-action: none;
    font-size: 0.8rem;
    font-weight: 800;
    line-height: 1.18;
    letter-spacing: 0.012em;
}

#poMatrixResizeHandle .po-matrix-resize-arrow {
    flex: 1 1 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#poMatrixResizeHandle .po-matrix-resize-arrow i {
    font-size: 1.42rem;
    font-weight: 900;
}

#poMatrixResizeHandle .po-matrix-resize-primary {
    flex: 1 1 100%;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 900;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

#poMatrixResizeHandle .po-matrix-resize-label {
    flex: 1 1 auto;
    white-space: normal;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 700;
}

#poMatrixResizeHandle .po-matrix-resize-value {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 56px;
    margin-left: 0.35rem;
    padding: 0.14rem 0.48rem;
    border-radius: 999px;
    border: 1px solid #a8cdb8;
    background: #fff;
    color: #1e523a;
    font-size: 0.68rem;
    font-weight: 800;
    line-height: 1;
}
</style>
@endpush

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

    <div class="card border-0 shadow-sm rounded-4 mt-4 co-program-tabs-shell" id="coProgramPloWorkspace" data-ui="co-plo-page-workspace" data-outcome-prefix="{{ $outcomeCodePrefix }}">
        <div class="card-body p-4 modal-plo-body co-program-tabs-body">
            <ul class="nav nav-tabs plo-config-tabs mb-0" id="ploConfigTabs" role="tablist" data-ui="co-plo-page-tabs">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link {{ $activePloTab === 'reports' ? 'active' : '' }}"
                        id="plo-reports-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#plo-reports-panel"
                        type="button"
                        role="tab"
                        aria-controls="plo-reports-panel"
                        aria-selected="{{ $activePloTab === 'reports' ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-bar-chart-fill me-1"></i>Program Outcome Reports
                    </button>
                </li>
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
                        <i class="bi bi-book me-1"></i>PLO Definitions
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
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i>CO to PLO Mapping
                    </button>
                </li>
            </ul>

            <div class="tab-content pt-4 plo-config-tab-content" id="ploConfigTabContent">
                <div
                    class="tab-pane fade {{ $activePloTab === 'reports' ? 'show active' : '' }} plo-config-pane"
                    id="plo-reports-panel"
                    role="tabpanel"
                    aria-labelledby="plo-reports-tab"
                >
                    <div class="border rounded-4 bg-white p-3 p-lg-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                                <div>
                                    <div class="text-uppercase text-muted small fw-semibold mb-1">Assigned Program</div>
                                    <div class="fw-semibold text-dark">{{ $program->course_code ?? 'N/A' }}</div>
                                    <div class="text-muted small">{{ $program->course_description ?? 'No program description available.' }}</div>
                                </div>

                                <div class="badge text-bg-light px-3 py-2 rounded-pill">
                                    {{ collect($activePloDefinitions ?? [])->count() }} active PLO{{ collect($activePloDefinitions ?? [])->count() === 1 ? '' : 's' }}
                                </div>
                            </div>

                            @if (collect($activePloDefinitions ?? [])->isEmpty())
                                <div class="text-center py-5">
                                    <i class="bi bi-diagram-3 text-muted fs-1 d-block mb-3"></i>
                                    <h5 class="fw-semibold">No active PLOs configured yet</h5>
                                    <p class="text-muted mb-0">Open the PLO Definitions tab to set up outcomes for this report.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
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
                                                    <td colspan="{{ collect($activePloDefinitions)->count() }}" class="text-center py-5">
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

                    <div
                        class="tab-pane fade {{ $activePloTab === 'definitions' ? 'show active' : '' }} plo-config-pane"
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
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-success rounded-pill shadow-sm" id="addPloRowButton">
                                            <i class="bi bi-plus-circle me-2"></i>Add PLO
                                        </button>
                                    </div>
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
                                                        <input type="text" name="plos[{{ $index }}][code]" value="{{ $row['code'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ $outcomeCodePrefix }}01" aria-label="Program learning outcome code" autocomplete="off" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="plos[{{ $index }}][title]" value="{{ $row['title'] ?? '' }}" class="form-control form-control-sm" placeholder="Program Outcome description" aria-label="Program learning outcome title" autocomplete="off" required>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="hidden" name="plos[{{ $index }}][is_active]" value="0">
                                                        <div class="form-check d-inline-flex justify-content-center">
                                                            <input class="form-check-input mt-0" type="checkbox" name="plos[{{ $index }}][is_active]" value="1" aria-label="Set PLO as active" {{ !empty($row['is_active']) ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" class="btn btn-outline-danger btn-sm remove-plo-row" title="Remove this PLO">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                    <small class="text-muted">Save definitions first before mapping newly added PLOs.</small>
                                    <button
                                        type="submit"
                                        class="btn btn-success rounded-pill px-4"
                                        id="ploDefinitionsSaveButton"
                                        data-save-scope="definitions"
                                        disabled
                                        aria-disabled="true"
                                        title="Make changes to enable saving."
                                    >
                                        Save Definitions
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div
                        class="tab-pane fade {{ $activePloTab === 'mapping' ? 'show active' : '' }} plo-config-pane plo-config-pane-mapping"
                        id="plo-mapping-panel"
                        role="tabpanel"
                        aria-labelledby="plo-mapping-tab"
                    >
                        <form method="POST" action="{{ route('chairperson.reports.co-program.plos.mappings.save') }}" class="h-100 d-flex flex-column" id="ploMappingForm">
                            @csrf

                            <div class="border rounded-4 bg-white p-3 p-lg-4 plo-mapping-card po-matrix-workspace">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1">CO to PLO Mapping</h5>
                                        <p class="text-muted mb-0">Check each CO that maps to a PLO. The system averages mapped COs per outcome.</p>
                                    </div>
                                    <span class="badge text-bg-secondary text-white rounded-pill px-3 py-2 plo-co-summary-badge"><i class="bi bi-list-check me-1"></i>{{ $availableCoSummary }}</span>
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
                                            Add or restore at least one program outcome in the PLO Definitions tab before mapping.
                                        </small>
                                    </div>
                                @else
                                    {{-- Toolbar --}}
                                    <div class="border rounded-3 bg-light p-3 mb-3" role="region" aria-label="Mapping filters">
                                        {{-- Hidden select keeps JS subject-filter state in sync with quick-jump buttons --}}
                                        <select id="poMatrixSubjectFilter" class="d-none" aria-hidden="true" tabindex="-1">
                                            <option value="">All subjects</option>
                                            @foreach($matrixSubjectOptions as $subjectOption)
                                                <option value="{{ strtolower($subjectOption['code']) }}">{{ $subjectOption['label'] }}</option>
                                            @endforeach
                                        </select>

                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                            <div class="input-group input-group-sm" style="max-width: 260px;">
                                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                                <input
                                                    type="search"
                                                    id="poMatrixSearch"
                                                    class="form-control form-control-sm"
                                                    placeholder="Search subject or CO..."
                                                    aria-label="Search course outcome rows"
                                                    autocomplete="off"
                                                >
                                            </div>

                                            <select id="poMatrixStateFilter" class="form-select form-select-sm" aria-label="Filter by row state" style="max-width: 140px;">
                                                <option value="all">All rows</option>
                                                <option value="mapped">Mapped</option>
                                                <option value="unmapped">Unmapped</option>
                                            </select>

                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="poMatrixClearFilters" title="Clear all filters">
                                                <i class="bi bi-x-lg me-1"></i>Clear
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm ms-auto po-matrix-expand-btn"
                                                id="poMatrixExpandToggle"
                                                data-ui="co-plo-expand-toggle"
                                                data-default-label="Expand table"
                                                data-expanded-label="Collapse table"
                                                aria-pressed="false"
                                                aria-label="Expand mapping table"
                                            >
                                                <i class="bi bi-arrows-angle-expand me-1" aria-hidden="true"></i>
                                                <span class="po-matrix-expand-label">Expand table</span>
                                            </button>
                                        </div>

                                        {{-- Quick jump --}}
                                        <div class="d-flex align-items-center flex-wrap gap-1 po-matrix-subject-jump" role="group" aria-label="Quick jump to subject rows" data-ui="co-plo-subject-jump">
                                            <span class="text-muted small fw-semibold me-1 po-matrix-subject-jump-label">
                                                <i class="bi bi-arrow-right-circle me-1"></i>Jump to:
                                            </span>
                                            <button
                                                type="button"
                                                class="btn btn-sm po-matrix-subject-jump-btn is-active"
                                                data-subject=""
                                                data-total-count="{{ count($availableCourseOutcomeRows) }}"
                                                aria-pressed="true"
                                            >
                                                <span class="po-matrix-subject-jump-text">All</span>
                                                <span class="po-matrix-subject-jump-count">{{ count($availableCourseOutcomeRows) }}</span>
                                            </button>
                                            @foreach($matrixSubjectOptions as $subjectOption)
                                                @php($subjectKey = strtolower((string) $subjectOption['code']))
                                                @php($subjectTotal = (int) ($matrixSubjectCounts[$subjectKey] ?? 0))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm po-matrix-subject-jump-btn"
                                                    data-subject="{{ strtolower($subjectOption['code']) }}"
                                                    data-total-count="{{ $subjectTotal }}"
                                                    aria-pressed="false"
                                                    title="{{ $subjectOption['label'] }}"
                                                >
                                                    <span class="po-matrix-subject-jump-text">{{ $subjectOption['code'] }}</span>
                                                    <span class="po-matrix-subject-jump-count">{{ $subjectTotal }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="table-responsive po-matrix-wrap" style="--po-matrix-outcome-count: {{ max(1, $visiblePloDefinitions->count()) }};" data-ui="co-plo-matrix-wrap">
                                        <table class="table table-bordered align-middle mb-0 po-matrix-table po-matrix-table--dense" aria-describedby="poMatrixLegend">
                                            <caption class="visually-hidden">Matrix mapping course outcome rows to program learning outcomes.</caption>
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-start po-matrix-sticky-col po-matrix-header-label">
                                                        <span>Course</span>
                                                    </th>
                                                    <th class="text-start po-matrix-sticky-col-secondary po-matrix-header-label">
                                                        <span>Course Outcome Row</span>
                                                    </th>
                                                    @foreach($visiblePloDefinitions as $plo)
                                                        <th class="text-center po-matrix-outcome-col" title="{{ $plo->title }}" data-plo-key="{{ strtolower($plo->plo_code) }}" data-ui="co-plo-column-header">
                                                            <span class="po-matrix-outcome-head">
                                                                <span class="po-matrix-code">{{ $plo->plo_code }}</span>
                                                            </span>
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
                                                            <div class="text-muted po-row-meta">{{ $row['co_code'] }} | Target {{ number_format((float) ($row['target_percentage'] ?? 75), 2) }}%</div>
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

                                    <div class="po-matrix-resize-control" data-ui="co-plo-resize-control" aria-label="Table resize controls">
                                        <div
                                            class="po-matrix-resize-handle"
                                            id="poMatrixResizeHandle"
                                            role="separator"
                                            aria-orientation="horizontal"
                                            aria-label="Drag to resize the mapping table"
                                            aria-valuemin="0"
                                            aria-valuemax="0"
                                            aria-valuenow="0"
                                            tabindex="0"
                                            title="Drag to resize the table"
                                        >
                                            <span class="po-matrix-resize-arrow" aria-hidden="true">
                                                <i class="bi bi-grip-horizontal" aria-hidden="true"></i>
                                            </span>
                                            <span class="po-matrix-resize-primary">Drag to resize</span>
                                            <span class="po-matrix-resize-value" id="poMatrixResizeValue" aria-hidden="true"></span>
                                        </div>
                                    </div>

                                    <div class="po-matrix-legend mt-3" id="poMatrixLegend">
                                        <div class="po-matrix-legend-title"><i class="bi bi-book me-2 text-success" aria-hidden="true"></i>PLO Reference</div>
                                        <div class="po-matrix-legend-subtitle">Column header reference while mapping.</div>
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
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>Clear any active filters before saving to avoid missing hidden rows.
                                        </small>
                                        <button
                                            type="submit"
                                            class="btn btn-success rounded-pill px-4 shadow-sm"
                                            id="poMatrixSaveBottom"
                                            data-save-scope="mapping"
                                            disabled
                                            aria-disabled="true"
                                            title="Make changes to enable saving."
                                        >
                                            <i class="bi bi-check2-circle me-1"></i>Save Mapping
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<template id="ploDefinitionRowTemplate">
    <tr class="plo-definition-row">
        <td>
            <input type="hidden" name="plos[__INDEX__][id]" value="">
            <input type="hidden" name="plos[__INDEX__][delete]" value="0" class="plo-delete-input">
            <input type="text" name="plos[__INDEX__][code]" value="__CODE__" class="form-control form-control-sm" placeholder="{{ $outcomeCodePrefix }}01" aria-label="Program learning outcome code" autocomplete="off" required>
        </td>
        <td>
            <input type="text" name="plos[__INDEX__][title]" value="Program Outcome __NUMBER__" class="form-control form-control-sm" placeholder="Program Outcome description" aria-label="Program learning outcome title" autocomplete="off" required>
        </td>
        <td class="text-center">
            <input type="hidden" name="plos[__INDEX__][is_active]" value="0">
            <div class="form-check d-inline-flex justify-content-center">
                <input class="form-check-input mt-0" type="checkbox" name="plos[__INDEX__][is_active]" value="1" aria-label="Set PLO as active" checked>
            </div>
        </td>
        <td class="text-end">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-danger btn-sm remove-plo-row" title="Remove this PLO">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
</template>

@endsection

