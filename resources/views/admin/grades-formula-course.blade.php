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
                    <li class="breadcrumb-item">
                        <a href="{{ $buildRoute('admin.gradesFormula.department', ['department' => $department->id]) }}" class="text-decoration-none link-success-green text-sm">
                            {{ $department->department_code }} Department
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-muted-gray text-sm" aria-current="page">
                        {{ $course->course_code }} Course
                    </li>
                </ol>
            </nav>

            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded-circle me-2 bg-gradient-green">
                        <i class="bi bi-journal-check text-white icon-lg"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-primary-green">
                            {{ $course->course_code }} · {{ $course->course_description }}
                        </h4>
                        <small class="text-muted">
                            Review course formula and drill down into subject weighting.
                        </small>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ $buildRoute('admin.gradesFormula.department', ['department' => $department->id]) }}" class="btn btn-outline-success btn-sm rounded-pill shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Department
                    </a>
                    <a href="{{ $buildRoute('admin.gradesFormula.edit.course', ['department' => $department->id, 'course' => $course->id]) }}" class="btn btn-success btn-sm rounded-pill shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i>{{ $needsCourseFormula ? 'Create Course Formula' : 'Edit Course Formula' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end mb-3">
        <form method="GET" action="{{ route('admin.gradesFormula.course', ['department' => $department->id, 'course' => $course->id]) }}" class="d-flex align-items-center gap-2 flex-wrap">
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
        $totalSubjects = $subjectSummaries->count();
        $customSubjects = $subjectSummaries->filter(fn ($summary) => $summary['has_formula'])->count();
        $defaultSubjects = max($totalSubjects - $customSubjects, 0);
    $fallbackScope = $courseFormula ? 'Course Formula' : ($departmentFallback ? 'Department Baseline' : 'System Default Formula');
    $fallbackLabel = $courseFormula->label ?? $departmentFallback->label ?? $globalFormula->label ?? 'Default Formula';
    @endphp

    <div class="card border-0 shadow-sm mb-3 bg-gradient-green-card">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-circle bg-gradient-overlay">
                        <i class="bi bi-journals text-white icon-md"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Subject Wildcards Overview</h6>
                        <small class="opacity-90">{{ $totalSubjects }} subjects · {{ $customSubjects }} custom formulas · {{ $defaultSubjects }} using fallback</small>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="bg-white bg-opacity-25 rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2">
                        <small class="fw-semibold text-dark mb-0">
                            <i class="bi bi-lightbulb me-1"></i>{{ $fallbackScope }} · {{ $fallbackLabel }}
                        </small>
                    </div>
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
                        <span class="badge bg-white text-success ms-1">{{ $totalSubjects }}</span>
                    </button>
                    <button class="btn btn-outline-success btn-sm rounded-pill wildcard-filter-btn" data-filter="custom">
                        <i class="bi bi-star-fill me-1"></i>Formulas
                        <span class="badge bg-success text-white ms-1">{{ $customSubjects }}</span>
                    </button>
                    <button class="btn btn-outline-success btn-sm rounded-pill wildcard-filter-btn" data-filter="default">
                        <i class="bi bi-shield-check me-1"></i>Subjects
                        <span class="badge bg-success text-white ms-1">{{ $defaultSubjects }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($needsCourseFormula)
        <div class="alert alert-info shadow-sm">
            <i class="bi bi-info-circle me-2"></i>No custom course formula yet. Subjects will inherit the {{ $departmentFallback ? 'department baseline' : 'system default' }} unless a course or subject formula is created.
        </div>
    @endif

    @if($subjectSummaries->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="text-muted mb-3">
                    <i class="bi bi-journal-minus fs-1 opacity-50"></i>
                </div>
                <h5 class="text-muted mb-2">No subjects found</h5>
                <p class="text-muted mb-0">Add subjects to this course to configure subject-level formulas.</p>
            </div>
        </div>
    @else
        <div class="row g-4" id="subject-wildcards">
            @foreach($subjectSummaries as $summary)
                @php
                    $subject = $summary['subject'];
                @endphp
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="wildcard-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden cursor-pointer transition-transform-shadow" data-status="{{ $summary['status'] }}" data-url="{{ $buildRoute('admin.gradesFormula.subject', ['subject' => $subject->id]) }}">
                        {{-- Top header --}}
                        <div class="position-relative header-height-80 bg-gradient-green-soft">
                            <div class="wildcard-circle-positioned">
                                <h5 class="mb-0 text-white fw-bold">{{ $subject->subject_code }}</h5>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div class="card-body pt-5 text-center">
                            <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $subject->subject_description }}">
                                {{ $subject->subject_description }}
                            </h6>
                            <p class="text-muted small mb-3">{{ $summary['scope_text'] }}</p>

                            {{-- Footer badges --}}
                            <div class="d-flex flex-column gap-2 mt-4">
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
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

{{-- JavaScript moved to: resources/js/pages/admin/grades-formula-course.js --}}

{{-- Styles: resources/css/admin/grades-formula.css --}}
@push('styles')
@endpush
