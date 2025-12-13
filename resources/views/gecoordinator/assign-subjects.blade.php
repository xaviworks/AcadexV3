@php
    function ordinalSuffix($n) {
        $suffixes = ['th', 'st', 'nd', 'rd'];
        $remainder = $n % 100;
        return $n . ($suffixes[($remainder - 20) % 10] ?? $suffixes[$remainder] ?? $suffixes[0]);
    }
@endphp

@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/gecoordinator/common.css --}}

<div class="page-wrapper">
    <div class="page-container">
        <!-- Page Title -->
        <div class="page-title">
            <h1 class="text-3xl font-bold mb-2 text-gray-800 flex items-center">
                <i class="bi bi-journal-plus text-success me-3 fs-2"></i>
                Manage Courses
            </h1>
            <p class="text-muted mb-0 small">
                <i class="bi bi-info-circle me-1"></i>
                View and manage instructor assignments for each course. Click the "Edit" button to add or remove instructors.
            </p>
        </div>

        {{-- Flash messages (server-side) will be displayed as toasts on load via JS --}}

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 0.5rem;">
                <!-- Year Level Tabs -->
                <ul class="nav nav-tabs mb-0" id="yearTabs" role="tablist" style="border-bottom: none !important;">
                    @for ($level = 1; $level <= 4; $level++)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $level === 1 ? 'active' : '' }}"
                               id="year-level-{{ $level }}"
                               data-bs-toggle="tab"
                               href="#level-{{ $level }}"
                               role="tab"
                               aria-controls="level-{{ $level }}"
                               aria-selected="{{ $level === 1 ? 'true' : 'false' }}">
                               {{ ordinalSuffix($level) }} Year
                            </a>
                        </li>
                    @endfor
                </ul>
                
                <!-- View Mode Switcher -->
                <div class="d-flex align-items-center">
                    <label for="viewMode" class="me-2 fw-semibold">
                        <i class="bi bi-eye me-1"></i>View Mode:
                    </label>
                    <select id="viewMode" class="form-select form-select-sm w-auto" onchange="toggleViewMode()"
                            data-bs-toggle="tooltip" title="Year View: See subjects by year level. Full View: See all subjects at once.">
                        <option value="year" selected>Year View</option>
                        <option value="full">Full View</option>
                    </select>
                </div>
            </div>

            <!-- YEAR VIEW (Tabbed) -->
            <div id="yearView">
        <div class="tab-content" id="yearTabsContent">
            @for ($level = 1; $level <= 4; $level++)
                @php
                    $subjectsByYear = $yearLevels[$level] ?? collect();
                @endphp

                <div class="tab-pane fade {{ $level === 1 ? 'show active' : '' }}"
                     id="level-{{ $level }}"
                     role="tabpanel"
                     aria-labelledby="year-level-{{ $level }}">
                        @if ($subjectsByYear->isNotEmpty())
                            <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                                <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Description</th>
                                        <th class="text-center">Assigned Instructor</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subjectsByYear as $subject)
                                        <tr>
                                            <td>{{ $subject->subject_code }}</td>
                                            <td>{{ $subject->subject_description }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-success subject-view-btn" 
                                                    data-subject-id="{{ $subject->id }}"
                                                    onclick="openViewInstructorsModal({{ $subject->id }}, {{ json_encode($subject->subject_code . ' - ' . $subject->subject_description) }})">
                                                    <i class="bi bi-people-fill text-success me-1"></i>
                                                    <span>View (<span class="view-count">{{ $subject->instructors_count ?? $subject->instructors->count() }}</span>)</span>
                                                </button>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    <button class="btn btn-sm btn-success subject-edit-btn"
                                                        data-subject-id="{{ $subject->id }}"
                                                        onclick="openInstructorListModal({{ $subject->id }}, {{ json_encode($subject->subject_code) }}, 'edit')"
                                                        title="Edit Instructors">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        @else
                            <div class="alert alert-warning shadow-sm rounded">
                                No subjects available for {{ ordinalSuffix($level) }} Year.
                            </div>
                        @endif
                </div>
            @endfor
        </div>
    </div>

    <!-- FULL VIEW (All Years) -->
    <div id="fullView" class="d-none">
        <div class="row g-4">
            @for ($level = 1; $level <= 4; $level++)
                @php
                    $subjectsByYear = $yearLevels[$level] ?? collect();
                @endphp
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-transparent border-0 py-3">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0 fw-semibold text-success">
                                    {{ ordinalSuffix($level) }} Year
                                </h5>
                                <span class="badge bg-success-subtle text-success ms-3">
                                    {{ $subjectsByYear->count() }} {{ Str::plural('subject', $subjectsByYear->count()) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if ($subjectsByYear->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th class="border-0 py-3">Course Code</th>
                                                <th class="border-0 py-3">Description</th>
                                                <th class="border-0 py-3 text-center">Assigned Instructors</th>
                                                <th class="border-0 py-3 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($subjectsByYear as $subject)
                                                <tr>
                                                    <td class="fw-medium">{{ $subject->subject_code }}</td>
                                                    <td>{{ $subject->subject_description }}</td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-success subject-view-btn" 
                                                            data-subject-id="{{ $subject->id }}"
                                                            onclick="openViewInstructorsModal({{ $subject->id }}, {{ json_encode($subject->subject_code . ' - ' . $subject->subject_description) }})">
                                                            <i class="bi bi-people-fill text-success me-1"></i>
                                                            <span>View (<span class="view-count">{{ $subject->instructors_count ?? $subject->instructors->count() }}</span>)</span>
                                                        </button>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center">
                                                            <button
                                                                class="btn btn-success btn-sm subject-edit-btn"
                                                                data-subject-id="{{ $subject->id }}"
                                                                onclick="openInstructorListModal({{ $subject->id }}, {{ json_encode($subject->subject_code . ' - ' . $subject->subject_description) }}, 'edit')"
                                                                title="Edit Instructors">
                                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-journal-x display-6"></i>
                                    </div>
                                    <p class="text-muted mb-0">No subjects available for {{ ordinalSuffix($level) }} Year.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endfor
        </div>
        </div>
    </div>
</div>

<!-- Confirm Bulk Assign Modal -->
<div class="modal fade" id="confirmBulkAssignModal" tabindex="-1" aria-labelledby="confirmBulkAssignModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title d-flex align-items-center" id="confirmBulkAssignModalLabel">
                    <i class="bi bi-file-earmark-check text-primary me-2 fs-4"></i>
                    <span>Confirm Assign</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small mb-1">Target Subject</label>
                    <div class="fw-semibold" id="assignTargetSubject">Loading...</div>
                </div>
                
                <div class="mb-3">
                    <div id="assignSelectionCount" class="text-muted small"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small mb-2">Selected Instructors</label>
                    <div id="assignList" class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                        <!-- Instructors will be listed here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmBulkAssignBtn">
                    <i class="bi bi-check-circle me-1"></i> Confirm Assign
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Assigned Instructors Modal (Read-Only) -->
<div class="modal fade" id="viewInstructorsModal" tabindex="-1" aria-labelledby="viewInstructorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title d-flex align-items-center" id="viewInstructorsModalLabel">
                    <i class="bi bi-people-fill text-success me-2 fs-4"></i>
                    <span>Assigned Instructors</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small mb-1">Subject</label>
                    <div class="fw-semibold" id="viewSubjectName">Loading...</div>
                </div>
                
                <div class="mb-3">
                    <div id="viewInstructorCount" class="text-muted small"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small mb-2">Instructors</label>
                    <div id="viewInstructorList" class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background-color: #f8f9fa;">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2 small">Loading instructors...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Instructor List Modal - Split Pane Design --}}
<div class="modal fade" id="instructorListModal" tabindex="-1" aria-labelledby="instructorListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-success text-white d-flex align-items-start">
                <div class="flex-grow-1 d-flex align-items-center">
                    <h5 class="modal-title mb-1 d-flex align-items-center" id="instructorListModalLabel">
                        <i class="bi bi-people-fill me-2"></i>
                        <span id="instructorListModalTitle">Manage Instructors</span>
                        <span id="instructorListSubjectName" class="ms-2 fw-semibold" style="font-size: 1.1rem;"></span>
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs px-3 pt-3 mb-0 bg-light border-bottom-0" role="tablist" style="margin-bottom: 0 !important;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="assign-tab" data-bs-toggle="tab" data-bs-target="#assignPanel" type="button" role="tab" aria-controls="assignPanel" aria-selected="true">
                            <i class="bi bi-person-plus-fill me-2"></i>Assign Instructors
                            <span class="badge bg-primary ms-2" id="assignTabCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="unassign-tab" data-bs-toggle="tab" data-bs-target="#unassignPanel" type="button" role="tab" aria-controls="unassignPanel" aria-selected="false">
                            <i class="bi bi-person-dash-fill me-2"></i>Unassign Instructors
                            <span class="badge bg-success ms-2" id="unassignTabCount">0</span>
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Assign Tab Panel -->
                    <div class="tab-pane fade show active" id="assignPanel" role="tabpanel" aria-labelledby="assign-tab">
                        <div class="p-3 bg-light border-bottom">
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <div class="input-group input-group-sm" style="max-width: 250px;">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="searchAvailableTab" 
                                           placeholder="Search instructors..."
                                           data-bs-toggle="tooltip" title="Search by instructor name">
                                </div>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Sort toggle">
                                    <button type="button" class="btn btn-outline-secondary" id="sortAvailableToggleTab" data-sort="asc"
                                            data-bs-toggle="tooltip" title="Sort A to Z">
                                        <i class="bi bi-sort-alpha-down"></i>
                                    </button>
                                </div>
                                <button class="btn btn-success ms-auto" id="assignSelectedBtnTab" disabled
                                        data-bs-toggle="tooltip" title="Check boxes first, then click to add selected instructors"
                                        style="padding: 0.5rem 1.5rem; font-size: 1rem;">
                                    <i class="bi bi-person-plus me-1"></i>Add Selected
                                </button>
                            </div>
                        </div>
                        <div class="p-4" style="max-height: 350px; overflow-y: auto;" id="availableInstructorsListTab">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading available instructors...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unassign Tab Panel -->
                    <div class="tab-pane fade" id="unassignPanel" role="tabpanel" aria-labelledby="unassign-tab">
                        <div class="p-3 bg-light border-bottom">
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <div class="input-group input-group-sm" style="max-width: 250px;">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="searchAssignedTab" 
                                           placeholder="Search instructors..."
                                           data-bs-toggle="tooltip" title="Search by instructor name">
                                </div>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Sort toggle">
                                    <button type="button" class="btn btn-outline-secondary" id="sortAssignedToggleTab" data-sort="asc"
                                            data-bs-toggle="tooltip" title="Sort A to Z">
                                        <i class="bi bi-sort-alpha-down"></i>
                                    </button>
                                </div>
                                <button class="btn btn-outline-danger ms-auto" id="unassignSelectedBtnTab" disabled
                                        data-bs-toggle="tooltip" title="Check boxes first, then click to remove selected instructors"
                                        style="padding: 0.5rem 1.5rem; font-size: 1rem;">
                                    <i class="bi bi-person-dash me-1"></i>Remove Selected
                                </button>
                            </div>
                        </div>
                        <div class="p-4" style="max-height: 350px; overflow-y: auto;" id="assignedInstructorsListTab">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading assigned instructors...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden original split-pane (keeping for backwards compatibility) -->
                <div class="row g-0 d-none" id="splitPaneView">
                    <!-- Left Panel: Assigned Instructors -->
                    <div class="col-md-6 border-end">
                        <div class="p-3 bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-semibold text-success">
                                    <i class="bi bi-person-check-fill me-2"></i>Assigned Instructors
                                </h6>
                                <!-- Info moved to modal header; tooltip removed from per-panel header -->
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchAssigned" 
                                       placeholder="Type to search instructors..."
                                       data-bs-toggle="tooltip" title="Search by instructor name">
                            </div>
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm" id="sortAssigned"
                                        data-bs-toggle="tooltip" title="Sort the list of instructors">
                                    <option value="name-asc">📊 Name (A-Z)</option>
                                    <option value="name-desc">📊 Name (Z-A)</option>
                                </select>
                                <button class="btn btn-sm btn-outline-danger" id="unassignSelectedBtn" disabled
                                        data-bs-toggle="tooltip" title="Check boxes first, then click to remove selected instructors">
                                    <i class="bi bi-person-dash me-1"></i>Remove
                                </button>
                            </div>
                        </div>
                        <div class="p-3" style="max-height: 400px; overflow-y: auto;" id="assignedInstructorsList">
                            <!-- Quick Help Banner -->
                            <div class="alert alert-info alert-dismissible fade show mb-3" role="alert" id="helpBanner">
                                <strong><i class="bi bi-lightbulb-fill me-1"></i> How it works:</strong>
                                <ul class="small mb-0 mt-1">
                                    <li>Hover over a name to see quick action buttons</li>
                                    <li>Check boxes to select multiple instructors</li>
                                    <li>Use search to find specific instructors quickly</li>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="small mt-2 mb-0">Loading assigned instructors...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel: Available Instructors -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="bi bi-person-plus-fill me-2"></i>Available Instructors
                                </h6>
                                <!-- Info moved to modal header; tooltip removed from per-panel header -->
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchAvailable" 
                                       placeholder="Type to search instructors..."
                                       data-bs-toggle="tooltip" title="Search by instructor name">
                            </div>
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm" id="sortAvailable"
                                        data-bs-toggle="tooltip" title="Sort the list of instructors">
                                    <option value="name-asc">📊 Name (A-Z)</option>
                                    <option value="name-desc">📊 Name (Z-A)</option>
                                </select>
                                <button class="btn btn-sm btn-success" id="assignSelectedBtn" disabled
                                        data-bs-toggle="tooltip" title="Check boxes first, then click to add selected instructors">
                                    <i class="bi bi-person-plus me-1"></i>Add
                                </button>
                            </div>
                        </div>
                        <div class="p-3" style="max-height: 400px; overflow-y: auto;" id="availableInstructorsList">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="small mt-2 mb-0">Loading available instructors...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <div class="text-muted small me-auto">
                    <i class="bi bi-info-circle me-1"></i>Select instructors using checkboxes for bulk operations
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- Global Toast Container for top-right floating messages -->
        <div id="globalToastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
            <!-- Toasts will be dynamically injected here -->
        </div>
    </div>
</div>

{{-- Confirm Assign Modal --}}
<div class="modal fade" id="confirmAssignModal" tabindex="-1" aria-labelledby="confirmAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-success text-white d-flex align-items-start">
                <div>
                    <h5 class="modal-title mb-1" id="confirmAssignModalLabel">
                        <i class="bi bi-plus-circle-dotted me-2"></i> Assign Instructor
                    </h5>
                    <div class="small text-white-50" id="assignSubjectNameSmall"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Assigning instructor to: <span id="assignSubjectName" class="fw-semibold"></span></p>
                <p class="small text-muted mb-2">Select an instructor to assign for the active academic period.</p>
                <form id="assignInstructorForm" class="vstack gap-3">
                    @csrf
                    <input type="hidden" name="subject_id" id="assign_subject_id">
                    <div>
                        <label for="instructor_select" class="form-label">Select Instructor</label>
                        <select id="instructor_select" name="instructor_id" class="form-select" required>
                            <option value="">-- Choose Instructor --</option>
                            @foreach ($instructors as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="assignSubjectSubmit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Assign
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light d-none">
                <!-- Buttons moved inside form -->
            </div>
        </div>
    </div>
</div>

<!-- Confirm Unassign Modal -->
<div class="modal fade" id="confirmUnassignModal" tabindex="-1" aria-labelledby="confirmUnassignModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title d-flex align-items-center" id="confirmUnassignModalLabel">
                    <i class="bi bi-file-earmark-x text-danger me-2 fs-4"></i>
                    <span>Confirm Unassign</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small mb-1">Target Subject</label>
                    <div class="fw-semibold" id="unassignTargetSubject">Loading...</div>
                </div>
                
                <div class="mb-3">
                    <div id="unassignSelectionCount" class="text-muted small"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small mb-2">Selected Instructors</label>
                    <div id="unassignList" class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                        <!-- Instructors will be listed here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmUnassignBtn">
                    <i class="bi bi-trash me-1"></i> Confirm Unassign
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/gecoordinator/assign-subjects.js --}}
<script>
    // Provide endpoints to the page script so clicks hit the correct routes
    window.pageData = Object.assign({}, window.pageData, {
        assignInstructorUrl: "{{ route('gecoordinator.assignInstructor') }}",
        unassignInstructorUrl: "{{ route('gecoordinator.unassignInstructor') }}",
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Server-side flash messages
        @if (session('success'))
            window.notify?.success(@json(session('success')));
        @endif
        @if (session('error'))
            window.notify?.error(@json(session('error')));
        @endif

        // Initialize Bootstrap tooltips
        if (window.bootstrap?.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>
@endpush
{{-- Styles: resources/css/gecoordinator/common.css --}}
@endsection
