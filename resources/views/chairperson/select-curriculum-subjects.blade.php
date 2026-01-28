@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css, resources/css/chairperson/select-curriculum.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-file-earmark-arrow-up text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Import Courses</span>
    </h1>
    <p class="text-muted mb-4">Select a curriculum and choose courses to import into the system</p>

    {{-- Toast Notifications --}}
    @include('chairperson.partials.toast-notifications')

        @if(Auth::user()->role === 1)
            <div class="alert alert-custom" role="alert">
                <i class="bi bi-info-circle-fill"></i>
                <div class="alert-custom-content">
                    <div class="alert-custom-title">Information</div>
                    <p class="alert-custom-text">GE, PD, PE, RS, and NSTP subjects are managed by the GE Coordinator and cannot be imported from this page.</p>
                </div>
            </div>
        @endif

        {{-- Curriculum Selection --}}
        <div class="curriculum-select-section">
            <label for="curriculumSelect" class="form-label">
                <i class="bi bi-mortarboard me-2"></i>Select Curriculum
            </label>
            <div class="position-relative">
                <select id="curriculumSelect" class="form-select">
                    <option value="">-- Choose a Curriculum --</option>
                    @foreach($curriculums as $curriculum)
                        <option value="{{ $curriculum->id }}">
                            {{ $curriculum->name }} ({{ $curriculum->course->course_description }})
                        </option>
                    @endforeach
                </select>
                <div id="loadBtnSpinner" class="position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                    <span class="spinner-border spinner-border-sm text-success" role="status" aria-hidden="true"></span>
                </div>
            </div>
        </div>

        <!-- Subject Selection Form -->
        <form method="POST" action="{{ route('curriculum.confirmSubjects') }}" id="confirmForm">
            @csrf
            <input type="hidden" name="curriculum_id" id="formCurriculumId">

            <div class="d-none" id="subjectsContainer">
                <!-- Tabs -->
                <div class="mb-3">
                    <ul class="nav nav-tabs" id="yearTabs" style="margin-bottom: 0;"></ul>
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

</div>

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

@endsection