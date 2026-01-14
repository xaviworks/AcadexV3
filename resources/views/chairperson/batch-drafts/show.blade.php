@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('chairperson.batch-drafts.index') }}">Batch Drafts</a></li>
            <li class="breadcrumb-item active">{{ $batchDraft->batch_name }}</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-success mb-1">
                <i class="bi bi-folder-symlink me-2"></i>{{ $batchDraft->batch_name }}
            </h2>
            <p class="text-muted mb-0">
                {{ $batchDraft->academicPeriod->semester ?? '' }} {{ $batchDraft->academicPeriod->academic_year ?? '' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('chairperson.batch-drafts.duplicate', $batchDraft) }}" class="btn btn-outline-success rounded-pill shadow-sm hover-lift">
                <i class="bi bi-files me-1"></i>Duplicate
            </a>
            <a href="{{ route('chairperson.batch-drafts.edit', $batchDraft) }}" class="btn btn-outline-secondary rounded-pill shadow-sm hover-lift">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <button type="button" class="btn btn-outline-danger rounded-pill shadow-sm hover-lift" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Workflow Progress -->
    @php
        $totalSubjects = $batchDraft->batchDraftSubjects->count();
        $appliedCount = $batchDraft->batchDraftSubjects->where('configuration_applied', true)->count();
        $progress = $totalSubjects > 0 ? round(($appliedCount / $totalSubjects) * 100) : 0;
    @endphp
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-list-check me-2 text-primary"></i>Progress
                </h6>
                @if($totalSubjects == 0)
                    <span class="badge bg-secondary">No Subjects</span>
                @elseif($progress == 100)
                    <span class="badge bg-success">Complete</span>
                @elseif($progress > 0)
                    <span class="badge bg-warning">{{ $appliedCount }}/{{ $totalSubjects }} Done</span>
                @else
                    <span class="badge bg-info">Ready to Configure</span>
                @endif
            </div>
            
            <div class="progress" style="height: 10px;">
                <div class="progress-bar 
                    @if($progress == 100) bg-success 
                    @elseif($progress > 0) bg-warning 
                    @else bg-info 
                    @endif" 
                     role="progressbar" 
                     style="width: {{ $totalSubjects > 0 ? $progress : 100 }}%">
                </div>
            </div>
            
            <div class="row g-3 mt-2 text-center">
                <div class="col">
                    <div class="fw-bold text-primary">{{ $batchDraft->students->count() }}</div>
                    <small class="text-muted">Students</small>
                </div>
                <div class="col">
                    <div class="fw-bold text-info">{{ $totalSubjects }}</div>
                    <small class="text-muted">Subjects</small>
                </div>
                <div class="col">
                    <div class="fw-bold text-success">{{ $appliedCount }}</div>
                    <small class="text-muted">Configured</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Batch Information -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Batch Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Course</small>
                            <span class="badge bg-primary">{{ $batchDraft->course->course_code ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Year Level</small>
                            <span class="fw-semibold">Year {{ $batchDraft->year_level }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">CO Template</small>
                            <a href="{{ route('chairperson.co-templates.show', $batchDraft->coTemplate) }}" 
                               class="text-decoration-none fw-semibold small">
                                {{ $batchDraft->coTemplate->template_name ?? 'N/A' }}
                            </a>
                        </div>
                        @if($batchDraft->description)
                            <div class="col-12 pt-2 border-top">
                                <small class="text-muted d-block mb-1">Description</small>
                                <small class="text-muted">{{ $batchDraft->description }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-lightning-fill me-2 text-warning"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#attachSubjectsModal"
                                @if($batchDraft->students->count() == 0) disabled title="Import students first" @endif>
                            <i class="bi bi-plus-circle me-2"></i>Attach Subjects
                        </button>
                        
                        <a href="{{ route('chairperson.batch-drafts.edit', $batchDraft) }}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Details
                        </a>
                        
                        <a href="{{ route('chairperson.co-templates.show', $batchDraft->coTemplate) }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-eye me-2"></i>View Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students and Subjects Row -->
    <div class="row g-4 mt-2">
        <!-- Students List -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-people me-2 text-info"></i>Students
                        <span class="badge bg-info-subtle text-info">{{ $batchDraft->students->count() }}</span>
                    </h5>
                    <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm hover-lift" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Student
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($batchDraft->students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>First Name</th>
                                        <th>Middle Name</th>
                                        <th>Last Name</th>
                                        <th>Course</th>
                                        <th>Year Level</th>
                                        <th width="60">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batchDraft->students as $student)
                                        <tr>
                                            <td class="fw-semibold">{{ $student->first_name }}</td>
                                            <td>{{ $student->middle_name ?? '-' }}</td>
                                            <td class="fw-semibold">{{ $student->last_name }}</td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $student->course->course_code ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $student->year_level }}</td>
                                            <td>
                                                <form action="{{ route('chairperson.batch-drafts.students.destroy', [$batchDraft, $student]) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Remove {{ $student->first_name }} {{ $student->last_name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill" title="Remove">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-people display-1 mb-3 d-block opacity-25"></i>
                            <h5 class="mb-3">No Students Imported Yet</h5>
                            <p class="mb-4 text-muted">This is <strong>Step 1</strong> of the batch draft process.<br>Import students using a CSV or XLSX file.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ asset('sample_imports/batch_draft_students_template.csv') }}" 
                                   class="btn btn-sm btn-outline-secondary"
                                   download>
                                    <i class="bi bi-download me-1"></i>Download Template
                                </a>
                            </div>
                            <div class="alert alert-info border-0 mt-4 mx-auto" style="max-width: 500px;">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Students will be imported when you apply configuration to subjects.</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Subjects Management -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-book me-2 text-warning"></i>Subjects
                        <span class="badge bg-warning-subtle text-warning">{{ $batchDraft->batchDraftSubjects->count() }}</span>
                    </h5>
                    @if($batchDraft->students->count() > 0)
                        <button type="button" 
                                class="btn btn-sm btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#attachSubjectsModal">
                            <i class="bi bi-plus-circle me-1"></i>Attach Subjects
                        </button>
                    @else
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                disabled
                                data-bs-toggle="tooltip"
                                title="Import students first before attaching subjects">
                            <i class="bi bi-lock-fill me-1"></i>Attach Subjects
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($batchDraft->batchDraftSubjects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%;">Subject Code</th>
                                        <th style="width: 35%;">Subject Description</th>
                                        <th style="width: 28%;">Status</th>
                                        <th style="width: 22%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batchDraft->batchDraftSubjects as $batchSubject)
                                        <tr>
                                            <td class="fw-semibold py-3">{{ $batchSubject->subject->subject_code ?? 'N/A' }}</td>
                                            <td class="py-3">{{ $batchSubject->subject->subject_description ?? 'N/A' }}</td>
                                            <td class="py-3">
                                                @if($batchSubject->configuration_applied)
                                                    <div class="mb-1">
                                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                                            <i class="bi bi-check-circle-fill me-1"></i>Applied
                                                        </span>
                                                    </div>
                                                    <div>
                                                        @if($batchSubject->subject->instructor)
                                                            <small class="text-success">
                                                                <i class="bi bi-person-check me-1"></i>{{ $batchDraft->students->count() }} students
                                                            </small>
                                                        @else
                                                            <small class="text-muted">
                                                                <i class="bi bi-person-x me-1"></i>No instructor
                                                            </small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2">
                                                        <i class="bi bi-circle me-1"></i>Ready
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center py-3">
                                                @if(!$batchSubject->configuration_applied)
                                                    <form action="{{ route('chairperson.batch-drafts.apply-configuration', $batchDraft) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Apply configuration to {{ addslashes($batchSubject->subject->subject_description ?? 'this subject') }}? This will import {{ $batchDraft->students->count() }} students and create {{ $batchDraft->coTemplate->items->count() }} course outcomes.');">
                                                        @csrf
                                                        <input type="hidden" name="subject_id" value="{{ $batchSubject->subject_id }}">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill shadow-sm hover-lift">
                                                            <i class="bi bi-play-circle-fill me-1"></i>Apply
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4 mb-3 d-block opacity-25"></i>
                            <h5 class="mb-3">No Subjects Attached Yet</h5>
                            <p class="mb-4">Start by attaching subjects to this batch draft.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attachSubjectsModal">
                                <i class="bi bi-plus-circle me-2"></i>Attach Your First Subject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Attach Subjects Modal -->
<div class="modal fade" id="attachSubjectsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>Attach Subjects to Batch Draft
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('chairperson.batch-drafts.attach-subjects', $batchDraft) }}" method="POST" id="attachSubjectsForm">
                @csrf
                <div class="modal-body">
                    <!-- Info Alert -->
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill fs-4 me-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading mb-2">Step 2: Attach Subjects</h6>
                                <p class="mb-2 small">Select the subjects you want to include in this batch draft. After attaching:</p>
                                <ul class="mb-0 small ps-3">
                                    <li>You'll need to <strong>"Apply Configuration"</strong> for each subject</li>
                                    <li>This will import the {{ $batchDraft->students->count() }} students to each subject</li>
                                    <li>And create {{ $batchDraft->coTemplate->items->count() }} course outcomes based on the template</li>
                                    <li>Only then can subjects be assigned to instructors</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $availableSubjects = \App\Models\Subject::where('academic_period_id', $batchDraft->academic_period_id)
                            ->where('course_id', $batchDraft->course_id)
                            ->whereNotIn('id', $batchDraft->subjects->pluck('id'))
                            ->get();
                    @endphp

                    @if($availableSubjects->count() > 0)
                        <label class="form-label fw-bold mb-3">
                            <i class="bi bi-list-check me-2"></i>Available Subjects ({{ $availableSubjects->count() }})
                        </label>
                        <div class="list-group">
                            @foreach($availableSubjects as $subject)
                                <label class="list-group-item list-group-item-action border-start border-4 border-primary">
                                    <div class="form-check">
                                        <input class="form-check-input subject-checkbox" 
                                               type="checkbox" 
                                               name="subject_ids[]" 
                                               value="{{ $subject->id }}"
                                               id="subject_{{ $subject->id }}">
                                        <label class="form-check-label w-100" for="subject_{{ $subject->id }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-primary">{{ $subject->subject_code }}</div>
                                                    <div class="text-dark">{{ $subject->subject_description }}</div>
                                                    @if($subject->instructor)
                                                        <small class="text-success">
                                                            <i class="bi bi-person-check me-1"></i>{{ $subject->instructor->name }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted">
                                                            <i class="bi bi-person-x me-1"></i>No instructor assigned yet
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3 text-muted small">
                            <i class="bi bi-lightbulb me-1"></i>
                            <span id="selectedCount">0</span> subject(s) selected
                        </div>
                    @else
                        <div class="alert alert-warning border-0 shadow-sm mb-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">No Available Subjects</h6>
                                    <p class="mb-0 small">All subjects for <strong>{{ $batchDraft->course->course_code }}</strong> in this academic period are already attached.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    @if($availableSubjects->count() > 0)
                        <button type="submit" class="btn btn-primary" id="attachSubjectsBtn" disabled>
                            <i class="bi bi-check-circle me-1"></i>Attach <span id="btnSelectedCount">0</span> Subject(s)
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Subject selection counter
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Subject checkbox counter
        const checkboxes = document.querySelectorAll('.subject-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        const btnSelectedCount = document.getElementById('btnSelectedCount');
        const attachBtn = document.getElementById('attachSubjectsBtn');
        
        function updateCount() {
            const checked = document.querySelectorAll('.subject-checkbox:checked').length;
            if (selectedCount) selectedCount.textContent = checked;
            if (btnSelectedCount) btnSelectedCount.textContent = checked;
            if (attachBtn) attachBtn.disabled = checked === 0;
        }
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateCount);
        });
        
        // Initialize count
        updateCount();
    });
</script>
@endpush

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-plus me-2"></i>Add Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('chairperson.batch-drafts.students.add', $batchDraft) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Course and Year Level will be set to {{ $batchDraft->course->course_code }} Year {{ $batchDraft->year_level }}
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this batch draft?</p>
                <p class="text-danger fw-semibold mb-0 mt-2">
                    This action cannot be undone. All associated data will be permanently deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('chairperson.batch-drafts.destroy', $batchDraft) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete Batch Draft
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.card {
    border-radius: 1rem;
    overflow: hidden;
}

.table > :not(caption) > * > * {
    padding: 0.75rem 1rem;
}

.btn-rounded-pill {
    border-radius: 50px;
}

.badge {
    font-weight: 600;
    padding: 0.35em 0.65em;
}
</style>
@endsection
