@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-bar-chart-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Students' Final Grades</span>
    </h1>
    <p class="text-muted mb-4">Select an instructor and subject to view students's final grades</p>

    {{-- Breadcrumb Navigation --}}
    @php
        $breadcrumbItems = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Select Instructor', 'url' => route('chairperson.viewGrades')]
        ];
        
        if (!empty($selectedInstructorId) && empty($selectedSubjectId)) {
            $breadcrumbItems[] = ['label' => 'Select Subject'];
        } elseif (!empty($selectedInstructorId) && !empty($selectedSubjectId)) {
            $breadcrumbItems[] = ['label' => 'Students\' Final Grades'];
        }
    @endphp
    <x-breadcrumbs :items="$breadcrumbItems" />

    {{-- Step 1: Instructor Selection --}}
    @if (empty($selectedInstructorId) && empty($selectedSubjectId))
        @if (count($instructors) > 0)
            <div class="row g-4 px-4 py-4">
                @foreach($instructors as $instructor)
                    <div class="col-md-4">
                        <div
                            class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden cursor-pointer transition-transform-shadow"
                            data-url="{{ route('chairperson.viewGrades', ['instructor_id' => $instructor->id]) }}"
                            onclick="window.location.href='{{ route('chairperson.viewGrades', ['instructor_id' => $instructor->id]) }}'"
                            style="cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease;"
                        >
                            {{-- Top header --}}
                            <div class="position-relative" style="height: 80px; background-color: #4ecd85;">
                                <div class="subject-circle wildcard-circle-positioned">
                                    {{-- Person Icon for Instructor (Square Design) --}}
                                    <i class="bi bi-person-circle text-white" style="font-size: 40px;"></i>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="card-body pt-5 text-center">
                                <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $instructor->last_name }}, {{ $instructor->first_name }}">
                                    {{ $instructor->last_name }}, {{ $instructor->first_name }}
                                </h6>
                                {{-- Badge for role --}}
                                <div class="mt-2">
                                    <span class="badge bg-primary text-white">Instructor</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state
                icon="bi-people"
                title="No Instructors Available"
                message="There are no instructors in your department or program yet."
            />
        @endif
    @elseif (empty($selectedSubjectId))
        {{-- Step 2: Subject Selection --}}
        @if (count($subjects) > 0)
            <div class="row g-4 px-4 py-4" id="subject-selection">
                @foreach($subjects as $subjectItem)
                    <div class="col-md-4">
                        <div
                            class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden transform transition hover:scale-105 hover:shadow-xl"
                            data-url="{{ route('chairperson.viewGrades', ['instructor_id' => $selectedInstructorId, 'subject_id' => $subjectItem->id]) }}"
                            style="cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease;"
                            onclick="window.location.href='{{ route('chairperson.viewGrades', ['instructor_id' => $selectedInstructorId, 'subject_id' => $subjectItem->id]) }}'"
                        >
                            {{-- Top header --}}
                            <div class="position-relative header-height-80 bg-gradient-green-soft">
                                <div class="wildcard-circle-positioned">
                                    <h5 class="mb-0 text-white fw-bold">{{ $subjectItem->subject_code }}</h5>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="card-body pt-5 text-center">
                                <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $subjectItem->subject_description }}">
                                    {{ $subjectItem->subject_description }}
                                </h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state
                icon="bi-journal-x"
                title="No Subjects Found"
                message="This instructor has no subjects assigned for the current academic period."
            />
        @endif
    @else
        {{-- Step 3: Display Students' Final Grades --}}
        {{-- Students Table --}}
        @if (count($students) > 0)
            <div class="bg-white shadow-lg rounded-4 overflow-x-auto">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Student Name</th>
                            <th class="text-center">Prelim</th>
                            <th class="text-center">Midterm</th>
                            <th class="text-center">Prefinal</th>
                            <th class="text-center">Final</th>
                            <th class="text-center text-success">Final Average</th>
                            <th class="text-center">Remarks</th>
                            <th class="text-center" style="min-width: 200px;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            @php
                                $termGrades = $student->termGrades->keyBy('term_id');

                                $prelim = $termGrades[1]->term_grade ?? null;
                                $midterm = $termGrades[2]->term_grade ?? null;
                                $prefinal = $termGrades[3]->term_grade ?? null;
                                $final = $termGrades[4]->term_grade ?? null;

                                $hasAll = !is_null($prelim) && !is_null($midterm) && !is_null($prefinal) && !is_null($final);
                                $average = $hasAll ? round(($prelim + $midterm + $prefinal + $final) / 4) : null;

                                $remarks = $average !== null ? ($average >= 75 ? 'Passed' : 'Failed') : null;
                                
                                // Get final grade record for notes
                                $finalGradeRecord = $student->finalGrades->first();
                                $notes = $finalGradeRecord->notes ?? '';
                                $finalGradeId = $finalGradeRecord->id ?? null;
                            @endphp
                            <tr class="hover:bg-light">
                                <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                <td class="text-center">{{ $prelim !== null ? round($prelim) : '-' }}</td>
                                <td class="text-center">{{ $midterm !== null ? round($midterm) : '-' }}</td>
                                <td class="text-center">{{ $prefinal !== null ? round($prefinal) : '-' }}</td>
                                <td class="text-center">{{ $final !== null ? round($final) : '-' }}</td>
                                <td class="text-center fw-semibold text-success">
                                    {{ $average !== null ? $average : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($remarks === 'Passed')
                                        <span class="badge bg-success-subtle text-success fw-medium px-3 py-2 rounded-pill">Passed</span>
                                    @elseif($remarks === 'Failed')
                                        <span class="badge bg-danger-subtle text-danger fw-medium px-3 py-2 rounded-pill">Failed</span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($finalGradeId)
                                        <button 
                                            class="btn btn-sm btn-outline-primary open-notes-modal"
                                            data-final-grade-id="{{ $finalGradeId }}"
                                            data-student-name="{{ $student->last_name }}, {{ $student->first_name }}"
                                            data-notes="{{ $notes }}"
                                            title="View/Edit notes"
                                        >
                                            <i class="bi bi-sticky"></i>
                                            @if($notes)
                                                <span class="badge bg-success ms-1">Has Notes</span>
                                            @else
                                                Add Notes
                                            @endif
                                        </button>
                                    @else
                                        <span class="text-muted fst-italic">No final grade yet</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif(!empty($selectedSubjectId))
            <x-empty-state
                icon="bi-person-x"
                title="No Students Found"
                message="No students are enrolled in this subject."
            />
        @endif
    @endif
</div>

{{-- Notes Modal --}}
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="notesModalLabel">
                    <i class="bi bi-sticky me-2"></i>
                    Student Notes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Student Name:</label>
                    <p class="text-muted" id="studentNameDisplay"></p>
                </div>
                <div class="mb-3">
                    <label for="notesTextarea" class="form-label fw-semibold">Notes/Remarks:</label>
                    <textarea 
                        class="form-control" 
                        id="notesTextarea" 
                        rows="6" 
                        maxlength="1000"
                        placeholder="Enter notes or remarks for this student..."
                    ></textarea>
                    <div class="form-text">
                        <span id="charCount">0</span> / 1000 characters
                    </div>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> The "Passed/Failed" remarks are automatically calculated based on grades and will not be affected by these notes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-success" id="saveNotesBtn" x-data>
                    <span x-show="!$store.loading.isLoading('saveNotes')">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Notes
                    </span>
                    <span x-show="$store.loading.isLoading('saveNotes')" x-cloak>
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- Page data for external JS: resources/js/pages/chairperson/view-grades.js --}}
<script>
    window.pageData = {
        saveGradeNotesUrl: '{{ route('chairperson.saveGradeNotes') }}',
        csrfToken: '{{ csrf_token() }}'
    };
</script>
@endpush
</div>
@endsection
