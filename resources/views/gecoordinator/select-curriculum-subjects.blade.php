@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <h1 class="text-2xl font-bold mb-4">
        <i class="bi bi-book-half text-success me-2"></i>
        Confirm Curriculum Subjects
    </h1>

    @if(Auth::user()->role === 4)
        <div class="alert alert-info mb-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Note:</strong> As a GE Coordinator, you can only import GE (General Education), PD (Professional Development), PE (Physical Education), RS (Religious Studies), and NSTP (National Service Training Program) subjects. Other subjects are managed by Department Chairpersons.
        </div>
    @endif

    {{-- Curriculum Dropdown --}}
    <div class="mb-4">
        <label for="curriculumSelect" class="form-label fw-semibold">Select Curriculum</label>
        <select id="curriculumSelect" class="form-select shadow-sm">
            <option value="">-- Choose Curriculum --</option>
            @foreach($curriculums as $curriculum)
                <option value="{{ $curriculum->id }}">
                    {{ $curriculum->name }} ({{ $curriculum->course->course_code }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- Load Button --}}
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <button id="loadSubjectsBtn" class="btn btn-success d-none">
            <span id="loadBtnText"><i class="bi bi-arrow-repeat me-1"></i> Load Subjects</span>
            <span id="loadBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </div>

    {{-- Subject Selection Form --}}
    <form method="POST" action="{{ route('curriculum.confirmSubjects') }}" id="confirmForm">
        @csrf
        <input type="hidden" name="curriculum_id" id="formCurriculumId">

        <div class="table-responsive d-none" id="subjectsContainer">
            {{-- Tabs for Year Levels --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <ul class="nav nav-tabs" id="yearTabs" style="margin-bottom: 0;"></ul>
                <button type="button" class="btn btn-success btn-sm" id="selectAllBtn" data-selected="false">
                    <i class="bi bi-check2-square me-1"></i> Select All
                </button>
            </div>

            <div class="tab-content mt-3" id="subjectsTableBody"></div>

            <div class="text-end mt-3">
                <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmModal">
                    <i class="bi bi-check-circle me-1"></i> Confirm Selected Subjects
                </button>
            </div>
        </div>
    </form>

    {{-- Toast Notification --}}
    @if(session('success'))
        <script>notify.success('{{ session('success') }}');</script>
    @endif
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
<script>
    window.pageData = {
        currentSemester: @json($activePeriod?->semester ?? ''),
        userRole: @json($userRole)
    };
</script>
@endpush

