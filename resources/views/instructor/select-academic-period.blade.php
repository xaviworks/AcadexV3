@extends('layouts.blank')

@push('styles')
    @vite('resources/css/components/select-academic-period.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/shared/select-academic-period.js')
@endpush

@section('content')
<div class="select-period-container">
    {{-- Header Section --}}
    <div class="text-center mb-3">
        <div class="logo-icon mb-2">
            <i class="bi bi-calendar-check"></i>
        </div>
        <h2 class="fw-bold text-gray-800 mb-1" style="font-size: 1.25rem;">Select Academic Period</h2>
        <p class="text-muted" style="font-size: 0.8rem;">Choose the semester you want to work with</p>
    </div>

    <form method="POST" action="{{ route('set.academicPeriod') }}" id="periodForm">
        @csrf

        @php
            // Group periods: merge summer semesters into their corresponding academic year
            $groupedPeriods = $periods->groupBy(function($period) {
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

        {{-- Year Dropdown with Label (Custom Dropdown) --}}
        <div class="year-filter mb-3">
            <label class="year-filter-label">
                <i class="bi bi-calendar3 me-1"></i> Academic Year
            </label>
            <div class="custom-dropdown" id="yearDropdown" data-default-year="{{ $defaultAcademicYear }}">
                <button type="button" class="dropdown-toggle" id="yearDropdownBtn">
                    <span class="dropdown-value">{{ $defaultAcademicYear }}</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </button>
                <div class="dropdown-menu" id="yearDropdownMenu">
                    @foreach($academicYearRanges as $year)
                        <div class="dropdown-item {{ $year === $defaultAcademicYear ? 'selected' : '' }}" data-value="{{ $year }}">
                            <span>{{ $year }}</span>
                            <i class="bi bi-check2"></i>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Semester Cards --}}
        <div class="semester-cards" id="semesterCards">
            @forelse($groupedPeriods as $year => $yearPeriods)
                @foreach($yearPeriods->sortBy(fn($p) => $p->semester === '1st' ? 1 : ($p->semester === '2nd' ? 2 : 3)) as $period)
                    <label class="semester-card" 
                           data-year="{{ $year }}" 
                           data-semester="{{ $period->semester }}"
                           style="{{ $year !== $defaultAcademicYear ? 'display: none;' : '' }}">
                        <input type="radio" 
                               name="academic_period_id" 
                               value="{{ $period->id }}"
                               {{ $year === $defaultAcademicYear && $period->semester === '1st' ? 'checked' : '' }}
                               required>
                        <div class="semester-card-content">
                            <span class="semester-icon semester-{{ strtolower(str_replace(' ', '', $period->semester)) }}">
                                @if($period->semester === '1st')
                                    <i class="bi bi-1-circle-fill"></i>
                                @elseif($period->semester === '2nd')
                                    <i class="bi bi-2-circle-fill"></i>
                                @else
                                    <i class="bi bi-sun-fill"></i>
                                @endif
                            </span>
                            <span class="semester-label">
                                @if($period->semester === 'Summer')
                                    Summer
                                @else
                                    {{ $period->semester }} Semester
                                @endif
                            </span>
                        </div>
                        <div class="check-indicator">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </label>
                @endforeach
            @empty
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <p>No academic periods available</p>
                </div>
            @endforelse
        </div>

        {{-- No Results Message --}}
        <div class="no-results" id="noResults" style="display: none;">
            <i class="bi bi-funnel"></i>
            <p>No semesters available for this year</p>
        </div>

        {{-- Submit Button --}}
        <div class="mt-3">
            <button type="submit" class="btn-proceed" id="submitBtn" {{ $periods->isEmpty() ? 'disabled' : '' }}>
                <span>Continue to Dashboard</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
@endsection
