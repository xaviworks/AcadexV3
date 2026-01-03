@extends('layouts.app')

@section('content')
@php
    $queryParams = array_filter([
        'academic_year' => $selectedAcademicYear,
        'academic_period_id' => $selectedAcademicPeriodId,
        'semester' => $semester,
    ], function ($value) {
        return $value !== null && $value !== '';
    });

    $buildRoute = function (string $name, array $parameters = []) use ($queryParams) {
        $url = route($name, $parameters);

        if (empty($queryParams)) {
            return $url;
        }

        return $url . '?' . http_build_query($queryParams);
    };
@endphp
<div class="container-fluid px-3 py-3 bg-gradient-light min-vh-100">
    <div class="row mb-2">
        <div class="col">
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb bg-white rounded-pill px-3 py-1 shadow-sm mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none link-success-green text-sm">
                            <i class="bi bi-house-door me-1"></i>Home
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ $buildRoute('admin.gradesFormula') }}" class="text-decoration-none link-success-green text-sm">
                            <i class="bi bi-sliders me-1"></i>Grades Formula
                        </a>
                    </li>
                    @if($department)
                        <li class="breadcrumb-item">
                            <a href="{{ $buildRoute('admin.gradesFormula.department', ['department' => $department->id]) }}" class="text-decoration-none link-success-green text-sm">
                                {{ $department->department_code }} Department
                            </a>
                        </li>
                    @endif
                    @if($course)
                        <li class="breadcrumb-item">
                            <a href="{{ $buildRoute('admin.gradesFormula.course', ['department' => $department->id, 'course' => $course->id]) }}" class="text-decoration-none link-success-green text-sm">
                                {{ $course->course_code }} Course
                            </a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active text-muted-gray text-sm" aria-current="page">
                        {{ $subject->subject_code }} Subject
                    </li>
                </ol>
            </nav>

            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded-circle me-2 bg-gradient-green">
                        <i class="bi bi-journal-text text-white icon-lg"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-primary-green">
                            {{ $subject->subject_code }} · {{ $subject->subject_description }}
                        </h4>
                        <small class="text-muted">
                            Inspect formulas across all levels before editing this subject's grading scale.
                        </small>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ $buildRoute('admin.gradesFormula.edit.subject', ['subject' => $subject->id]) }}" class="btn btn-success btn-sm rounded-pill shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i>{{ $subjectFormula ? 'Edit Subject Formula' : 'Create Subject Formula' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <form method="GET" action="{{ route('admin.gradesFormula.subject', ['subject' => $subject->id]) }}" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex flex-column">
                <label class="text-success small mb-1">Academic Year</label>
                <select name="academic_year" class="form-select form-select-sm max-w-180" onchange="this.form.submit()">
                    <option value="" {{ $selectedAcademicYear ? '' : 'selected' }}>All Years</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year }}" {{ $selectedAcademicYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex flex-column">
                <label class="text-success small mb-1">Semester</label>
                <select name="semester" class="form-select form-select-sm max-w-150" onchange="this.form.submit()">
                    <option value="" {{ $semester ? '' : 'selected' }}>All/Default</option>
                    @foreach($availableSemesters as $availableSemester)
                        <option value="{{ $availableSemester }}" {{ $semester === $availableSemester ? 'selected' : '' }}>{{ $availableSemester }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if (session('success'))
        <script>notify.success('{{ session('success') }}');</script>
    @endif

    @php
        $requiresPasswordPrompt = ($requiresPasswordConfirmation ?? false) === true;
        $passwordErrorMessage = $requiresPasswordPrompt ? ($errors->first('current_password') ?? '') : '';
    @endphp

    @if ($errors->has('structure_type') || $errors->has('department_formula_id'))
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first('structure_type') ?? $errors->first('department_formula_id') }}
        </div>
    @elseif ($passwordErrorMessage)
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $passwordErrorMessage }}
        </div>
    @endif

    @php
        $subjectName = trim(($subject->subject_code ? $subject->subject_code . ' - ' : '') . ($subject->subject_description ?? ''));
        if ($subjectName === '') {
            $subjectName = 'this subject';
        }

        $courseName = $course
            ? trim(($course->course_code ? $course->course_code . ' - ' : '') . ($course->course_description ?? ''))
            : '';
        if ($courseName === '') {
            $courseName = 'this course';
        }

        $departmentName = $department
            ? trim(($department->department_code ? $department->department_code . ' - ' : '') . ($department->department_description ?? ''))
            : '';
        if ($departmentName === '') {
            $departmentName = 'this department';
        }

        $activeLabel = $activeMeta['label'] ?? ($subjectFormula?->label ?? 'ASBME Default');
        $activeScopeLabel = match ($activeScope ?? 'default') {
            'subject' => 'Subject Custom Formula',
            'course' => 'Inherits Course Formula',
            'department' => 'Inherits Department Formula',
            default => 'System Default Formula',
        };

        $activeWeights = collect($activeMeta['relative_weights'] ?? $activeMeta['weights'] ?? [])
            ->map(function ($weight, $type) {
                $numeric = is_numeric($weight) ? (float) $weight : 0;
                $clamped = max(min($numeric, 100), 0);

                return [
                    'type' => strtoupper($type),
                    'percent' => $clamped,
                    'display' => number_format($clamped, 0),
                    'progress' => $clamped / 100,
                ];
            })
            ->values();

        $manageHeadline = $subjectFormula ? 'Fine-tune this subject’s grading scale.' : 'Give this subject its own grading scale.';
        $manageCopy = $subjectFormula
            ? 'Adjust weights, base score, and passing mark to reflect unique assessment plans for this subject.'
            : 'Start with department or course guidance, then tailor the weights and scaling just for this subject.';
        $manageCta = $subjectFormula ? 'Edit Subject Formula' : 'Create Subject Formula';
        $hasSubjectFormula = (bool) $subjectFormula;

        $structureOptions = collect($structureOptions ?? [])
            ->map(function ($option) {
                $key = $option['key'] ?? 'lecture_only';
                $label = $option['label'] ?? \Illuminate\Support\Str::of($key)->replace('_', ' ')->title()->toString();

                return [
                    'key' => $key,
                    'label' => $label,
                ];
            })
            ->values();
        $structureOptionCount = $structureOptions->count();
        $structureBlueprints = collect($structureBlueprints ?? []);
        $selectedStructureType = $selectedStructureType ?? 'lecture_only';
    @endphp

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between flex-wrap gap-3 align-items-start">
                        <div>
                            <h5 class="text-success fw-semibold mb-1">Current Formula</h5>
                            <p class="text-muted mb-0">{{ $activeScopeLabel }} powering {{ $subjectName }}.</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-success fw-semibold">{{ $activeLabel }}</span>
                            <div class="small text-muted mt-1">Base {{ number_format($activeMeta['base_score'] ?? $subjectFormula?->base_score ?? 0, 0) }} · Scale ×{{ number_format($activeMeta['scale_multiplier'] ?? $subjectFormula?->scale_multiplier ?? 0, 0) }} · Passing {{ number_format($activeMeta['passing_grade'] ?? $subjectFormula?->passing_grade ?? 0, 0) }}</div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($activeWeights as $weight)
                            <span class="formula-weight-chip" style="--chip-progress: {{ number_format($weight['progress'], 2, '.', '') }};">
                                <span>{{ $weight['type'] }} {{ $weight['display'] }}%</span>
                            </span>
                        @endforeach
                        @if ($activeWeights->isEmpty())
                            <span class="text-muted small">No activity weights defined.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="text-success fw-semibold mb-1">Choose Department Formula</h5>
                    <p class="text-muted mb-0">Department formulas replace the old department baselines. Pick one to baseline {{ $subjectName }} and refine a subject-specific override afterward.</p>
                </div>
                <div class="card-body">
                    <form
                        id="subject-formula-apply-form"
                        method="POST"
                        action="{{ $buildRoute('admin.gradesFormula.subject.apply', ['subject' => $subject->id]) }}"
                        data-has-subject-formula="{{ $hasSubjectFormula ? '1' : '0' }}"
                        data-subject-name="{{ $subjectName }}"
                        data-requires-password="{{ $requiresPasswordPrompt ? '1' : '0' }}"
                        data-password-error="{{ $passwordErrorMessage ? '1' : '0' }}"
                        data-password-error-message="{{ $passwordErrorMessage ? e($passwordErrorMessage) : '' }}"
                    >
                        @csrf
                        @if ($requiresPasswordPrompt)
                            <input type="hidden" name="current_password" id="subjectFormulaPasswordField">
                        @endif
                        @if ($structureBlueprints->isEmpty())
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle me-2"></i>No structure templates available yet. Configure templates before applying them to subjects.
                            </div>
                        @else
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @if ($hasSubjectFormula)
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 shadow-sm-sm">
                                            <i class="bi bi-patch-check-fill me-1"></i>Custom subject formula active
                                        </span>
                                        <span class="text-muted small">Applying a structure template will replace the current override.</span>
                                    @else
                                        <span class="badge bg-light text-success rounded-pill px-3 py-2 shadow-sm-sm">
                                            <i class="bi bi-brush me-1"></i>No subject override yet
                                        </span>
                                        <span class="text-muted small">Pick a structure template to jump-start this subject.</span>
                                    @endif
                                </div>
                                @if ($hasSubjectFormula)
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#removeSubjectFormulaModal">
                                        <i class="bi bi-trash me-1"></i>Remove Subject Formula
                                    </button>
                                @endif
                            </div>

                            @if ($hasSubjectFormula)
                                <div class="alert alert-warning d-flex align-items-start shadow-sm formula-alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                                    <div>
                                        This subject already has a custom formula. Applying a structure template will replace the current subject override.
                                    </div>
                                </div>
                                @if ($requiresPasswordPrompt)
                                    <div class="alert alert-warning border-0 shadow-sm-sm d-flex align-items-start gap-2 mb-3">
                                        <i class="bi bi-lock-fill mt-1"></i>
                                        <div>{{ $subjectName }} already has recorded grades. Confirm your password before applying a new structure template.</div>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-success d-flex align-items-start shadow-sm formula-alert">
                                    <i class="bi bi-lightning-charge me-2 mt-1"></i>
                                    <div>
                                        Start with a structure template, then fine-tune a subject-specific formula to match unique assessments.
                                    </div>
                                </div>
                            @endif

                            @php
                                $selectedStructureKey = old('structure_type', $selectedStructureType);
                            @endphp

                            @if ($structureOptionCount > 0)
                                <div class="row g-3 align-items-end mb-3">
                                    <div class="col-sm-6 col-md-4 col-lg-3">
                                        <label for="department-structure-filter" class="form-label text-success fw-semibold small mb-1">Department Formula</label>
                                        <select class="form-select form-select-sm" id="department-structure-filter">
                                            <option value="all" selected>All Structures</option>
                                            @foreach ($structureOptions as $option)
                                                <option value="{{ $option['key'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="row g-4 formula-option-grid">
                                @foreach ($structureBlueprints as $blueprint)
                                    @php
                                        $inputId = 'structure-type-' . $blueprint['key'];
                                        $weights = collect($blueprint['weights']);
                                        $isSelected = $selectedStructureKey === $blueprint['key'];
                                        $structureTypeKey = $blueprint['key'];
                                        $structureTypeLabel = $blueprint['label'];
                                        $structureTypeDescription = $blueprint['description'] ?? '';
                                    @endphp
                                    <div class="col-xl-4 col-lg-6 formula-card-column" data-structure-type="{{ $structureTypeKey }}">
                                        <label class="w-100 formula-option-wrapper">
                                            <input
                                                type="radio"
                                                id="{{ $inputId }}"
                                                name="structure_type"
                                                value="{{ $structureTypeKey }}"
                                                class="form-check-input formula-option-input"
                                                @checked($isSelected)
                                            >
                                            <div class="formula-option-card position-relative h-100 p-4">
                                                <div class="formula-card-glow" aria-hidden="true"></div>
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="fw-semibold text-success mb-1">{{ $structureTypeLabel }}</h6>
                                                        <p class="small text-muted mb-0">
                                                            Base {{ number_format($blueprint['base_score'], 0) }} · Scale ×{{ number_format($blueprint['scale_multiplier'], 0) }} · Passing {{ number_format($blueprint['passing_grade'], 0) }}
                                                        </p>
                                                    </div>
                                                    <div class="d-flex flex-column align-items-end gap-1 text-end">
                                                        <span class="badge bg-white text-success border border-success border-opacity-25 rounded-pill">{{ $structureTypeLabel }}</span>
                                                        @if (! empty($blueprint['is_baseline']))
                                                            <span class="badge bg-success-subtle text-success rounded-pill">Department Baseline</span>
                                                        @else
                                                            <span class="badge bg-light text-secondary rounded-pill">Structure Template</span>
                                                        @endif
                                                        <span class="small text-muted">Matches {{ $structureTypeLabel }} blueprint</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($weights as $weight)
                                                        <span class="formula-weight-chip" style="--chip-progress: {{ number_format($weight['progress'], 2, '.', '') }};">
                                                            <span>{{ $weight['type'] }} {{ $weight['display'] }}%</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                                @if ($structureTypeDescription)
                                                    <p class="text-muted small mt-3 mb-0">{{ $structureTypeDescription }}</p>
                                                @endif
                                                <div class="formula-card-footer small text-muted d-flex flex-wrap gap-3 mt-4">
                                                    <span><i class="bi bi-speedometer2 text-success me-1"></i>Base {{ number_format($blueprint['base_score'], 0) }}</span>
                                                    <span><i class="bi bi-diagram-3 text-success me-1"></i>Scale ×{{ number_format($blueprint['scale_multiplier'], 0) }}</span>
                                                    <span><i class="bi bi-mortarboard text-success me-1"></i>Passing {{ number_format($blueprint['passing_grade'], 0) }}</span>
                                                </div>
                                                <div class="formula-check" aria-hidden="true">
                                                    <i class="bi bi-check-lg"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($structureBlueprints->isNotEmpty())
                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                                <small class="text-muted">Need unique weights? Create a subject formula after applying a template.</small>
                                <button type="submit" class="btn btn-success btn-apply-formula" data-action="apply">Apply Structure</button>
                            </div>
                            @if ($requiresPasswordPrompt)
                                <div class="alert alert-danger mt-3 mb-0 d-none" id="subjectFormulaPasswordServerError"></div>
                            @endif
                        @endif
                    </form>
                    @if ($requiresPasswordPrompt)
                        <div class="modal fade" id="subjectFormulaPasswordModal" tabindex="-1" aria-labelledby="subjectFormulaPasswordModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-sm">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title" id="subjectFormulaPasswordModalLabel"><i class="bi bi-lock-fill me-2"></i>Confirm Sensitive Change</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning border-0 shadow-sm-sm mb-3 white-space-pre-wrap text-sm">
Choose Department Formula
Department formulas replace the old department baselines. Pick one to baseline {{ $subjectName }} and refine a subject-specific override afterward.

Custom subject formula active
Applying a structure template will replace the current override.
This subject already has a custom formula. Applying a structure template will replace the current subject override.
                                        </div>
                                        <p class="text-muted">{{ $subjectName }} already has recorded grades. Enter your password to continue.</p>
                                        <div class="mb-3">
                                            <label for="subjectFormulaPasswordInput" class="form-label fw-semibold">Account Password</label>
                                            <input type="password" class="form-control" id="subjectFormulaPasswordInput" autocomplete="current-password" placeholder="Enter your password">
                                            <div class="invalid-feedback d-none" id="subjectFormulaPasswordInlineError"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-success" id="subjectFormulaPasswordConfirmBtn">Confirm &amp; Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($hasSubjectFormula)
                        <div class="modal fade" id="removeSubjectFormulaModal" tabindex="-1" aria-labelledby="removeSubjectFormulaModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title text-success" id="removeSubjectFormulaModalLabel">Remove Subject Formula</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-0">Removing the custom formula will restore {{ $subjectName }} to {{ $departmentName }}’s baseline. You can always create a new subject formula afterward.</p>
                                    </div>
                                    <div class="modal-footer border-0 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form method="POST" action="{{ $buildRoute('admin.gradesFormula.subject.remove', ['subject' => $subject->id]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-trash me-1"></i>Remove Formula
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h5 class="text-success fw-semibold mb-1">Add or Edit Subject Formula</h5>
                        <p class="text-muted mb-0">{{ $manageHeadline }} {{ $manageCopy }}</p>
                    </div>
                    <a href="{{ $buildRoute('admin.gradesFormula.edit.subject', ['subject' => $subject->id]) }}" class="btn btn-outline-success px-4">{{ $manageCta }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript moved to: resources/js/pages/admin/grades-formula-subject.js --}}

{{-- Styles: resources/css/admin/grades-formula.css --}}
@push('styles')

@endpush
