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
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-building-fill text-success me-2"></i>{{ $department->department_code }} Department</h1>
            <p class="text-muted mb-0">{{ $department->department_description }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $buildRoute('admin.gradesFormula', ['view' => 'overview']) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <a href="{{ $buildRoute('admin.gradesFormula.edit.department', ['department' => $department->id]) }}" class="btn btn-success btn-sm">
                <i class="bi bi-pencil-square me-1"></i>{{ $needsDepartmentFormula ? 'Create Formula' : 'Edit Formula' }}
            </a>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
    <form method="GET" action="{{ route('admin.gradesFormula.department', ['department' => $department->id]) }}" class="d-flex align-items-center gap-2 flex-wrap">
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

    @php
        $totalCourses = $courseSummaries->count();
        $customCourses = $courseSummaries->filter(fn ($summary) => $summary['has_formula'])->count();
        $defaultCourses = max($totalCourses - $customCourses, 0);
        $fallbackLabel = $departmentFallback->label ?? $globalFormula->label ?? 'Baseline Formula';
        $catalogTotal = $departmentFormulas->count();
    @endphp

    <div class="card border-0 shadow-sm mb-3 bg-success bg-opacity-10">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-1 fw-bold text-success"><i class="bi bi-collection me-2"></i>Course Wildcards Overview</h6>
                    <small class="text-muted">{{ $totalCourses }} courses · {{ $customCourses }} subject overrides · {{ $defaultCourses }} using baseline</small>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <span class="badge bg-success text-white px-3 py-2">
                        <i class="bi bi-lightbulb me-1"></i>Baseline: {{ $fallbackLabel }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-filter text-success"></i>
                    <span class="fw-semibold text-success">Filter wildcards</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-success btn-sm rounded-pill wildcard-filter-btn active" data-filter="all">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i>All
                        <span class="badge bg-white text-success ms-1">{{ $totalCourses }}</span>
                    </button>
                    <button class="btn btn-outline-success btn-sm rounded-pill wildcard-filter-btn" data-filter="custom">
                        <i class="bi bi-star-fill me-1"></i>Formulas
                        <span class="badge bg-success text-white ms-1">{{ $catalogTotal }}</span>
                    </button>
                    <button class="btn btn-outline-success btn-sm rounded-pill wildcard-filter-btn" data-filter="default">
                        <i class="bi bi-shield-check me-1"></i>Subjects
                        <span class="badge bg-success text-white ms-1">{{ $defaultCourses }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 d-none" id="department-formula-catalog" data-wildcard-section="catalog">
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                    <div>
                        <h5 class="fw-semibold mb-1 text-primary-green">Department Formula Catalog</h5>
                        <p class="text-muted mb-0">Start subjects from a consistent baseline or tailor alternatives for special cases.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $buildRoute('admin.gradesFormula.department.formulas.create', ['department' => $department->id]) }}" class="btn btn-success btn-sm rounded-pill shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i>Create Catalog Formula
                        </a>
                        <a href="{{ $buildRoute('admin.gradesFormula.edit.department', ['department' => $department->id]) }}" class="btn btn-outline-success btn-sm rounded-pill shadow-sm">
                            <i class="bi bi-pencil-square me-1"></i>Edit Fallback Formula
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="wildcard-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden bg-success bg-opacity-10" data-status="catalog" data-url="{{ $buildRoute('admin.gradesFormula.department.formulas.create', ['department' => $department->id]) }}">
                <div class="position-relative header-height-80 bg-gradient-green"></div>
                <div class="wildcard-circle bg-gradient-green-dark">
                    <i class="bi bi-plus-lg text-white icon-lg"></i>
                </div>
                    <div class="card-body pt-5 text-center d-flex flex-column align-items-center gap-3">
                    <div>
                        <h6 class="fw-semibold wildcard-title mt-2 text-dark">Create New Catalog Formula</h6>
                        <p class="text-muted small mb-0">Define reusable weightings for instructors to apply across subjects.</p>
                    </div>
                    {{-- Bottom badge intentionally removed to avoid empty placeholder --}}
                </div>
            </div>
        </div>

        @foreach($departmentFormulas as $formula)
            @php
                $weights = collect($formula->weight_map)
                    ->map(fn ($weight, $type) => [
                        'type' => strtoupper($type),
                        'percent' => number_format($weight * 100, 0),
                    ])
                    ->values();
                $isFallback = (bool) $formula->is_department_fallback;
                                $editRoute = $isFallback
                                    ? $buildRoute('admin.gradesFormula.edit.department', ['department' => $department->id])
                                    : $buildRoute('admin.gradesFormula.department.formulas.edit', ['department' => $department->id, 'formula' => $formula->id]);
            @endphp
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="wildcard-card card h-100 border-0 shadow-sm formula-card" data-status="catalog" data-url="{{ $editRoute }}">
                    <div class="card-header {{ $isFallback ? 'bg-success' : 'bg-secondary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">{{ $isFallback ? 'FALLBACK' : 'CATALOG' }}</h6>
                            <span class="badge bg-white text-dark">{{ $isFallback ? 'Baseline' : 'Custom' }}</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div>
                            <h6 class="fw-semibold wildcard-title text-dark mb-1" title="{{ $formula->label }}">{{ $formula->label }}</h6>
                            <p class="text-muted small mb-1">Base {{ number_format($formula->base_score, 0) }} · Scale ×{{ number_format($formula->scale_multiplier, 0) }} · Passing {{ number_format($formula->passing_grade, 0) }}</p>
                            <div class="text-muted small">Updated {{ $formula->updated_at?->diffForHumans() ?? 'n/a' }}</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($weights as $weight)
                                <span class="badge bg-success-subtle text-success">{{ $weight['type'] }} {{ $weight['percent'] }}%</span>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ $editRoute }}" class="btn btn-outline-success btn-sm rounded-pill">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </a>
                            @unless($isFallback)
                                    <form action="{{ $buildRoute('admin.gradesFormula.department.formulas.destroy', ['department' => $department->id, 'formula' => $formula->id]) }}" method="POST" onsubmit="return confirm('Delete this formula? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($courseSummaries->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="text-muted mb-3">
                    <i class="bi bi-journal-minus fs-1 opacity-50"></i>
                </div>
                <h5 class="text-muted mb-2">No courses found</h5>
                <p class="text-muted mb-0">Add courses to this department to configure course-level formulas.</p>
            </div>
        </div>
    @else
        <div class="row g-4" id="course-wildcards" data-wildcard-section="courses">
            @foreach($courseSummaries as $summary)
                @php
                    $course = $summary['course'];
                @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <a href="{{ $buildRoute('admin.gradesFormula.course', ['department' => $department->id, 'course' => $course->id]) }}" class="text-decoration-none text-reset">
                        <div class="wildcard-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden cursor-pointer transition-transform-shadow" data-status="{{ $summary['status'] }}" data-url="{{ $buildRoute('admin.gradesFormula.course', ['department' => $department->id, 'course' => $course->id]) }}">
                            {{-- Top header --}}
                            <div class="position-relative header-height-80 bg-gradient-green-soft">
                                <div class="wildcard-circle-positioned">
                                    <span class="text-white fw-bold">{{ $course->course_code }}</span>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="card-body pt-5 text-center">
                                <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $course->course_description }}">
                                    {{ $course->course_description }}
                                </h6>
                                <p class="text-muted small mb-3">{{ $summary['scope_text'] }}</p>

                                {{-- Footer badges --}}
                                <div class="d-flex flex-column gap-2 mt-4">
                                    @if($summary['missing_subject_count'] > 0)
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $summary['missing_subject_count'] }} subject{{ $summary['missing_subject_count'] === 1 ? '' : 's' }} pending
                                        </span>
                                    @endif
                                    <span class="badge px-3 py-2 fw-semibold rounded-pill {{ $summary['has_formula'] ? 'bg-success' : 'bg-secondary' }}">
                                        @if($summary['has_formula'])
                                            ✓ {{ $summary['formula_scope'] }}
                                        @else
                                            {{ $summary['formula_scope'] }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

{{-- JavaScript moved to: resources/js/pages/admin/grades-formula-department.js --}}

{{-- Styles: resources/css/admin/grades-formula.css --}}
@push('styles')
@endpush
