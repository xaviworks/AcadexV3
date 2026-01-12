@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-calendar-week-fill text-success me-2"></i>Select Academic Period</h1>
            <p class="text-muted mb-0">Choose the period for Grade Formula Management</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    @php
        // Group periods: merge summer semesters into their corresponding academic year
        $groupedPeriods = $academicPeriods->groupBy(function($period) {
            $year = $period->academic_year;
            if (preg_match('/^\d{4}$/', $year)) {
                $summerYear = (int)$year;
                return $summerYear . '-' . ($summerYear + 1);
            }
            return $year;
        })->sortKeysDesc();
        
        // Get academic year ranges for dropdown
        $academicYearRanges = $groupedPeriods->keys()->toArray();
        
        // Determine current academic year (default selection)
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        // Academic year starts in August, so Jan-Jul belongs to previous academic year
        $academicStartYear = $currentMonth >= 8 ? $currentYear : $currentYear - 1;
        $defaultAcademicYear = $academicStartYear . '-' . ($academicStartYear + 1);
        
        // Fallback to first available if default doesn't exist
        if (!in_array($defaultAcademicYear, $academicYearRanges) && count($academicYearRanges) > 0) {
            $defaultAcademicYear = $academicYearRanges[0];
        }
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <form method="GET" action="{{ route('admin.gradesFormula') }}" id="periodForm">
                {{-- "All Academic Periods" Option --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold text-success">
                            <i class="bi bi-globe me-2"></i>Global Formulas
                        </h6>
                    </div>
                    <div class="card-body">
                        <label class="period-card all-periods w-100 {{ old('academic_period_id') === 'all' ? 'selected' : '' }}">
                            <input type="radio" 
                                   name="academic_period_id" 
                                   value="all"
                                   class="d-none"
                                   {{ old('academic_period_id') === 'all' ? 'checked' : '' }}>
                            <div class="d-flex align-items-center">
                                <div class="period-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-globe"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">All Academic Periods</div>
                                    <small class="text-muted">Manage global baseline formulas that apply across all periods</small>
                                </div>
                                <div class="period-check">
                                    <i class="bi bi-check-circle-fill text-primary"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Specific Period Selection --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-semibold text-success">
                                <i class="bi bi-calendar3 me-2"></i>Specific Period
                            </h6>
                            {{-- Year Dropdown --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" id="yearDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    <span id="selectedYearText">{{ $defaultAcademicYear }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" id="yearDropdownMenu">
                                    @foreach($academicYearRanges as $year)
                                        <li>
                                            <a class="dropdown-item {{ $year === $defaultAcademicYear ? 'active' : '' }}" 
                                               href="#" 
                                               data-year="{{ $year }}">
                                                {{ $year }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2" id="semesterCards">
                            @forelse($groupedPeriods as $year => $yearPeriods)
                                @foreach($yearPeriods->sortBy(fn($p) => $p->semester === '1st' ? 1 : ($p->semester === '2nd' ? 2 : 3)) as $period)
                                    <label class="period-card w-100" 
                                           data-year="{{ $year }}" 
                                           style="{{ $year !== $defaultAcademicYear ? 'display: none;' : '' }}">
                                        <input type="radio" 
                                               name="academic_period_id" 
                                               value="{{ $period->id }}"
                                               class="d-none">
                                        <div class="d-flex align-items-center">
                                            <div class="period-icon 
                                                @if($period->semester === '1st') bg-success bg-opacity-10 text-success
                                                @elseif($period->semester === '2nd') bg-danger bg-opacity-10 text-danger
                                                @else bg-warning bg-opacity-10 text-warning
                                                @endif">
                                                @if($period->semester === '1st')
                                                    <i class="bi bi-1-circle-fill"></i>
                                                @elseif($period->semester === '2nd')
                                                    <i class="bi bi-2-circle-fill"></i>
                                                @else
                                                    <i class="bi bi-sun-fill"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="fw-semibold">
                                                    @if($period->semester === 'Summer')
                                                        Summer Term
                                                    @else
                                                        {{ $period->semester }} Semester
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $period->academic_year }}</small>
                                            </div>
                                            <div class="period-check">
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-x fs-1 mb-2 d-block"></i>
                                    <p class="mb-0">No academic periods available</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- No Results Message --}}
                        <div class="text-center py-4 text-muted" id="noResults" style="display: none;">
                            <i class="bi bi-funnel fs-1 mb-2 d-block"></i>
                            <p class="mb-0">No semesters available for this year</p>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                        <span>Continue to Grade Formula Management</span>
                        <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Styles: resources/css/admin/grades-formula-select-period.css --}}
{{-- Scripts: resources/js/pages/admin/grades-formula-select-period.js --}}
@endsection
