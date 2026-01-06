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

        {{-- Success/Error Messages via Notify --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    window.notify?.success(@json(session('success')));
                });
            </script>
        @endif
        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    window.notify?.error(@json(session('error')));
                });
            </script>
        @endif

        @if(Auth::user()->role === 1)
            <div class="alert alert-custom" role="alert">
                <i class="bi bi-info-circle-fill"></i>
                <div class="alert-custom-content">
                    <div class="alert-custom-title">Information</div>
                    <p class="alert-custom-text">GE, PD, PE, RS, and NSTP subjects are managed by the GE Coordinator and cannot be imported from this page.</p>
                </div>
            </div>
        @endif

        <!-- Curriculum Selection -->
        <div class="curriculum-select-section">
            <label for="curriculumSelect" class="form-label">
                <i class="bi bi-mortarboard me-2"></i>Select Curriculum
            </label>
            <div class="d-flex gap-3 align-items-center">
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
                <button id="loadSubjectsBtn" class="btn btn-success btn-load" disabled>
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
                    <button type="button" id="openConfirmModalBtn" class="btn btn-success btn-confirm" disabled>
                        <i class="bi bi-check-circle me-2"></i>Confirm Selected Courses
                    </button>
                </div>
            </div>
        </form>
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

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/shared/select-curriculum-subjects.js --}}
{{-- Pass PHP data to JavaScript --}}
<script>
    window.pageData = {
        currentSemester: @json($activePeriod?->semester ?? ''),
        userRole: @json($userRole)
    };
</script>
@endpush

