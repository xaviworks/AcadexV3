@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('chairperson.batch-drafts.index') }}">Batch Drafts</a></li>
            <li class="breadcrumb-item active">Create Batch Draft</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-success mb-1">
            <i class="bi bi-plus-circle me-2"></i>Create Batch Draft
        </h2>
        <p class="text-muted mb-0">Configure student import and CO template assignment</p>
    </div>

    <!-- Alerts -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Batch Draft Information
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('chairperson.batch-drafts.store') }}" method="POST" enctype="multipart/form-data" id="batchDraftForm">
                @csrf

                <div class="row g-3">
                    <!-- Batch Name -->
                    <div class="col-md-6">
                        <label for="batch_name" class="form-label fw-semibold">
                            Batch Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('batch_name') is-invalid @enderror" 
                               id="batch_name" 
                               name="batch_name" 
                               value="{{ old('batch_name') }}"
                               placeholder="e.g., BSIT 3A - SY 2024-2025"
                               required>
                        @error('batch_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Enter a descriptive name for this batch</small>
                    </div>

                    <!-- Course Selection -->
                    <div class="col-md-6">
                        <label for="course_id" class="form-label fw-semibold">
                            Course <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('course_id') is-invalid @enderror" 
                                id="course_id" 
                                name="course_id" 
                                required>
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->course_code }} - {{ $course->course_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Year Level -->
                    <div class="col-md-6">
                        <label for="year_level" class="form-label fw-semibold">
                            Year Level <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('year_level') is-invalid @enderror" 
                                id="year_level" 
                                name="year_level" 
                                required>
                            <option value="">-- Select Year Level --</option>
                            <option value="1" {{ old('year_level') == '1' ? 'selected' : '' }}>First Year</option>
                            <option value="2" {{ old('year_level') == '2' ? 'selected' : '' }}>Second Year</option>
                            <option value="3" {{ old('year_level') == '3' ? 'selected' : '' }}>Third Year</option>
                            <option value="4" {{ old('year_level') == '4' ? 'selected' : '' }}>Fourth Year</option>
                            <option value="5" {{ old('year_level') == '5' ? 'selected' : '' }}>Fifth Year</option>
                        </select>
                        @error('year_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- CO Template Selection -->
                    <div class="col-md-6">
                        <label for="co_template_id" class="form-label fw-semibold">
                            Course Outcome Template <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('co_template_id') is-invalid @enderror" 
                                id="co_template_id" 
                                name="co_template_id" 
                                required>
                            <option value="">-- Select CO Template --</option>
                            @foreach($coTemplates as $template)
                                <option value="{{ $template->id }}" 
                                        data-co-count="{{ $template->items->count() }}"
                                        {{ old('co_template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->template_name }}
                                    @if($template->is_universal)
                                        (Universal)
                                    @else
                                        ({{ $template->course->course_code ?? 'N/A' }})
                                    @endif
                                    - {{ $template->items->count() }} CO Items
                                </option>
                            @endforeach
                        </select>
                        @error('co_template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Select a CO template to apply to all subjects</small>
                    </div>

                    <!-- Student Import File -->
                    <div class="col-12">
                        <label for="students_file" class="form-label fw-semibold">
                            Student List File (CSV/XLSX) <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control @error('students_file') is-invalid @enderror" 
                               id="students_file" 
                               name="students_file" 
                               accept=".csv,.xlsx"
                               required>
                        @error('students_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Upload a CSV or XLSX file with student information. 
                            <a href="{{ asset('sample_imports/batch_draft_students_template.csv') }}" download>Download sample template</a>
                        </small>
                    </div>

                    <!-- File Format Guide -->
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-start">
                            <i class="bi bi-info-circle me-3 fs-5"></i>
                            <div>
                                <h6 class="fw-bold mb-2">Required CSV/XLSX Columns:</h6>
                                <ul class="mb-0 small">
                                    <li><code>First Name</code> - Student first name (required)</li>
                                    <li><code>Middle Name</code> - Student middle name (optional)</li>
                                    <li><code>Last Name</code> - Student last name (required)</li>
                                    <li><code>Year Level</code> - Year level number 1-5 (optional, will use selected year if empty)</li>
                                </ul>
                                <small class="text-muted mt-2 d-block">Course will be automatically set from your selection above.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Description (Optional) -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">
                            Description (Optional)
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Enter any additional notes or description for this batch draft">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('chairperson.batch-drafts.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Create Batch Draft
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-lightbulb text-warning me-2"></i>What happens after creating a batch draft?
            </h6>
            <ol class="mb-0">
                <li class="mb-2">Students will be imported from the uploaded file</li>
                <li class="mb-2">The selected CO template will be linked to this batch</li>
                <li class="mb-2">You can then attach subjects to this batch draft</li>
                <li class="mb-0">Apply the configuration to import students and COs to each subject</li>
            </ol>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input validation
    const fileInput = document.getElementById('students_file');
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // Convert to MB
            const fileExt = file.name.split('.').pop().toLowerCase();
            
            if (!['csv', 'xlsx'].includes(fileExt)) {
                alert('Please upload a CSV or XLSX file.');
                this.value = '';
                return;
            }
            
            if (fileSize > 10) {
                alert('File size must be less than 10MB.');
                this.value = '';
                return;
            }
        }
    });

    // CO Template selection info
    const coTemplateSelect = document.getElementById('co_template_id');
    coTemplateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const coCount = selectedOption.getAttribute('data-co-count');
        
        if (coCount) {
            console.log(`Selected template has ${coCount} CO items`);
        }
    });
});
</script>
@endsection
