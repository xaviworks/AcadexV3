@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4"
     x-data="vpaaDashboard()"
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
            <p class="text-muted mb-0">Oversee academic operations and institutional management</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-3 hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-2 p-2 bg-primary-subtle me-2">
                            <i class="bi bi-building text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small">Total Departments</h6>
                            <h4 class="fw-bold text-primary mb-0" x-text="data.departmentsCount">{{ $departmentsCount ?? 0 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                        <i class="bi bi-arrow-right"></i> Active departments
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-3 hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-2 p-2 bg-success-subtle me-2">
                            <i class="bi bi-people-fill text-success fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small">Total Instructors</h6>
                            <h4 class="fw-bold text-success mb-0" x-text="data.instructorsCount">{{ $instructorsCount ?? 0 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                        <i class="bi bi-arrow-right"></i> Active faculty
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-3 hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-2 p-2 bg-info-subtle me-2">
                            <i class="bi bi-mortarboard-fill text-info fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small">Total Students</h6>
                            <h4 class="fw-bold text-info mb-0" x-text="data.studentsCount">{{ $studentsCount ?? 0 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                        <i class="bi bi-arrow-right"></i> Enrolled students
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-3 hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-2 p-2 bg-warning-subtle me-2">
                            <i class="bi bi-journal-text text-warning fs-5"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small">Academic Programs</h6>
                            <h4 class="fw-bold text-warning mb-0" x-text="data.academicPrograms">{{ ($departmentsCount ?? 0) * 3 }}</h4>
                        </div>
                    </div>
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                        <i class="bi bi-arrow-right"></i> Course offerings
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <span class="me-auto">{{ session('status') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="row g-3 mt-1">
        {{-- Departments Overview --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    {{-- Header Section --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-3 p-2 bg-primary-subtle me-3">
                                <i class="bi bi-building text-primary fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-semibold mb-0">Department Management</h5>
                                <p class="text-muted small mb-0">Academic department overview and status</p>
                            </div>
                        </div>
                        <a href="{{ route('vpaa.departments') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    {{-- Quick Stats Grid --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-primary-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-3 p-2 bg-primary text-white me-3">
                                            <i class="bi bi-building fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-primary small fw-semibold">Active Departments</div>
                                            <h3 class="fw-bold text-primary mb-0" x-text="data.departmentsCount">{{ $departmentsCount ?? 0 }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Currently operational</span>
                                        <span class="badge bg-primary px-2 py-1">100%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-success-subtle hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-3 p-2 bg-success text-white me-3">
                                            <i class="bi bi-people-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-success small fw-semibold">Faculty Members</div>
                                            <h3 class="fw-bold text-success mb-0" x-text="data.instructorsCount">{{ $instructorsCount ?? 0 }}</h3>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Active instructors</span>
                                        <span class="badge bg-success px-2 py-1" x-text="avgPerDept">
                                            {{ $departmentsCount > 0 ? number_format(($instructorsCount / $departmentsCount), 1) : '0.0' }} avg
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Access Panel --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>Quick Access
                        </h5>
                    </div>

                    {{-- Quick Access Items --}}
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('vpaa.departments') }}" class="card border-0 bg-primary bg-opacity-10 hover-bg-opacity-25 transition-all rounded-3 text-decoration-none h-100">
                                <div class="card-body text-center p-3">
                                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px;">
                                        <i class="bi bi-building fs-5"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold small text-dark">Departments</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.7rem;">Manage departments</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('vpaa.instructors') }}" class="card border-0 bg-success bg-opacity-10 hover-bg-opacity-25 transition-all rounded-3 text-decoration-none h-100">
                                <div class="card-body text-center p-3">
                                    <div class="bg-success bg-opacity-25 text-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px;">
                                        <i class="bi bi-people-fill fs-5"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold small text-dark">Instructors</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.7rem;">Manage faculty</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('vpaa.students') }}" class="card border-0 bg-info bg-opacity-10 hover-bg-opacity-25 transition-all rounded-3 text-decoration-none h-100">
                                <div class="card-body text-center p-3">
                                    <div class="bg-info bg-opacity-25 text-info rounded-circle p-2 d-inline-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px;">
                                        <i class="bi bi-mortarboard-fill fs-5"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold small text-dark">Students</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.7rem;">Access records</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Styles: resources/css/vpaa/common.css --}}

@push('scripts')
<script>
function vpaaDashboard() {
    return {
        polling: false,
        pollInterval: null,
        data: {
            departmentsCount: @json($departmentsCount ?? 0),
            instructorsCount: @json($instructorsCount ?? 0),
            studentsCount: @json($studentsCount ?? 0),
            academicPrograms: @json(($departmentsCount ?? 0) * 3),
        },
        get avgPerDept() {
            return this.data.departmentsCount > 0
                ? (this.data.instructorsCount / this.data.departmentsCount).toFixed(1) + ' avg'
                : '0.0 avg';
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
            this.pollInterval = setInterval(() => this.fetchData(), 10000);
        },
        async fetchData() {
            try {
                const r = await fetch('{{ route("vpaa.dashboard.poll") }}', {
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
