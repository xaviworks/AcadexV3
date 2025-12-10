@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css, resources/css/chairperson/select-curriculum.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        <!-- Page Title -->
        <div class="page-title">
            <h1>
                <i class="bi bi-file-earmark-arrow-up"></i>
                Import Courses
            </h1>
            <p class="page-subtitle">Select a curriculum and choose courses to import into the system</p>
        </div>

        @if(Auth::user()->role === 1)
            <div class="alert alert-custom" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> As a Chairperson, you cannot import GE (General Education), PD (Professional Development), PE (Physical Education), RS (Religious Studies), and NSTP (National Service Training Program) subjects. These subjects are managed by the GE Coordinator.
            </div>
        @endif

        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Curriculum Selection -->
            <div class="curriculum-select-section">
                <label for="curriculumSelect" class="form-label">
                    <i class="bi bi-mortarboard me-2"></i>Select Curriculum
                </label>
                <div class="d-flex gap-3 align-items-end">
                    <div class="flex-grow-1">
                        <select id="curriculumSelect" class="form-select">
                            <option value="">-- Choose a Curriculum --</option>
                            @foreach($curriculums as $curriculum)
                                <option value="{{ $curriculum->id }}">
                                    {{ $curriculum->name }} ({{ $curriculum->course->course_description }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button id="loadSubjectsBtn" class="btn btn-success btn-load d-none">
                        <span id="loadBtnText">
                            <i class="bi bi-arrow-repeat me-2"></i>Load Courses
                        </span>
                        <span id="loadBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <!-- Subject Selection Form -->
            <form method="POST" action="{{ route('curriculum.confirmSubjects') }}" id="confirmForm">
                @csrf
                <input type="hidden" name="curriculum_id" id="formCurriculumId">

                <div class="d-none" id="subjectsContainer">
                    <!-- Tabs and Select All Button -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <ul class="nav nav-tabs flex-grow-1" id="yearTabs" style="margin-bottom: 0;"></ul>
                        <button type="button" class="btn btn-select-all" id="selectAllBtn" data-selected="false">
                            <i class="bi bi-check2-square me-1"></i> Select All
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="subjectsTableBody"></div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <div class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <span id="selectedCount">0</span> course(s) selected
                        </div>
                        <button type="button" class="btn btn-success btn-confirm" data-bs-toggle="modal" data-bs-target="#confirmModal">
                            <i class="bi bi-check-circle me-2"></i>Confirm Selected Courses
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Submission</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to confirm and save the selected subjects for this curriculum?
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="submitConfirmBtn" class="btn btn-success">Yes, Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $activePeriod = \App\Models\AcademicPeriod::find(session('active_academic_period_id'));
    $userRole = Auth::user()->role;
@endphp

<script>
    const currentSemester = @json($activePeriod?->semester ?? '');
    const userRole = @json($userRole);
</script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const curriculumSelect = document.getElementById('curriculumSelect');
    const loadSubjectsBtn = document.getElementById('loadSubjectsBtn');
    const subjectsContainer = document.getElementById('subjectsContainer');
    const subjectsTableBody = document.getElementById('subjectsTableBody');
    const formCurriculumId = document.getElementById('formCurriculumId');
    const loadBtnText = document.getElementById('loadBtnText');
    const loadBtnSpinner = document.getElementById('loadBtnSpinner');
    const yearTabs = document.getElementById('yearTabs');
    const selectAllBtn = document.getElementById('selectAllBtn');

    curriculumSelect.addEventListener('change', function () {
        loadSubjectsBtn.classList.toggle('d-none', !this.value);
        subjectsContainer.classList.add('d-none');
        yearTabs.innerHTML = '';
        subjectsTableBody.innerHTML = '';
    });

    loadSubjectsBtn.addEventListener('click', function () {
        const curriculumId = curriculumSelect.value;
        if (!curriculumId) return;

        formCurriculumId.value = curriculumId;
        yearTabs.innerHTML = '';
        subjectsTableBody.innerHTML = '';
        loadSubjectsBtn.disabled = true;
        loadBtnText.classList.add('d-none');
        loadBtnSpinner.classList.remove('d-none');

        fetch(`/curriculum/${curriculumId}/fetch-subjects`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.length) {
                yearTabs.innerHTML = '';
                subjectsTableBody.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0">No courses found for this curriculum.</p>
                    </div>
                `;
                subjectsContainer.classList.remove('d-none');
                return;
            }

            const grouped = {};
            data.forEach(subj => {
                // Only include subjects for the current semester
                if (subj.semester !== currentSemester) return;

                const key = `year${subj.year_level}`;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(subj);
            });

            let tabIndex = 0;
            for (const [key, subjects] of Object.entries(grouped)) {
                const year = key.replace('year', '');
                const yearLabels = { '1': '1st Year', '2': '2nd Year', '3': '3rd Year', '4': '4th Year' };
                const isActive = tabIndex === 0 ? 'active' : '';

                yearTabs.insertAdjacentHTML('beforeend', `
                    <li class="nav-item">
                        <button class="nav-link ${isActive}" style="color: #198754; font-weight: 500;" data-bs-toggle="tab" data-bs-target="#tab-${key}" type="button" role="tab">${yearLabels[year]}</button>
                    </li>
                `);

                const rows = subjects.map(s => {
                    // For GE Coordinator, disable checkboxes for non-GE subjects
                    // For Chairperson, disable checkboxes for GE, PD, PE, RS, NSTP subjects
                    let isDisabled = false;
                    if (userRole === 4 && !s.is_universal) {
                        isDisabled = true; // GE Coordinator can only select GE subjects
                    } else if (userRole === 1 && s.is_restricted) {
                        isDisabled = true; // Chairperson cannot select restricted subjects (GE, PD, PE, RS, NSTP)
                    }
                    const disabledAttr = isDisabled ? 'disabled' : '';
                    const disabledClass = isDisabled ? 'opacity-50' : '';
                    
                    return `
                        <tr class="${disabledClass}">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input subject-checkbox" name="subject_ids[]" value="${s.id}" data-year="${s.year_level}" data-semester="${s.semester}" ${disabledAttr}>
                            </td>
                            <td><strong>${s.subject_code}</strong></td>
                            <td>${s.subject_description}</td>
                            <td class="text-center">${s.year_level}</td>
                            <td class="text-center">${s.semester}</td>
                        </tr>
                    `;
                }).join('');

                const table = `
                    <h6 class="semester-heading">
                        <i class="bi bi-calendar3 me-2"></i>${currentSemester} Semester
                    </h6>
                    <div class="table-container">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">Select</th>
                                    <th style="width: 150px;">Course Code</th>
                                    <th>Description</th>
                                    <th style="width: 100px;" class="text-center">Year</th>
                                    <th style="width: 120px;" class="text-center">Semester</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    </div>
                `;

                subjectsTableBody.insertAdjacentHTML('beforeend', `
                    <div class="tab-pane fade ${isActive ? 'show active' : ''}" id="tab-${key}" role="tabpanel">
                        ${table}
                    </div>
                `);

                tabIndex++;
            }

            subjectsContainer.classList.remove('d-none');
        })
        .catch(() => {
            subjectsTableBody.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    <p class="text-danger mb-0">Failed to load courses. Please try again.</p>
                </div>
            `;
            subjectsContainer.classList.remove('d-none');
        })
        .finally(() => {
            loadSubjectsBtn.disabled = false;
            loadBtnText.classList.remove('d-none');
            loadBtnSpinner.classList.add('d-none');
        });
    });

    // Select/Unselect All Handler
    document.addEventListener('click', function (e) {
        if (e.target.closest('#selectAllBtn')) {
            const btn = e.target.closest('#selectAllBtn');
            let allSelected = btn.dataset.selected === 'true';
            allSelected = !allSelected;
            btn.dataset.selected = allSelected;
            
            // For GE Coordinator and Chairperson, only select enabled checkboxes
            document.querySelectorAll('.subject-checkbox').forEach(cb => {
                if ((userRole === 4 && cb.disabled) || (userRole === 1 && cb.disabled)) {
                    cb.checked = false; // Keep disabled checkboxes unchecked
                } else {
                    cb.checked = allSelected;
                }
            });
            
            if (allSelected) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
            
            btn.innerHTML = allSelected
                ? '<i class="bi bi-x-square me-1"></i> Unselect All'
                : '<i class="bi bi-check2-square me-1"></i> Select All';
            
            updateSelectedCount();
        }
    });

    // Update selected count
    function updateSelectedCount() {
        const count = document.querySelectorAll('.subject-checkbox:checked').length;
        const countEl = document.getElementById('selectedCount');
        if (countEl) {
            countEl.textContent = count;
        }
    }

    // Listen for checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('subject-checkbox')) {
            updateSelectedCount();
        }
    });

    // Confirm Modal Submission
    document.getElementById('submitConfirmBtn')?.addEventListener('click', function () {
        document.getElementById('confirmForm')?.submit();
    });
});
</script>
@endpush

