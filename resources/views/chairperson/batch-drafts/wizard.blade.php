@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="bi bi-stars me-2 text-warning"></i>Quick Setup Wizard
                </h2>
                <p class="text-muted mb-0">Create and configure your batch in 4 simple steps</p>
            </div>
            <a href="{{ route('chairperson.batch-drafts.index') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
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

    <!-- Wizard Form -->
    <form action="{{ route('chairperson.batch-drafts.store-wizard') }}" method="POST" enctype="multipart/form-data" id="wizardForm">
        @csrf

        <!-- Progress Steps -->
        <div class="mb-4">
            <div class="p-4">
                <div class="row text-center position-relative">
                    <div class="progress-line"></div>
                    <div class="col wizard-step active" data-step="1">
                        <div class="step-circle">
                            <i class="bi bi-info-circle"></i>
                            <span class="step-number">1</span>
                        </div>
                        <div class="step-label mt-2 fw-semibold">Basic Info</div>
                    </div>
                    <div class="col wizard-step" data-step="2">
                        <div class="step-circle">
                            <i class="bi bi-people"></i>
                            <span class="step-number">2</span>
                        </div>
                        <div class="step-label mt-2 fw-semibold">Students</div>
                    </div>
                    <div class="col wizard-step" data-step="3">
                        <div class="step-circle">
                            <i class="bi bi-book"></i>
                            <span class="step-number">3</span>
                        </div>
                        <div class="step-label mt-2 fw-semibold">Subjects</div>
                    </div>
                    <div class="col wizard-step" data-step="4">
                        <div class="step-circle">
                            <i class="bi bi-check2-circle"></i>
                            <span class="step-number">4</span>
                        </div>
                        <div class="step-label mt-2 fw-semibold">Review</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Basic Information -->
        <div class="wizard-content" id="step1">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Step 1: Basic Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Batch Name <span class="text-danger">*</span></label>
                                <input type="text" name="batch_name" id="batch_name" class="form-control" 
                                       value="{{ old('batch_name') }}" required>
                                <small class="text-muted">
                                    <i class="bi bi-lightbulb"></i> Suggestion: <span id="nameSuggestion" class="text-primary fw-bold">Select course and year to auto-generate</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Year Level <span class="text-danger">*</span></label>
                                <select name="year_level" id="year_level" class="form-select" required>
                                    <option value="">Select Year</option>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ old('year_level') == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Course</label>
                                @if(Auth::user()->role === 1 && $courses->count() === 1)
                                    @php $course = $courses->first(); @endphp
                                    <input type="hidden" name="course_id" id="course_id" value="{{ $course->id }}" data-code="{{ $course->course_code }}">
                                    <div class="form-control bg-light" style="cursor: not-allowed;">
                                        {{ $course->course_code }}
                                    </div>
                                    <small class="text-muted"><i class="bi bi-info-circle"></i> Your assigned course</small>
                                @else
                                    <select name="course_id" id="course_id" class="form-select" required>
                                        <option value="">Select Course</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" 
                                                    data-code="{{ $course->course_code }}"
                                                    {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->course_code }} - {{ $course->course_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">CO Template <span class="text-danger">*</span></label>
                                <select name="co_template_id" id="co_template_id" class="form-select" required>
                                    <option value="">Select Template</option>
                                    @foreach($coTemplates as $template)
                                        <option value="{{ $template->id }}" {{ old('co_template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->template_name }}
                                            @if($template->is_universal)
                                                <span class="badge bg-info">Universal</span>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    <a href="{{ route('chairperson.co-templates.create') }}" target="_blank">
                                        <i class="bi bi-plus-circle"></i> Create new template
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-success" onclick="nextStep(2)">
                            Next: Import Students <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Import Students -->
        <div class="wizard-content d-none" id="step2">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-people me-2"></i>Step 2: Import Students</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Choose Import Method <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <!-- Upload File -->
                            <div class="col-md-4">
                                <div class="card border-2 import-method-card shadow-sm" data-method="file">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper mb-2">
                                            <i class="bi bi-cloud-upload fs-2 text-primary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">Upload File</h6>
                                        <p class="text-muted small mb-0">CSV or Excel</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Copy-Paste -->
                            <div class="col-md-4">
                                <div class="card border-2 import-method-card shadow-sm" data-method="paste">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper mb-2">
                                            <i class="bi bi-clipboard-data fs-2 text-info"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">Copy-Paste</h6>
                                        <p class="text-muted small mb-0">From spreadsheet</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Previous Batch -->
                            <div class="col-md-4">
                                <div class="card border-2 import-method-card shadow-sm" data-method="previous_batch">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-wrapper mb-2">
                                            <i class="bi bi-arrow-repeat fs-2 text-warning"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">Previous Batch</h6>
                                        <p class="text-muted small mb-0">From existing</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="student_import_method" id="student_import_method" value="{{ old('student_import_method', 'file') }}">
                    </div>

                    <!-- File Upload Section -->
                    <div id="import_file_section" class="import-section">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select File <span class="text-danger">*</span></label>
                            <input type="file" name="students_file" id="students_file" 
                                   class="form-control" accept=".xlsx,.xls,.csv">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Supported formats: Excel (.xlsx, .xls) or CSV (.csv)
                                <br>Expected columns: First Name, Middle Name, Last Name, Year Level (optional)
                            </small>
                            <div id="file_preview" class="mt-2"></div>
                        </div>
                    </div>

                    <!-- Copy-Paste Section -->
                    <div id="import_paste_section" class="import-section d-none">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Paste Student Data <span class="text-danger">*</span></label>
                            <textarea name="students_paste" id="students_paste" class="form-control font-monospace" 
                                      rows="10" placeholder="Paste from Excel/Google Sheets here...&#10;&#10;Example (tab or comma separated):&#10;John	M	Doe	1&#10;Jane	A	Smith	1&#10;Robert		Johnson	1">{{ old('students_paste') }}</textarea>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Paste directly from Excel or Google Sheets. Supports tab or comma separated values.
                                <br>Format: First Name, Middle Name, Last Name, Year Level (optional)
                            </small>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="previewPastedData()">
                                <i class="bi bi-eye"></i> Preview
                            </button>
                            <span id="paste_preview_count" class="ms-2 text-success"></span>
                        </div>
                    </div>

                    <!-- Previous Batch Section -->
                    <div id="import_previous_section" class="import-section d-none">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Previous Batch <span class="text-danger">*</span></label>
                            <select name="previous_batch_id" id="previous_batch_id" class="form-select">
                                <option value="">Select a batch to import from</option>
                                @foreach($previousBatches as $batch)
                                    <option value="{{ $batch->id }}" 
                                            data-students="{{ $batch->students->count() }}"
                                            {{ old('previous_batch_id') == $batch->id ? 'selected' : '' }}>
                                        {{ $batch->batch_name }} 
                                        ({{ $batch->students->count() }} students - {{ $batch->course->course_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div id="previous_batch_info" class="alert alert-info mt-2 d-none">
                                <i class="bi bi-info-circle"></i> 
                                Will import <strong id="previous_student_count">0</strong> students from selected batch
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn btn-success" onclick="nextStep(3)">
                            Next: Select Subjects <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Select Subjects -->
        <div class="wizard-content d-none" id="step3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-book me-2"></i>Step 3: Select Subjects (Optional)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="mb-0">Select subjects to attach to this batch draft. You can also do this later.</p>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAllSubjects()">
                                    <i class="bi bi-check-all"></i> Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSubjects()">
                                    <i class="bi bi-x"></i> Clear
                                </button>
                            </div>
                        </div>

                        <div id="subjects_loading" class="text-center py-4 d-none">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading subjects...</p>
                        </div>

                        <div id="subjects_container" class="row g-3">
                            <div class="col-12 text-center text-muted py-4">
                                <i class="bi bi-info-circle fs-3"></i>
                                <p class="mt-2">Subjects will load automatically when you enter this step</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn btn-success" onclick="nextStep(4)">
                            Next: Review & Confirm <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Review & Confirm -->
        <div class="wizard-content d-none" id="step4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Step 4: Review & Confirm</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Basic Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="140">Batch Name:</th>
                                    <td id="review_batch_name">-</td>
                                </tr>
                                <tr>
                                    <th>Course:</th>
                                    <td id="review_course">-</td>
                                </tr>
                                <tr>
                                    <th>Year Level:</th>
                                    <td id="review_year">-</td>
                                </tr>
                                <tr>
                                    <th>CO Template:</th>
                                    <td id="review_template">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Import Summary</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="140">Import Method:</th>
                                    <td id="review_method">-</td>
                                </tr>
                                <tr>
                                    <th>Students:</th>
                                    <td id="review_students" class="text-success fw-bold">-</td>
                                </tr>
                                <tr>
                                    <th>Subjects:</th>
                                    <td id="review_subjects" class="text-info fw-bold">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-success border-0 mt-3">
                        <div class="form-check">
                            <input type="checkbox" name="apply_immediately" id="apply_immediately" 
                                   class="form-check-input" value="1" checked>
                            <label class="form-check-label fw-bold" for="apply_immediately">
                                <i class="bi bi-lightning-charge"></i> Apply configuration immediately to all selected subjects
                            </label>
                            <small class="d-block text-muted mt-1">
                                This will create course outcomes and import students to all selected subjects automatically. 
                                If unchecked, you can apply configuration later individually.
                            </small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(3)">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-rocket-takeoff me-2"></i>Create Batch Draft
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* Premium Gradient */
.bg-gradient {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

/* Progress Line */
.progress-line {
    position: absolute;
    top: 35px;
    left: 20%;
    width: 60%;
    height: 3px;
    background: linear-gradient(90deg, #e9ecef 0%, #e9ecef 100%);
    z-index: 0;
}

/* Wizard Steps */
.wizard-step {
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

.wizard-step .step-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    background: white;
    border: 4px solid #dee2e6;
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.wizard-step .step-circle i {
    font-size: 1.75rem;
    color: #adb5bd;
    transition: all 0.3s;
}

.wizard-step .step-circle .step-number {
    position: absolute;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6c757d;
    bottom: -5px;
    right: -5px;
    background: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #dee2e6;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.wizard-step.active .step-circle {
    border-color: #10b981;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
    transform: scale(1.1);
}

.wizard-step.active .step-circle i {
    color: white;
}

.wizard-step.active .step-circle .step-number {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.wizard-step .step-label {
    font-size: 0.875rem;
    color: #6c757d;
    transition: all 0.3s;
}

.wizard-step.active .step-label {
    color: #10b981;
}

/* Import Method Cards */
.import-method-card {
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-color: #e9ecef !important;
    position: relative;
    overflow: hidden;
}

.import-method-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.1), transparent);
    transition: left 0.6s;
}

.import-method-card:hover::before {
    left: 100%;
}

.import-method-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    border-color: #10b981 !important;
}

.import-method-card.selected {
    border-color: #10b981 !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.08) 100%);
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.2);
}

.import-method-card .icon-wrapper {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.import-method-card:hover .icon-wrapper,
.import-method-card.selected .icon-wrapper {
    transform: scale(1.15) rotate(5deg);
}

/* Subject Cards */
.subject-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid #e9ecef !important;
}

.subject-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-color: #10b981 !important;
}

.subject-card.selected {
    border-color: #10b981 !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.08) 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.subject-checkbox {
    cursor: pointer;
}

.form-check-label {
    user-select: none;
}

/* Premium Buttons */
.btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    padding: 0.625rem 1.25rem;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-outline-secondary {
    border-width: 2px;
}

.btn-outline-secondary:hover {
    transform: translateY(-2px);
    background: #6c757d;
    border-color: #6c757d;
}

/* Form Controls */
.form-control,
.form-select {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
    padding: 0.625rem 1rem;
    transition: all 0.3s;
}

.form-control:focus,
.form-select:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
}

/* Cards */
.card {
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.card-header {
    border-radius: 1rem 1rem 0 0 !important;
}

/* Smooth Animations */
.wizard-content {
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.subject-card.selected {
    border-color: var(--theme-green) !important;
    background-color: rgba(25, 135, 84, 0.05);
}
</style>

<script>
let currentStep = 1;

// Auto-generate batch name
const courseField = document.getElementById('course_id');
if (courseField.tagName === 'SELECT') {
    courseField.addEventListener('change', updateNameSuggestion);
}
document.getElementById('year_level').addEventListener('change', updateNameSuggestion);

function updateNameSuggestion() {
    const courseField = document.getElementById('course_id');
    const yearLevel = document.getElementById('year_level').value;
    let courseCode = null;
    
    // Handle both select dropdown and hidden input
    if (courseField.tagName === 'SELECT') {
        const selectedOption = courseField.options[courseField.selectedIndex];
        courseCode = selectedOption.getAttribute('data-code');
    } else if (courseField.tagName === 'INPUT') {
        courseCode = courseField.getAttribute('data-code');
    }
    
    if (courseCode && yearLevel) {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const semester = month >= 6 && month <= 10 ? '1st Sem' : '2nd Sem';
        const suggestion = `${courseCode} Y${yearLevel} ${semester} ${year}`;
        document.getElementById('nameSuggestion').textContent = suggestion;
        
        // Auto-fill if empty
        if (!document.getElementById('batch_name').value) {
            document.getElementById('batch_name').value = suggestion;
        }
    }
}

// Step navigation
function nextStep(step) {
    if (!validateStep(currentStep)) {
        return;
    }
    
    document.getElementById('step' + currentStep).classList.add('d-none');
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.remove('active');
    
    currentStep = step;
    
    document.getElementById('step' + currentStep).classList.remove('d-none');
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');
    
    if (step === 3) {
        // Auto-load subjects when entering step 3
        const courseId = document.getElementById('course_id').value;
        const yearLevel = document.getElementById('year_level').value;
        if (courseId && document.getElementById('subjects_container').children.length === 1) {
            // Only auto-load if subjects haven't been loaded yet
            loadSubjects();
        }
    }
    
    if (step === 4) {
        updateReview();
    }
    
    window.scrollTo(0, 0);
}

function prevStep(step) {
    document.getElementById('step' + currentStep).classList.add('d-none');
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.remove('active');
    
    currentStep = step;
    
    document.getElementById('step' + currentStep).classList.remove('d-none');
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');
    
    window.scrollTo(0, 0);
}

function validateStep(step) {
    if (step === 1) {
        if (!document.getElementById('batch_name').value) {
            alert('Please enter a batch name');
            return false;
        }
        if (!document.getElementById('course_id').value) {
            alert('Please select a course');
            return false;
        }
        if (!document.getElementById('year_level').value) {
            alert('Please select a year level');
            return false;
        }
        if (!document.getElementById('co_template_id').value) {
            alert('Please select a CO template');
            return false;
        }
    }
    
    if (step === 2) {
        const method = document.getElementById('student_import_method').value;
        if (method === 'file' && !document.getElementById('students_file').value) {
            alert('Please select a file to upload');
            return false;
        }
        if (method === 'paste' && !document.getElementById('students_paste').value) {
            alert('Please paste student data');
            return false;
        }
        if (method === 'previous_batch' && !document.getElementById('previous_batch_id').value) {
            alert('Please select a previous batch');
            return false;
        }
    }
    
    return true;
}

// Import method selection
document.querySelectorAll('.import-method-card').forEach(card => {
    card.addEventListener('click', function() {
        const method = this.getAttribute('data-method');
        
        // Update selected state
        document.querySelectorAll('.import-method-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        
        // Update hidden input
        document.getElementById('student_import_method').value = method;
        
        // Show/hide sections
        document.querySelectorAll('.import-section').forEach(s => s.classList.add('d-none'));
        document.getElementById('import_' + method + '_section').classList.remove('d-none');
    });
});

// Previous batch selection
document.getElementById('previous_batch_id').addEventListener('change', function() {
    const count = this.options[this.selectedIndex].getAttribute('data-students');
    if (count) {
        document.getElementById('previous_student_count').textContent = count;
        document.getElementById('previous_batch_info').classList.remove('d-none');
    } else {
        document.getElementById('previous_batch_info').classList.add('d-none');
    }
});

// CSV File Preview
document.getElementById('students_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewDiv = document.getElementById('file_preview');
    
    if (!file) {
        previewDiv.innerHTML = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(event) {
        const text = event.target.result;
        const lines = text.trim().split('\n').filter(l => l.trim());
        
        if (lines.length === 0) {
            previewDiv.innerHTML = '<div class="alert alert-warning">File appears to be empty</div>';
            return;
        }
        
        // Parse header and first 5 data rows
        const header = lines[0].split(',').map(h => h.trim());
        const dataRows = lines.slice(1, 6);
        const totalCount = lines.length - 1; // Exclude header
        
        let html = `
            <div class="card border-success mt-2">
                <div class="card-body p-3">
                    <h6 class="card-title text-success mb-2">
                        <i class="bi bi-file-earmark-check"></i> File Preview
                        <span class="badge bg-success float-end">${totalCount} student${totalCount !== 1 ? 's' : ''}</span>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>`;
        
        header.forEach(col => {
            html += `<th class="small">${col}</th>`;
        });
        
        html += `</tr></thead><tbody>`;
        
        dataRows.forEach(row => {
            const cells = row.split(',').map(c => c.trim());
            html += '<tr>';
            cells.forEach(cell => {
                html += `<td class="small">${cell}</td>`;
            });
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        
        if (totalCount > 5) {
            html += `<p class="text-muted small mb-0 mt-2">Showing first 5 of ${totalCount} students</p>`;
        }
        
        html += '</div></div>';
        previewDiv.innerHTML = html;
    };
    
    reader.onerror = function() {
        previewDiv.innerHTML = '<div class="alert alert-danger">Error reading file</div>';
    };
    
    reader.readAsText(file);
});

// Preview pasted data
function previewPastedData() {
    const data = document.getElementById('students_paste').value;
    if (!data) {
        alert('Please paste some data first');
        return;
    }
    
    const lines = data.trim().split('\n').filter(l => l.trim());
    const count = lines.length - (lines[0].toLowerCase().includes('name') ? 1 : 0);
    
    document.getElementById('paste_preview_count').innerHTML = 
        `<i class="bi bi-check-circle"></i> Detected approximately <strong>${count}</strong> students`;
}

// Load subjects
function loadSubjects() {
    const courseId = document.getElementById('course_id').value;
    const yearLevel = document.getElementById('year_level').value;
    
    if (!courseId) {
        alert('Please select a course first');
        return;
    }
    
    document.getElementById('subjects_loading').classList.remove('d-none');
    document.getElementById('subjects_container').innerHTML = '';
    
    fetch(`{{ route('chairperson.batch-drafts.ajax.subjects') }}?course_id=${courseId}&year_level=${yearLevel}`)
        .then(response => response.json())
        .then(subjects => {
            document.getElementById('subjects_loading').classList.add('d-none');
            
            if (subjects.length === 0) {
                document.getElementById('subjects_container').innerHTML = `
                    <div class="col-12 text-center text-muted py-4">
                        <i class="bi bi-info-circle fs-3"></i>
                        <p class="mt-2">No subjects found for this course and year level</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            subjects.forEach(subject => {
                const badge = subject.has_batch_draft 
                    ? '<span class="badge bg-success">Configured</span>' 
                    : '<span class="badge bg-secondary">Not Configured</span>';
                
                html += `
                    <div class="col-md-6">
                        <div class="card subject-card border-2">
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="checkbox" name="subject_ids[]" value="${subject.id}" 
                                           class="form-check-input subject-checkbox" id="subject_${subject.id}"
                                           onchange="this.closest('.subject-card').classList.toggle('selected', this.checked)">
                                    <label class="form-check-label w-100" for="subject_${subject.id}" style="cursor: pointer;">
                                        <strong>${subject.subject_code}</strong> - ${subject.subject_name}
                                        <div class="mt-1">${badge}</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            document.getElementById('subjects_container').innerHTML = html;
            
            // Auto-select all subjects by default for easier workflow
            document.querySelectorAll('.subject-checkbox').forEach(checkbox => {
                checkbox.checked = true;
                checkbox.closest('.subject-card').classList.add('selected');
            });
        })
        .catch(error => {
            document.getElementById('subjects_loading').classList.add('d-none');
            alert('Error loading subjects: ' + error.message);
        });
}

function selectAllSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = true;
        cb.closest('.subject-card').classList.add('selected');
    });
}

function clearSubjects() {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('.subject-card').classList.remove('selected');
    });
}

// Update review
function updateReview() {
    // Basic info
    document.getElementById('review_batch_name').textContent = document.getElementById('batch_name').value;
    
    // Handle course - check if it's a hidden input (chairperson) or select
    const courseInput = document.getElementById('course_id');
    let courseText = '';
    if (courseInput.tagName === 'INPUT') {
        // Hidden input for chairperson - get text from display field
        const displayField = courseInput.parentElement.querySelector('.form-control.bg-light');
        courseText = displayField ? displayField.textContent.trim() : courseInput.value;
    } else {
        // Select dropdown
        courseText = courseInput.options[courseInput.selectedIndex].text;
    }
    document.getElementById('review_course').textContent = courseText;
    
    const yearLevel = document.getElementById('year_level').value;
    document.getElementById('review_year').textContent = yearLevel ? 'Year ' + yearLevel : '-';
    
    const templateSelect = document.getElementById('co_template_id');
    const templateText = templateSelect.selectedIndex > 0 
        ? templateSelect.options[templateSelect.selectedIndex].text 
        : '-';
    document.getElementById('review_template').textContent = templateText;
    
    // Import info
    const method = document.getElementById('student_import_method').value;
    let methodText = '-';
    let studentCount = '-';
    
    if (method === 'file') {
        const file = document.getElementById('students_file').files[0];
        methodText = 'Upload File';
        studentCount = file ? file.name : 'No file selected';
    } else if (method === 'paste') {
        methodText = 'Copy-Paste';
        const data = document.getElementById('students_paste').value;
        if (data.trim()) {
            const lines = data.trim().split('\n').filter(l => l.trim());
            studentCount = lines.length - (lines[0].toLowerCase().includes('name') ? 1 : 0) + ' students';
        } else {
            studentCount = 'No data pasted';
        }
    } else if (method === 'previous_batch') {
        methodText = 'From Previous Batch';
        const select = document.getElementById('previous_batch_id');
        if (select.selectedIndex > 0) {
            studentCount = select.options[select.selectedIndex].getAttribute('data-students') + ' students';
        } else {
            studentCount = 'No batch selected';
        }
    }
    
    document.getElementById('review_method').textContent = methodText;
    document.getElementById('review_students').textContent = studentCount;
    
    const selectedSubjects = document.querySelectorAll('.subject-checkbox:checked').length;
    document.getElementById('review_subjects').textContent = 
        selectedSubjects > 0 ? selectedSubjects + ' subjects selected' : 'None (can add later)';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set first import method as selected
    document.querySelector('.import-method-card[data-method="file"]').classList.add('selected');
    
    // Update name suggestion if values exist
    updateNameSuggestion();
});
</script>
@endsection
