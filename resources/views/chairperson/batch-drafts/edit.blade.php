@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('chairperson.batch-drafts.index') }}">Batch Drafts</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chairperson.batch-drafts.show', $batchDraft) }}">{{ $batchDraft->batch_name }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-success mb-1">
            <i class="bi bi-pencil-square me-2"></i>Edit Batch Draft
        </h2>
        <p class="text-muted mb-0">Update batch draft information</p>
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

    <!-- Info Alert -->
    @if($batchDraft->batchDraftSubjects->where('configuration_applied', true)->count() > 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-2">Configuration Applied</h6>
                    <p class="mb-0 small">
                        Some subjects have already been configured with this batch draft. 
                        Changing the course or CO template will <strong>not affect</strong> already configured subjects.
                        Only new configurations will use the updated settings.
                    </p>
                </div>
            </div>
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
            <form action="{{ route('chairperson.batch-drafts.update', $batchDraft) }}" method="POST" id="batchDraftEditForm">
                @csrf
                @method('PUT')

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
                               value="{{ old('batch_name', $batchDraft->batch_name) }}"
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
                                <option value="{{ $course->id }}" 
                                        {{ old('course_id', $batchDraft->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->course_code }} - {{ $course->course_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($batchDraft->batchDraftSubjects->count() > 0)
                            <small class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Changing this will affect subject availability
                            </small>
                        @endif
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
                                        {{ old('co_template_id', $batchDraft->co_template_id) == $template->id ? 'selected' : '' }}>
                                    {{ $template->template_name }} 
                                    @if($template->is_universal)
                                        <span class="badge bg-info">Universal</span>
                                    @endif
                                    ({{ $template->items->count() }} COs)
                                </option>
                            @endforeach
                        </select>
                        @error('co_template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="coTemplateInfo" class="mt-2"></div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">
                            Description <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Add notes or details about this batch draft...">{{ old('description', $batchDraft->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Current Statistics -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info border-0">
                            <h6 class="alert-heading fw-bold mb-2">
                                <i class="bi bi-info-circle me-2"></i>Current Status
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="text-muted small">Students Imported</div>
                                    <div class="fs-5 fw-bold text-primary">{{ $batchDraft->students->count() }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">Subjects Attached</div>
                                    <div class="fs-5 fw-bold text-info">{{ $batchDraft->batchDraftSubjects->count() }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">Configured</div>
                                    <div class="fs-5 fw-bold text-success">{{ $batchDraft->batchDraftSubjects->where('configuration_applied', true)->count() }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">Pending</div>
                                    <div class="fs-5 fw-bold text-warning">{{ $batchDraft->batchDraftSubjects->where('configuration_applied', false)->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('chairperson.batch-drafts.show', $batchDraft) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Update Batch Draft
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const coTemplateSelect = document.getElementById('co_template_id');
        const coTemplateInfo = document.getElementById('coTemplateInfo');
        
        function updateCoTemplateInfo() {
            const selectedOption = coTemplateSelect.options[coTemplateSelect.selectedIndex];
            const coCount = selectedOption.getAttribute('data-co-count');
            
            if (coCount) {
                coTemplateInfo.innerHTML = `
                    <div class="alert alert-success border-0 py-2 mb-0">
                        <i class="bi bi-check-circle me-1"></i>
                        <small>This template contains <strong>${coCount} course outcome(s)</strong></small>
                    </div>
                `;
            } else {
                coTemplateInfo.innerHTML = '';
            }
        }
        
        // Update on page load
        updateCoTemplateInfo();
        
        // Update on change
        coTemplateSelect.addEventListener('change', updateCoTemplateInfo);
    });
</script>
@endpush
@endsection
