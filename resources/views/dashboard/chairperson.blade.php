@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4"
     x-data="chairpersonDashboard()"
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
            <p class="text-muted mb-0">Monitor Course performance and faculty management</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div x-show="polling" x-cloak>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2" style="font-size: 0.75rem;">
                    <i class="bi bi-broadcast me-1"></i> Live
                </span>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-primary-subtle me-3">
                            <i class="bi bi-person-video3 text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Total Instructors</h6>
                            <h3 class="fw-bold text-primary mb-0" x-text="data.countInstructors">{{ $countInstructors }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Department faculty</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-success-subtle me-3">
                            <i class="bi bi-mortarboard-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Total Students</h6>
                            <h3 class="fw-bold text-success mb-0" x-text="data.countStudents">{{ $countStudents }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Enrolled this semester</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-info-subtle me-3">
                            <i class="bi bi-journal-text text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Active Courses</h6>
                            <h3 class="fw-bold text-info mb-0" x-text="data.countCourses">{{ $countCourses }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Current offerings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        {{-- Faculty Status Overview --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    {{-- Header Section --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 p-2 bg-primary-subtle me-3">
                                <i class="bi bi-person-video3 text-primary fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-semibold mb-1">Faculty Status Overview</h5>
                                <p class="text-muted small mb-0">
                                    Managing <span class="fw-bold" x-text="data.countInstructors">{{ $countInstructors }}</span> Course Faculty Members
                                </p>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-pill px-3 hover-lift" type="button" id="helpDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-info-circle me-1"></i> Help
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-sm" aria-labelledby="helpDropdown" style="min-width: 280px;">
                                <li class="small">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                        <h6 class="mb-0">Status Guide</h6>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-mortarboard-fill text-success me-2"></i>
                                                <strong>Active:</strong>
                                                <span class="ms-2 text-muted small">Currently teaching</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-x-fill text-danger me-2"></i>
                                                <strong>Inactive:</strong>
                                                <span class="ms-2 text-muted small">On leave/deactivated</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-plus text-warning me-2"></i>
                                                <strong>Pending:</strong>
                                                <span class="ms-2 text-muted small">Awaiting verification</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Overall Progress Bar --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-graph-up text-primary me-2"></i>
                                Faculty Distribution
                            </h6>
                            <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                Total: <span x-text="data.countInstructors">{{ $countInstructors }}</span> Members
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height: 12px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                :style="'width: ' + data.activePercentage + '%'"
                                :title="'Active: ' + data.countActiveInstructors + ' faculty members'">
                            </div>
                            <div class="progress-bar bg-danger" role="progressbar"
                                :style="'width: ' + data.inactivePercentage + '%'"
                                :title="'Inactive: ' + data.countInactiveInstructors + ' faculty members'">
                            </div>
                            <div class="progress-bar bg-warning" role="progressbar"
                                :style="'width: ' + data.pendingPercentage + '%'"
                                :title="'Pending: ' + data.countUnverifiedInstructors + ' faculty members'">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="small"><i class="bi bi-circle-fill text-success me-1"></i> Active</div>
                            <div class="small"><i class="bi bi-circle-fill text-danger me-1"></i> Inactive</div>
                            <div class="small"><i class="bi bi-circle-fill text-warning me-1"></i> Pending</div>
                        </div>
                    </div>

                    {{-- Status Cards --}}
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-success-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 p-2 bg-success text-white me-3">
                                            <i class="bi bi-mortarboard-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-success small fw-semibold">Active Faculty</div>
                                            <h3 class="fw-bold text-success mb-0" x-text="data.countActiveInstructors">{{ $countActiveInstructors }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Currently Teaching</span>
                                        <span class="badge bg-success text-white px-2 py-1" x-text="data.activePercentage + '%'">
                                            {{ $countInstructors > 0 ? number_format(($countActiveInstructors / $countInstructors) * 100, 1) : '0.0' }}%
                                        </span>
                                    </div>
                                    @if($countActiveInstructors > 0)
                                        <a href="{{ route('chairperson.instructors') }}#active" class="stretched-link" data-bs-toggle="tooltip" title="View active faculty members"></a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-danger-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 p-2 bg-danger text-white me-3">
                                            <i class="bi bi-person-x-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-danger small fw-semibold">Inactive Faculty</div>
                                            <h3 class="fw-bold text-danger mb-0" x-text="data.countInactiveInstructors">{{ $countInactiveInstructors }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">On Deactivated</span>
                                        <span class="badge bg-danger text-white px-2 py-1" x-text="data.inactivePercentage + '%'">
                                            {{ $countInstructors > 0 ? number_format(($countInactiveInstructors / $countInstructors) * 100, 1) : '0.0' }}%
                                        </span>
                                    </div>
                                    @if($countInactiveInstructors > 0)
                                        <a href="{{ route('chairperson.instructors') }}#inactive" class="stretched-link" data-bs-toggle="tooltip" title="Review inactive faculty members"></a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-warning-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 p-2 bg-warning text-white me-3">
                                            <i class="bi bi-person-plus fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-warning small fw-semibold">Pending Verification</div>
                                            <h3 class="fw-bold text-warning mb-0" x-text="data.countUnverifiedInstructors">{{ $countUnverifiedInstructors }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Awaiting Approval</span>
                                        <span class="badge bg-warning text-dark px-2 py-1" x-text="data.pendingPercentage + '%'">
                                            {{ $countInstructors > 0 ? number_format(($countUnverifiedInstructors / $countInstructors) * 100, 1) : '0.0' }}%
                                        </span>
                                    </div>
                                    @if($countUnverifiedInstructors > 0)
                                        <a href="{{ route('chairperson.instructors') }}#pending" class="stretched-link" data-bs-toggle="tooltip" title="Review pending faculty accounts"></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-pill px-3 hover-lift" type="button" id="quickActionsHelp" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-info-circle me-1"></i> Help
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-sm" aria-labelledby="quickActionsHelp" style="min-width: 280px;">
                                <li class="small">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                        <h6 class="mb-0">Quick Actions Guide</h6>
                                    </div>
                                    <p class="text-muted small mb-2">Access frequently used features to manage your department efficiently.</p>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-journal-plus text-success me-2"></i>
                                                <div>
                                                    <strong>Assign Subjects:</strong>
                                                    <span class="d-block text-muted small">Manage teaching loads and course assignments</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-mortarboard-fill text-primary me-2"></i>
                                                <div>
                                                    <strong>Student List:</strong>
                                                    <span class="d-block text-muted small">View and manage student records by year level</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-clipboard-data text-info me-2"></i>
                                                <div>
                                                    <strong>View Grades:</strong>
                                                    <span class="d-block text-muted small">Monitor and analyze student performance</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3 flex-grow-1 justify-content-between">
                        <a href="{{ route('chairperson.assign-subjects') }}" class="btn btn-light text-start rounded-3 p-3 hover-lift position-relative" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage teaching loads and course assignments">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 bg-success-subtle me-3"><i class="bi bi-journal-plus text-success fs-5"></i></div>
                                <div><h6 class="mb-1">Assign Courses</h6><small class="text-muted d-block">Manage teaching loads</small></div>
                                <div class="ms-auto"><i class="bi bi-chevron-right text-muted"></i></div>
                            </div>
                        </a>

                        <a href="{{ route('chairperson.studentsByYear') }}" class="btn btn-light text-start rounded-3 p-3 hover-lift position-relative" data-bs-toggle="tooltip" data-bs-placement="right" title="View and manage student records by year level">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 bg-primary-subtle me-3"><i class="bi bi-mortarboard-fill text-primary fs-5"></i></div>
                                <div><h6 class="mb-1">Student List</h6><small class="text-muted d-block">View students by year</small></div>
                                <div class="ms-auto"><i class="bi bi-chevron-right text-muted"></i></div>
                            </div>
                        </a>

                        <a href="{{ route('chairperson.viewGrades') }}" class="btn btn-light text-start rounded-3 p-3 hover-lift position-relative" data-bs-toggle="tooltip" data-bs-placement="right" title="Monitor and analyze student performance">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 p-2 bg-info-subtle me-3"><i class="bi bi-clipboard-data text-info fs-5"></i></div>
                                <div><h6 class="mb-1">View Grades</h6><small class="text-muted d-block">Monitor student performance</small></div>
                                <div class="ms-auto"><i class="bi bi-chevron-right text-muted"></i></div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
{{-- Styles: resources/css/dashboard/common.css --}}
{{-- JavaScript: resources/js/pages/dashboard/chairperson.js --}}

@push('scripts')
<script>
function chairpersonDashboard() {
    return {
        polling: false,
        pollInterval: null,
        data: {
            countInstructors: @json($countInstructors),
            countStudents: @json($countStudents),
            countCourses: @json($countCourses),
            countActiveInstructors: @json($countActiveInstructors),
            countInactiveInstructors: @json($countInactiveInstructors),
            countUnverifiedInstructors: @json($countUnverifiedInstructors),
            activePercentage: @json($countInstructors > 0 ? round(($countActiveInstructors / $countInstructors) * 100, 1) : 0),
            inactivePercentage: @json($countInstructors > 0 ? round(($countInactiveInstructors / $countInstructors) * 100, 1) : 0),
            pendingPercentage: @json($countInstructors > 0 ? round(($countUnverifiedInstructors / $countInstructors) * 100, 1) : 0),
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
