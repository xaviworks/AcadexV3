@extends('layouts.app')

{{-- Styles: resources/css/gecoordinator/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-file-earmark-bar-graph text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>GE Coordinator Reports</span>
    </h1>
    <p class="text-muted mb-4">Overview of GE subjects, instructors, and enrollment statistics</p>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Subjects Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-book text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total GE Subjects</div>
                        <div class="fs-3 fw-bold text-dark">{{ $reportData['total_subjects'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Subjects Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-check text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Assigned Subjects</div>
                        <div class="fs-3 fw-bold text-dark">{{ $reportData['assigned_subjects'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unassigned Subjects Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-x text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Unassigned Subjects</div>
                        <div class="fs-3 fw-bold text-dark">{{ $reportData['unassigned_subjects'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Instructors Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-workspace text-info" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Available Instructors</div>
                        <div class="fs-3 fw-bold text-dark">{{ $reportData['total_instructors'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Enrollments Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Enrollments</div>
                        <div class="fs-3 fw-bold text-dark">{{ $reportData['total_enrollments'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Rate Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-percent text-secondary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Assignment Rate</div>
                        <div class="fs-3 fw-bold text-dark">
                            @if($reportData['total_subjects'] > 0)
                                {{ round(($reportData['assigned_subjects'] / $reportData['total_subjects']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects by Year Level -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h5 class="fw-semibold mb-4">Subjects by Year Level</h5>
            <div class="row g-3">
                @for($year = 1; $year <= 4; $year++)
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded-4">
                            <div class="fs-3 fw-bold text-success">
                                {{ $reportData['subjects_by_year'][$year] ?? 0 }}
                            </div>
                            <div class="text-muted small">Year {{ $year }}</div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-3">
        <a href="{{ route('gecoordinator.assign-subjects') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Assign Subjects
        </a>
        <a href="{{ route('gecoordinator.manage-schedule') }}" class="btn btn-success">
            <i class="bi bi-calendar-week me-2"></i>Manage Schedule
        </a>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="bi bi-printer me-2"></i>Print Report
        </button>
    </div>
</div>
@endsection
