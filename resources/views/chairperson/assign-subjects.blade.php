@php
    function ordinalSuffix($n) {
        $suffixes = ['th', 'st', 'nd', 'rd'];
        $remainder = $n % 100;
        return $n . ($suffixes[($remainder - 20) % 10] ?? $suffixes[$remainder] ?? $suffixes[0]);
    }
@endphp

@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        <!-- Page Title -->
        <div class="page-title">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1>
                        <i class="bi bi-person-badge"></i>
                        Assign Courses to Instructors
                    </h1>
                    <p class="page-subtitle">Assign subjects to instructors by year level or view all assignments</p>
                </div>
                <!-- View Mode Switcher -->
                <div class="d-flex align-items-center">
                    <label for="viewMode" class="me-2 fw-semibold">View Mode:</label>
                    <select id="viewMode" class="form-select form-select-sm w-auto" onchange="toggleViewMode()">
                        <option value="year" selected>Year View</option>
                        <option value="full">Full View</option>
                    </select>
                </div>
            </div>
        </div>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify?.success(@json(session('success')));
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify?.error(@json(session('error')));
            });
        </script>
    @endif

    <!-- YEAR VIEW (Tabbed) -->
    <div id="yearView">
        <!-- Year Level Tabs -->
        <ul class="nav nav-tabs" id="yearTabs" role="tablist">
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
                                    <th>Assigned Instructor</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach ($subjectsByYear as $subject)
                                        <tr>
                                            <td>{{ $subject->subject_code }}</td>
                                            <td>{{ $subject->subject_description }}</td>
                                            <td>{{ $subject->instructor ? $subject->instructor->name : '—' }}</td>
                                            <td class="text-center">
                                                @if ($subject->instructor)
                                                    <button
                                                        onclick="openConfirmUnassignModal({{ $subject->id }}, '{{ addslashes($subject->subject_code . ' - ' . $subject->subject_description) }}')"
                                                        class="btn btn-danger btn-sm">
                                                        <i class="bi bi-x-circle me-1"></i> Unassign
                                                    </button>
                                                @else
                                                    <button
                                                        onclick="openConfirmAssignModal({{ $subject->id }}, '{{ addslashes($subject->subject_code . ' - ' . $subject->subject_description) }}')"
                                                        class="btn btn-success shadow-sm btn-sm">
                                                        <i class="bi bi-person-plus me-1"></i> Assign
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="bg-warning bg-opacity-25 text-warning border border-warning px-4 py-3 rounded-4 shadow-sm">
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
                                                <th class="border-0 py-3">Assigned Instructor</th>
                                                <th class="border-0 py-3 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($subjectsByYear as $subject)
                                                <tr>
                                                    <td class="fw-medium">{{ $subject->subject_code }}</td>
                                                    <td>{{ $subject->subject_description }}</td>
                                                    <td>
                                                        @if($subject->instructor)
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi bi-person-check-fill text-success me-2"></i>
                                                                <span>{{ $subject->instructor->name }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($subject->instructor)
                                                            <button
                                                                onclick="openConfirmUnassignModal({{ $subject->id }}, '{{ addslashes($subject->subject_code . ' - ' . $subject->subject_description) }}')"
                                                                class="btn btn-outline-danger btn-sm" 
                                                                title="Unassign Instructor">
                                                                <i class="bi bi-x-circle me-1"></i> Unassign
                                                            </button>
                                                        @else
                                                            <button
                                                                onclick="openConfirmAssignModal({{ $subject->id }}, '{{ addslashes($subject->subject_code . ' - ' . $subject->subject_description) }}')"
                                                                class="btn btn-success shadow-sm btn-sm" 
                                                                title="Assign Instructor">
                                                                <i class="bi bi-person-plus me-1"></i> Assign
                                                            </button>
                                                        @endif
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

{{-- Confirm Unassign Modal --}}
<div id="confirmUnassignModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white w-full max-w-lg rounded-4 shadow-lg overflow-hidden flex flex-col">
        <div class="bg-danger text-white px-4 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Unassign
            </h5>
            <button onclick="closeConfirmUnassignModal()" class="btn-close btn-close-white" aria-label="Close"></button>
        </div>
        <div class="p-4">
            <p>Are you sure you want to unassign this subject? This action cannot be undone.</p>
            <form id="unassignForm" action="{{ route('chairperson.toggleAssignedSubject') }}" method="POST">
                @csrf
                <input type="hidden" name="subject_id" id="unassign_subject_id">
                <input type="hidden" name="instructor_id" value="">
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Unassign
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmUnassignModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirm Assign Modal --}}
<div id="confirmAssignModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white w-full max-w-lg rounded-4 shadow-lg overflow-hidden flex flex-col">
        <div class="bg-success text-white px-4 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-check-circle-fill me-2"></i> Confirm Assign
            </h5>
            <button onclick="closeConfirmAssignModal()" class="btn-close btn-close-white" aria-label="Close"></button>
        </div>
        <div class="p-4">
            <p>Select the instructor to assign this subject to:</p>
            <form id="assignForm" method="POST" action="{{ route('chairperson.storeAssignedSubject') }}" class="vstack gap-3">
                @csrf
                <input type="hidden" name="subject_id" id="assign_subject_id">
                <div>
                    <label class="form-label">Instructor</label>
                    <select name="instructor_id" class="form-select" required>
                        <option value="">-- Choose Instructor --</option>
                        @foreach ($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Assign
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmAssignModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Styles: resources/css/chairperson/common.css --}}
{{-- JavaScript: resources/js/pages/chairperson/assign-subjects.js --}}

    </div>
</div>
@endsection