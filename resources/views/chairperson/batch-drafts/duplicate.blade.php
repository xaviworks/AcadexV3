@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('chairperson.batch-drafts.index') }}">Batch Drafts</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chairperson.batch-drafts.show', $batchDraft) }}">{{ $batchDraft->batch_name }}</a></li>
            <li class="breadcrumb-item active">Duplicate</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-success mb-1">
            <i class="bi bi-files me-2"></i>Duplicate Batch Draft
        </h2>
        <p class="text-muted mb-0">Create a copy of "{{ $batchDraft->batch_name }}" with customizable options</p>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Source Batch Info -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Source Batch</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Batch Name</small>
                        <strong>{{ $batchDraft->batch_name }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Course</small>
                        <strong>{{ $batchDraft->course->course_code }} - {{ $batchDraft->course->course_name }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Year Level</small>
                        <strong>Year {{ $batchDraft->year_level }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">CO Template</small>
                        <strong>{{ $batchDraft->coTemplate->template_name }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Students</small>
                        <span class="badge bg-success">{{ $batchDraft->students->count() }} students</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Subjects</small>
                        <span class="badge bg-info">{{ $batchDraft->batchDraftSubjects->count() }} subjects</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Academic Period</small>
                        <strong>{{ $batchDraft->academicPeriod->semester ?? '' }} {{ $batchDraft->academicPeriod->academic_year ?? '' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duplicate Form -->
        <div class="col-md-8">
            <form action="{{ route('chairperson.batch-drafts.store-duplicate', $batchDraft) }}" method="POST">
                @csrf

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Basic Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">New Batch Name <span class="text-danger">*</span></label>
                            <input type="text" name="batch_name" class="form-control" 
                                   value="{{ old('batch_name', $batchDraft->batch_name . ' (Copy)') }}" required>
                            <small class="text-muted">Choose a unique name for the new batch</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" class="form-select" required>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" 
                                                    {{ old('course_id', $batchDraft->course_id) == $course->id ? 'selected' : '' }}>
                                                {{ $course->course_code }} - {{ $course->course_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Year Level <span class="text-danger">*</span></label>
                                    <select name="year_level" class="form-select" required>
                                        @for($i = 1; $i <= 6; $i++)
                                            <option value="{{ $i }}" {{ old('year_level', $batchDraft->year_level) == $i ? 'selected' : '' }}>
                                                Year {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">CO Template <span class="text-danger">*</span></label>
                            <select name="co_template_id" class="form-select" required>
                                @foreach($coTemplates as $template)
                                    <option value="{{ $template->id }}" 
                                            {{ old('co_template_id', $batchDraft->co_template_id) == $template->id ? 'selected' : '' }}>
                                        {{ $template->template_name }}
                                        @if($template->is_universal)
                                            <span class="badge bg-info">Universal</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $batchDraft->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Clone Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" name="clone_students" id="clone_students" 
                                   class="form-check-input" value="1" 
                                   {{ old('clone_students', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="clone_students">
                                <i class="bi bi-people-fill text-success"></i> Clone Students
                            </label>
                            <small class="d-block text-muted mt-1">
                                Copy all {{ $batchDraft->students->count() }} students from the original batch
                            </small>
                        </div>

                        <div class="form-check form-switch mb-3" id="promote_option" style="margin-left: 2rem;">
                            <input type="checkbox" name="promote_year_level" id="promote_year_level" 
                                   class="form-check-input" value="1" 
                                   {{ old('promote_year_level', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="promote_year_level">
                                <i class="bi bi-arrow-up-circle text-info"></i> Auto-promote year level (+1)
                            </label>
                            <small class="d-block text-muted mt-1">
                                Automatically increment student year levels (for returning students)
                            </small>
                        </div>

                        <hr>

                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" name="clone_subjects" id="clone_subjects" 
                                   class="form-check-input" value="1" 
                                   {{ old('clone_subjects', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="clone_subjects">
                                <i class="bi bi-book-fill text-warning"></i> Clone Subject Associations
                            </label>
                            <small class="d-block text-muted mt-1">
                                Attach subjects with matching codes from the new academic period ({{ $batchDraft->batchDraftSubjects->count() }} subjects)
                            </small>
                        </div>

                        <div class="alert alert-info border-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Configuration will NOT be applied automatically. 
                            You'll need to apply it manually after duplication.
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-eye me-2"></i>Preview Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0" id="preview_students">
                                        <span class="preview-count">{{ $batchDraft->students->count() }}</span>
                                    </h3>
                                    <small class="text-muted">Students will be imported</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0" id="preview_subjects">
                                        <span class="preview-count">{{ $batchDraft->batchDraftSubjects->count() }}</span>
                                    </h3>
                                    <small class="text-muted">Subjects will be attached</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-info">
                                        <i class="bi bi-arrow-right"></i>
                                    </h3>
                                    <small class="text-muted">To new academic period</small>
                                </div>
                            </div>
                        </div>

                        <div id="promote_warning" class="alert alert-warning border-0 d-none">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Year Level Promotion Active:</strong> Students will be moved from 
                            Year {{ $batchDraft->year_level }} to Year <span id="promoted_year">{{ $batchDraft->year_level + 1 }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('chairperson.batch-drafts.show', $batchDraft) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-files me-2"></i>Duplicate Batch Draft
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const originalStudents = {{ $batchDraft->students->count() }};
const originalSubjects = {{ $batchDraft->batchDraftSubjects->count() }};
const originalYearLevel = {{ $batchDraft->year_level }};

// Toggle dependent options
document.getElementById('clone_students').addEventListener('change', function() {
    updatePreview();
    document.getElementById('promote_option').style.display = this.checked ? 'block' : 'none';
    if (!this.checked) {
        document.getElementById('promote_year_level').checked = false;
    }
});

document.getElementById('clone_subjects').addEventListener('change', updatePreview);

document.getElementById('promote_year_level').addEventListener('change', function() {
    const warning = document.getElementById('promote_warning');
    const yearSelect = document.querySelector('select[name="year_level"]');
    
    if (this.checked) {
        warning.classList.remove('d-none');
        const newYear = originalYearLevel + 1;
        document.getElementById('promoted_year').textContent = newYear;
        yearSelect.value = newYear;
    } else {
        warning.classList.add('d-none');
        yearSelect.value = originalYearLevel;
    }
});

function updatePreview() {
    const cloneStudents = document.getElementById('clone_students').checked;
    const cloneSubjects = document.getElementById('clone_subjects').checked;
    
    // Update students count
    const studentsElements = document.querySelectorAll('#preview_students .preview-count');
    studentsElements.forEach(el => {
        el.textContent = cloneStudents ? originalStudents : 0;
        el.parentElement.classList.toggle('text-muted', !cloneStudents);
        el.parentElement.classList.toggle('text-success', cloneStudents);
    });
    
    // Update subjects count
    const subjectsElements = document.querySelectorAll('#preview_subjects .preview-count');
    subjectsElements.forEach(el => {
        el.textContent = cloneSubjects ? originalSubjects : 0;
        el.parentElement.classList.toggle('text-muted', !cloneSubjects);
        el.parentElement.classList.toggle('text-warning', cloneSubjects);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
    const cloneStudents = document.getElementById('clone_students').checked;
    document.getElementById('promote_option').style.display = cloneStudents ? 'block' : 'none';
});
</script>
@endsection
