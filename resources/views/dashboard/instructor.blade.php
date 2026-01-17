@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4" x-data="instructorDashboard(@js($instructorStudents), @js($enrolledSubjectsCount), @js($totalPassedStudents), @js($totalFailedStudents), @js($termCompletions), @js($subjectCharts))" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Welcome Back, {{ Auth::user()->name }}! 👋</h2>
            <p class="text-muted mb-0">Here's what's happening with your classes today.</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-primary-subtle me-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Total Students</h6>
                            <h3 class="fw-bold text-primary mb-0" x-text="data.instructorStudents"></h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-arrow-right"></i> Currently enrolled
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-info-subtle me-3">
                            <i class="bi bi-journal-text text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Course Load</h6>
                            <h3 class="fw-bold text-info mb-0" x-text="data.enrolledSubjectsCount"></h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-arrow-right"></i> Current semester
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-success-subtle me-3">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Students Passed</h6>
                            <h3 class="fw-bold text-success mb-0" x-text="data.totalPassedStudents"></h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-arrow-right"></i> Final grades
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-danger-subtle me-3">
                            <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Students Failed</h6>
                            <h3 class="fw-bold text-danger mb-0" x-text="data.totalFailedStudents"></h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-arrow-right"></i> Final grades
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        {{-- Term Completion --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-graph-up-arrow me-2"></i>Grading Progress
                        </h5>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Current Term</span>
                    </div>
                    
                    <template x-for="(term, index) in ['prelim', 'midterm', 'prefinal', 'final']" :key="term">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-capitalize mb-0" x-text="term.charAt(0).toUpperCase() + term.slice(1)"></h6>
                                <span :class="'text-' + getProgressColor(getTermProgress(term))" x-text="getTermProgress(term) + '%'"></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div :class="'progress-bar bg-' + getProgressColor(getTermProgress(term))" 
                                     role="progressbar" 
                                     :style="'width: ' + getTermProgress(term) + '%;'"
                                     :aria-valuenow="getTermProgress(term)" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted" x-text="(data.termCompletions[term]?.graded || 0) + ' of ' + (data.termCompletions[term]?.total || 0) + ' grades submitted'"></small>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Subject Performance --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-bar-chart-fill me-2"></i>Course Completion Status
                        </h5>
                    </div>
                    <div class="mb-4" style="height: 300px;">
                        <canvas id="subjectPerformanceChart"></canvas>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Description</th>
                                    <th class="text-center">Completion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="subject in data.subjectCharts" :key="subject.code">
                                    <tr>
                                        <td x-text="subject.code"></td>
                                        <td x-text="subject.description"></td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div :class="'progress-bar bg-' + getCompletionColor(getAvgCompletion(subject))" 
                                                         role="progressbar" 
                                                         :style="'width: ' + getAvgCompletion(subject) + '%'">
                                                    </div>
                                                </div>
                                                <span :class="'badge bg-' + getCompletionColor(getAvgCompletion(subject)) + '-subtle text-' + getCompletionColor(getAvgCompletion(subject)) + ' rounded-pill'" x-text="Math.round(getAvgCompletion(subject)) + '%'"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Styles: resources/css/dashboard/common.css --}}

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/dashboard/instructor.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass PHP data to external JS
    window.pageData = {
        subjectCharts: @json($subjectCharts)
    };
</script>
@endpush
