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
    <div class="text-center mb-4">
        <div class="logo-icon mb-3">
            <i class="bi bi-calendar-check"></i>
        </div>
        <h2 class="fw-bold text-gray-800 mb-2">Select Academic Period</h2>
        <p class="text-muted small">Choose the semester you want to work with</p>
    </div>

    <form method="POST" action="{{ route('set.academicPeriod') }}" id="periodForm">
        @csrf

        @php
            // Group periods: merge summer semesters into their corresponding academic year
            // e.g., Summer 2026 (academic_year="2026") goes into "2026-2027" group
            $groupedPeriods = $periods->groupBy(function($period) {
                $year = $period->academic_year;
                // If it's a standalone year (summer), map it to the corresponding academic year range
                if (preg_match('/^\d{4}$/', $year)) {
                    $summerYear = (int)$year;
                    return $summerYear . '-' . ($summerYear + 1);
                }
                return $year;
            })->sortKeysDesc();
            
            // Get only academic year ranges for dropdown (they're now all XXXX-XXXX format)
            $academicYearRanges = $groupedPeriods->keys()->toArray();
        @endphp

        {{-- Year Filter Dropdown --}}
        <div class="year-filter mb-3">
            <select id="yearFilter" class="form-select">
                <option value="">All Academic Years</option>
                @foreach($academicYearRanges as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        {{-- Period List --}}
        <div class="period-list-container">
            <div class="period-list" id="periodList">
                @forelse($groupedPeriods as $year => $yearPeriods)
                    <div class="year-group" data-year="{{ $year }}" data-filter-year="{{ $year }}">
                        <div class="year-header">
                            <i class="bi bi-folder2 me-2"></i>{{ $year }}
                        </div>
                        @foreach($yearPeriods->sortBy(fn($p) => $p->semester === '1st' ? 1 : ($p->semester === '2nd' ? 2 : 3)) as $period)
                            <label class="period-item" data-year="{{ $year }}">
                                <input type="radio" 
                                       name="academic_period_id" 
                                       value="{{ $period->id }}"
                                       {{ $loop->parent->first && $loop->first ? 'checked' : '' }}
                                       required>
                                <div class="period-content">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="semester-badge semester-{{ strtolower(str_replace(' ', '', $period->semester)) }}">
                                            @if($period->semester === '1st')
                                                <i class="bi bi-1-circle-fill"></i>
                                            @elseif($period->semester === '2nd')
                                                <i class="bi bi-2-circle-fill"></i>
                                            @else
                                                <i class="bi bi-sun-fill"></i>
                                            @endif
                                        </span>
                                        <span class="semester-name">{{ $period->semester }} Semester</span>
                                    </div>
                                </div>
                                <div class="check-indicator">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <p>No academic periods available</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- No Results Message --}}
        <div class="no-results" id="noResults" style="display: none;">
            <i class="bi bi-funnel"></i>
            <p>No periods for selected year</p>
        </div>

        {{-- Submit Button --}}
        <div class="mt-4">
            <button type="submit" class="btn-proceed" id="submitBtn" {{ $periods->isEmpty() ? 'disabled' : '' }}>
                <span>Continue to Dashboard</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>

        {{-- Period Count --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <span id="visibleCount">{{ $periods->count() }}</span> of {{ $periods->count() }} periods
            </small>
        </div>
    </form>
</div>
@endsection
