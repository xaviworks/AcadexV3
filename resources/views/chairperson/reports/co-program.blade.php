@extends('layouts.app')

@section('content')
@php
    $definitionRows = old('plos');
    if ($definitionRows === null) {
        $definitionRows = collect($ploDefinitions ?? [])->map(function ($plo) {
            return [
                'id' => $plo->id,
                'code' => $plo->plo_code,
                'title' => $plo->title,
                'is_active' => $plo->is_active,
                'delete' => false,
            ];
        })->values()->all();
    }

    $selectedMappings = old('mappings', $ploMappings ?? []);
    $shouldOpenPloModal = session('openPloModal') || $errors->any();
    $availableCoSummary = match (count($availableCoCodes ?? [])) {
        0 => 'No CO slots yet',
        1 => $availableCoCodes[0],
        2 => implode(', ', $availableCoCodes),
        default => ($availableCoCodes[0] ?? 'CO1') . ' ... ' . ($availableCoCodes[count($availableCoCodes) - 1] ?? 'CO6'),
    };
    $activePloTab = session('ploTab', 'definitions');
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
                                                <span class="badge {{ $toneClass }} px-3 py-2 rounded-pill">
                                                    {{ number_format((float) $value['percent'], 2) }}%
                                                </span>
                                                <div class="mt-2">
                                                    <small class="text-muted d-block">
                                                        {{ implode(', ', $value['co_codes']) }}
                                                    </small>
                                                    <small class="text-muted">target {{ number_format($threshold, 2) }}%</small>
                                                    @if(!empty($level['label']))
                                                        <small class="text-muted d-block">{{ $level['label'] }}</small>
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
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header bg-success text-white">
                <div>
                    <h4 class="modal-title fw-bold" id="configurePloModalLabel">Configure Program Learning Outcomes</h4>
                    <p class="mb-0 text-white-50">Set up the PLOs for <span class="fw-semibold text-white">{{ $program->course_code }}</span> and choose which CO slots contribute to each one.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border rounded-4 mb-4">
                    <div class="fw-semibold text-dark mb-1">How this works</div>
                    <small class="text-muted">First define the PLOs you want to show in the summary table, then link the COs for this program. Acadex automatically computes each PLO by averaging only the COs linked to that PLO.</small>
                </div>

                <ul class="nav nav-tabs mb-0" id="ploConfigTabs" role="tablist" style="background: transparent; border-bottom: 2px solid #dee2e6;">
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

                            <div class="border rounded-4 bg-white p-3">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1">PLO Definitions</h5>
                                        <p class="text-muted mb-0">Keep the codes short and use titles that are easy to recognize in the report.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-success rounded-pill" id="addPloRowButton">
                                        <i class="bi bi-plus-circle me-2"></i>Add PLO
                                    </button>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge text-bg-light rounded-pill px-3 py-2">Default: PLO1 to PLO5</span>
                                    <span class="badge text-bg-light rounded-pill px-3 py-2">Maximum: 20 PLOs</span>
                                    <span class="badge text-bg-light rounded-pill px-3 py-2">Inactive PLOs are hidden from the table</span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 120px;">Code</th>
                                                <th>Title</th>
                                                <th style="width: 120px;">Active</th>
                                                <th style="width: 90px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="ploDefinitionRows">
                                            @foreach($definitionRows as $index => $row)
                                                @php($isDeleted = filter_var($row['delete'] ?? false, FILTER_VALIDATE_BOOL))
                                                <tr class="plo-definition-row {{ $isDeleted ? 'd-none' : '' }}">
                                                    <td>
                                                        <input type="hidden" name="plos[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                                                        <input type="hidden" name="plos[{{ $index }}][delete]" value="{{ $isDeleted ? 1 : 0 }}" class="plo-delete-input">
                                                        <input type="text" name="plos[{{ $index }}][code]" value="{{ $row['code'] ?? '' }}" class="form-control" placeholder="PLO1" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="plos[{{ $index }}][title]" value="{{ $row['title'] ?? '' }}" class="form-control" placeholder="Program Learning Outcome title" required>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="hidden" name="plos[{{ $index }}][is_active]" value="0">
                                                        <input class="form-check-input mt-0" type="checkbox" name="plos[{{ $index }}][is_active]" value="1" {{ !empty($row['is_active']) ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-plo-row">
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
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

                            <div class="border rounded-4 bg-white p-3">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1">CO to PLO Mapping</h5>
                                        <p class="text-muted mb-0">Choose which COs belong to each PLO. The system will automatically average the COs you select for that PLO.</p>
                                    </div>
                                    <div class="badge text-bg-light rounded-pill px-3 py-2 plo-co-summary-badge">Available COs: {{ $availableCoSummary }}</div>
                                </div>

                                @if(empty($availableCoCodes))
                                    <div class="alert alert-light border rounded-4 mb-0">
                                        <div class="fw-semibold text-dark mb-1">No course outcomes available yet</div>
                                        <small class="text-muted">
                                            This program has no COs configured for the current academic period yet. Set up Course Outcomes first, then return here to link them to PLOs.
                                        </small>
                                    </div>
                                @else
                                    <div class="plo-mapping-toolbar mb-3">
                                        <small class="text-muted">Click the CO chips you want to include for each PLO.</small>
                                    </div>

                                    <div class="plo-mapping-grid plo-mapping-grid-compact">
                                        @foreach($ploDefinitions as $plo)
                                            @if(!$plo->is_deleted)
                                                @php($checkedMappings = $selectedMappings[$plo->id] ?? [])
                                                <div class="plo-mapping-card {{ $plo->is_active ? '' : 'plo-mapping-card--inactive' }}">
                                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                                        <div class="plo-mapping-summary">
                                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                                <span class="fw-bold text-dark fs-5">{{ $plo->plo_code }}</span>
                                                                <span class="badge {{ $plo->is_active ? 'text-bg-success' : 'text-bg-secondary' }} rounded-pill">
                                                                    {{ $plo->is_active ? 'Visible' : 'Hidden' }}
                                                                </span>
                                                                <span class="badge text-bg-light rounded-pill">
                                                                    {{ count($checkedMappings) > 0 ? 'Linked' : 'Not linked' }}
                                                                </span>
                                                            </div>
                                                            <div class="text-muted plo-mapping-title">{{ \Illuminate\Support\Str::limit($plo->title, 70) }}</div>
                                                        </div>

                                                        <div class="d-flex flex-wrap gap-1 justify-content-lg-end plo-mapping-chip-group">
                                                            @foreach($availableCoCodes as $coCode)
                                                                <label class="plo-co-chip {{ in_array($coCode, $checkedMappings, true) ? 'is-selected' : '' }}">
                                                                    <input
                                                                        class="d-none plo-co-chip-input"
                                                                        type="checkbox"
                                                                        name="mappings[{{ $plo->id }}][]"
                                                                        value="{{ $coCode }}"
                                                                        {{ in_array($coCode, $checkedMappings, true) ? 'checked' : '' }}
                                                                    >
                                                                    <span>{{ $coCode }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
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
            <input type="text" name="plos[__INDEX__][code]" value="PLO__NUMBER__" class="form-control" placeholder="PLO1" required>
        </td>
        <td>
            <input type="text" name="plos[__INDEX__][title]" value="Program Learning Outcome __NUMBER__" class="form-control" placeholder="Program Learning Outcome title" required>
        </td>
        <td class="text-center">
            <input type="hidden" name="plos[__INDEX__][is_active]" value="0">
            <input class="form-check-input mt-0" type="checkbox" name="plos[__INDEX__][is_active]" value="1" checked>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-plo-row">
                Remove
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

    const getUsedNumbers = () => getVisibleRows()
        .map((row) => row.querySelector('input[name$="[code]"]'))
        .filter(Boolean)
        .map((input) => {
            const match = (input.value || '').toUpperCase().match(/^PLO(\d+)$/);
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
        const html = template.innerHTML
            .replaceAll('__INDEX__', String(index))
            .replaceAll('__NUMBER__', String(nextNumber));

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

    document.querySelectorAll('.plo-co-chip-input').forEach((input) => {
        input.addEventListener('change', function () {
            const chip = input.closest('.plo-co-chip');
            if (!chip) {
                return;
            }

            chip.classList.toggle('is-selected', input.checked);
        });
    });

    refreshAddButtonState();
});
</script>
@endpush
@endsection
