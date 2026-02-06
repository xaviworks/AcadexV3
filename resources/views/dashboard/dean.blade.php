@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4"
     x-data="deanDashboard()"
     x-init="init()">
    @php
        $hour = date('H');
        $firstName = explode(' ', Auth::user()->name)[0];
        
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Good Morning';
            $icon = 'bi bi-cloud-sun-fill';
            $iconColor = 'text-warning';
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = 'Good Afternoon';
            $icon = 'bi bi-brightness-high-fill';
            $iconColor = 'text-warning';
        } else {
            $greeting = 'Good Evening';
            $icon = 'bi bi-cloud-moon-fill';
            $iconColor = 'text-primary';
        }
    @endphp
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 d-flex align-items-center">
                <i class="{{ $icon }} {{ $iconColor }} me-2" style="font-size: 1.8rem;"></i>
                <span>{{ $greeting }}, {{ $firstName }}!</span>
            </h2>
            <p class="text-muted mb-0">Monitor academic performance and department statistics</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div x-show="polling" x-cloak>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2" style="font-size: 0.75rem;">
                    <i class="bi bi-broadcast me-1"></i> Live
                </span>
            </div>
            <a href="{{ route('dean.grades') }}" class="btn btn-success rounded-pill px-3 shadow-sm">
                <i class="bi bi-clipboard-data"></i> View Grades
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-primary-subtle me-3">
                            <i class="bi bi-mortarboard-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Total Students</h6>
                            <h3 class="fw-bold text-primary mb-0" x-text="data.totalStudents">{{ $studentsPerDepartment->sum() }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Across all departments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-success-subtle me-3">
                            <i class="bi bi-person-video3 text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Total Instructors</h6>
                            <h3 class="fw-bold text-success mb-0" x-text="data.totalInstructors">{{ $totalInstructors }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Active faculty members</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-info-subtle me-3">
                            <i class="bi bi-book-half text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Total Courses</h6>
                            <h3 class="fw-bold text-info mb-0" x-text="data.totalCourses">{{ $studentsPerCourse->count() }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Active academic courses</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-warning-subtle me-3">
                            <i class="bi bi-building text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Departments</h6>
                            <h3 class="fw-bold text-warning mb-0" x-text="data.totalDepartments">{{ $studentsPerDepartment->count() }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Active departments</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        {{-- Program Distribution --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-pie-chart-fill me-2"></i>Course Distribution
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Students</th>
                                    <th>Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(total, courseCode) in data.studentsPerCourse" :key="courseCode">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-mortarboard text-primary me-2"></i>
                                                <strong x-text="courseCode"></strong>
                                            </div>
                                        </td>
                                        <td x-text="total"></td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-primary" role="progressbar"
                                                     :style="'width: ' + (data.totalStudents > 0 ? (total / data.totalStudents * 100) : 0) + '%'">
                                                </div>
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

        {{-- Department Overview --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4">
                        <i class="bi bi-building me-2"></i>Department Overview
                    </h5>

                    <template x-for="(count, department) in data.studentsPerDepartment" :key="department">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted" x-text="department"></span>
                                <span class="badge bg-primary" x-text="count + ' students'"></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     :style="'width: ' + (data.totalStudents > 0 ? (count / data.totalStudents * 100) : 0) + '%'">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Styles: resources/css/dashboard/common.css --}}

@push('scripts')
<script>
function deanDashboard() {
    return {
        polling: false,
        pollInterval: null,
        data: {
            totalStudents: @json($studentsPerDepartment->sum()),
            totalInstructors: @json($totalInstructors),
            totalCourses: @json($studentsPerCourse->count()),
            totalDepartments: @json($studentsPerDepartment->count()),
            studentsPerDepartment: @json($studentsPerDepartment),
            studentsPerCourse: @json($studentsPerCourse),
        },
        _lastJson: '',
        init() {
            this.polling = true;
            this.fetchData();
            this.startPolling();
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) { clearInterval(this.pollInterval); }
                else { this.fetchData(); this.startPolling(); }
            });
        },
        destroy() {
            if (this.pollInterval) clearInterval(this.pollInterval);
        },
        startPolling() {
            if (this.pollInterval) clearInterval(this.pollInterval);
            this.pollInterval = setInterval(() => this.fetchData(), 2000);
        },
        async fetchData() {
            try {
                const r = await fetch('{{ route("dashboard.poll") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!r.ok) return;
                const d = await r.json();
                const j = JSON.stringify(d);
                if (j === this._lastJson) return;
                this._lastJson = j;
                Object.assign(this.data, d);
            } catch (e) { console.error('Dashboard poll error:', e); }
        }
    };
}
</script>
@endpush
