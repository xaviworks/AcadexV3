@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        <!-- Page Title -->
        <div class="page-title">
            <h1>
                <i class="bi bi-bar-chart-fill"></i>
                Students' Final Grades
            </h1>
            <p class="page-subtitle">Select an instructor and subject to view students' final grades</p>
        </div>

    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item">
                <a href="{{ route('chairperson.viewGrades') }}" class="{{ empty($selectedInstructorId) && empty($selectedSubjectId) ? 'active' : '' }}">Select Instructor</a>
            </li>
            @if (!empty($selectedInstructorId) && empty($selectedSubjectId))
                <li class="breadcrumb-item active" aria-current="page">Select Subject</li>
            @elseif (!empty($selectedInstructorId) && !empty($selectedSubjectId))
                <li class="breadcrumb-item active" aria-current="page">Students' Final Grades</li>
            @endif
        </ol>
    </nav>

    {{-- Step 1: Instructor Selection --}}
    @if (empty($selectedInstructorId) && empty($selectedSubjectId))
        <div class="row g-4 px-4 py-4">
            @foreach($instructors as $instructor)
                <div class="col-md-4">
                    <div
                        class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden cursor-pointer transition-transform-shadow"
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
    @elseif (empty($selectedSubjectId))
        {{-- Step 2: Subject Selection --}}
        @if (!empty($subjects))
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
            <div class="text-center text-muted mt-8 bg-warning bg-opacity-25 border border-warning px-6 py-4 rounded-4">
                No subjects found for this instructor.
            </div>
        @endif
    @else
        {{-- Step 3: Display Students' Final Grades --}}
        {{-- Students Table --}}
        @if (!empty($students) && count($students))
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
            <div class="text-center text-muted mt-8 bg-warning bg-opacity-25 border border-warning px-6 py-4 rounded-4">
                No students found for this subject.
            </div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {\n    const notesTextarea = document.getElementById('notesTextarea');\n    const studentNameDisplay = document.getElementById('studentNameDisplay');\n    const saveNotesBtn = document.getElementById('saveNotesBtn');\n    const charCount = document.getElementById('charCount');\n    let currentFinalGradeId = null;\n    let currentButton = null;

    // Update character count
    notesTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    // Handle open notes modal button click
    document.querySelectorAll('.open-notes-modal').forEach(button => {
        button.addEventListener('click', function() {
            currentFinalGradeId = this.dataset.finalGradeId;
            currentButton = this;
            const studentName = this.dataset.studentName;
            const notes = this.dataset.notes || '';
            
            // Populate modal
            studentNameDisplay.textContent = studentName;
            notesTextarea.value = notes;
            charCount.textContent = notes.length;
            
            // Show modal
            modal.open('notesModal', { finalGradeId, studentName, notes });
        });
    });

    // Handle save notes button click
    saveNotesBtn.addEventListener('click', async function() {
        if (!currentFinalGradeId) return;
        
        const notes = notesTextarea.value.trim();
        
        // Start loading
        loading.start('saveNotes');
        this.disabled = true;
        
        try {
            const response = await fetch('{{ route('chairperson.saveGradeNotes') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    final_grade_id: currentFinalGradeId,
                    notes: notes
                })
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Update button appearance
                if (currentButton) {
                    const badgeExists = currentButton.querySelector('.badge');
                    if (notes) {
                        if (!badgeExists) {
                            currentButton.innerHTML = `
                                <i class="bi bi-sticky"></i>
                                <span class="badge bg-success ms-1">Has Notes</span>
                            `;
                        }
                    } else {
                        currentButton.innerHTML = `
                            <i class="bi bi-sticky"></i>
                            Add Notes
                        `;
                    }
                    // Update data attribute for next time
                    currentButton.dataset.notes = notes;
                }
                
                // Show success notification
                notify.success(data.message);
                
                // Close modal after short delay
                setTimeout(() => {
                    modal.close('notesModal');
                    loading.stop('saveNotes');
                    this.disabled = false;
                }, 500);
            } else {
                throw new Error(data.message || 'Failed to save notes');
            }
        } catch (error) {
            console.error('Error saving notes:', error);
            loading.stop('saveNotes');
            this.disabled = false;
            notify.error(error.message || 'Failed to save notes. Please try again.');
        }
    });

    // Reset modal when closed
    document.getElementById('notesModal').addEventListener('hidden.bs.modal', function() {
        currentFinalGradeId = null;
        currentButton = null;
        notesTextarea.value = '';
        charCount.textContent = '0';
        studentNameDisplay.textContent = '';
    });
    
    // Helper function to show toast notifications
    function showToast(title, message, type = 'info') {
        // Create toast element
        const toastHTML = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Get or create toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Add toast to container
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
});
</script>
@endpush

    </div>
</div>
@endsection
