@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-calendar-week-fill text-success me-2"></i>Select Academic Period</h1>
            <p class="text-muted mb-0">Choose which academic period you want to manage formulas for</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.gradesFormula') }}" id="grades-formula-period-form" class="d-grid gap-4">
                        <div>
                            <label for="academic-period-select" class="form-label fw-semibold text-success">Academic Period</label>
                            <select class="form-select form-select-lg" id="academic-period-select" required>
                                <option value="">-- Select Academic Period --</option>
                                <option value="all">All Academic Periods</option>
                                @foreach ($academicPeriods as $period)
                                    <option
                                        value="{{ $period->id }}"
                                        data-year="{{ $period->academic_year }}"
                                        data-semester="{{ $period->semester }}"
                                    >
                                        {{ $period->academic_year }} - {{ $period->semester }} Semester
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="academic_period_id" id="selected-academic-period">
                            <input type="hidden" name="academic_year" id="selected-academic-year">
                            <input type="hidden" name="semester" id="selected-semester">
                            <small class="text-muted d-block mt-2">Only periods with active data appear here. Contact the registrar if the period you need is missing.</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ route('admin.departments') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-4" id="continue-button" disabled>
                                Continue
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript moved to: resources/js/pages/admin/grades-formula-select-period.js --}}
