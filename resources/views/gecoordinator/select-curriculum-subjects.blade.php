@extends('layouts.app')

{{-- Styles: resources/css/gecoordinator/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-book-half text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Confirm Curriculum Courses</span>
    </h1>
    <p class="text-muted mb-4">Select and confirm courses from the curriculum to enable them for the current academic period</p>

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

    @if(Auth::user()->role === 4)
        <div class="alert alert-info mb-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Note:</strong> As a GE Coordinator, you can only import GE (General Education), PD (Professional Development), PE (Physical Education), RS (Religious Studies), and NSTP (National Service Training Program) courses. Other courses are managed by Department Chairpersons.
        </div>
    @endif

    {{-- Curriculum Dropdown with Load Button --}}
    <div class="mb-4">
        <label for="curriculumSelect" class="form-label fw-semibold">Select Curriculum</label>
        <div class="d-flex gap-3 align-items-center">
            <div class="flex-grow-1">
                <select id="curriculumSelect" class="form-select shadow-sm">
                    <option value="">-- Choose Curriculum --</option>
                    @foreach($curriculums as $curriculum)
                        <option value="{{ $curriculum->id }}">
                            {{ $curriculum->name }} ({{ $curriculum->course->course_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="loadSubjectsBtn" class="btn btn-success" disabled>
                <span id="loadBtnText"><i class="bi bi-arrow-repeat me-1"></i> Load Courses</span>
                <span id="loadBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    {{-- Course Selection Form --}}
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

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    <span id="selectedCount">0</span> course(s) selected
                </div>
                <button type="button" id="openConfirmModalBtn" class="btn btn-success shadow-sm" disabled>
                    <i class="bi bi-check-circle me-1"></i> Confirm Selected Courses
                </button>
            </div>
        </div>
    </form>
</div>

<x-modal.success id="confirmModal" title="Confirm Submission">
    Are you sure you want to confirm and save the selected courses for this curriculum?

    <x-slot:footer>
        <x-modal.actions secondary-text="">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
            <button type="button" id="submitConfirmBtn" class="btn btn-success">Yes, Confirm</button>
        </x-modal.actions>
    </x-slot:footer>
</x-modal.success>
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
