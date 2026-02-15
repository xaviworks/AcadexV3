@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4"
     x-data="adminDashboard()"
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
            <p class="text-muted mb-0">Monitor system activity and user management</p>
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
                            <h6 class="text-muted mb-0">Total Users</h6>
                            <h3 class="fw-bold text-primary mb-0" x-text="data.totalUsers">{{ $totalUsers }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Registered accounts</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-success-subtle me-3">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Successful Logins</h6>
                            <h3 class="fw-bold text-success mb-0" x-text="data.loginCount">{{ $loginCount }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Today's activity</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-danger-subtle me-3">
                            <i class="bi bi-shield-exclamation text-danger fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Failed Attempts</h6>
                            <h3 class="fw-bold text-danger mb-0" x-text="data.failedLoginCount">{{ $failedLoginCount }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Today's failed logins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-info-subtle me-3">
                            <i class="bi bi-person-check text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Active Users</h6>
                            <h3 class="fw-bold text-info mb-0" x-text="activeRate">{{ round($loginCount / max($totalUsers, 1) * 100) }}%</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> User activity today</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        {{-- Login Activity Table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-graph-up me-2"></i>Login Activity
                        </h5>
                        <div></div>
                    </div>
                    <div class="table-responsive flex-grow-1" style="height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top" style="top: 0; z-index: 1;">
                                <tr>
                                    <th>Hour</th>
                                    <th class="text-center">Successful Logins</th>
                                    <th class="text-center">Failed Attempts</th>
                                    <th class="text-end">Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(hour, index) in hours" :key="index">
                                    <tr :class="isPeakHour(index) ? 'table-active' : ''">
                                        <td x-text="hour"></td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success" x-text="data.successfulData[index] || 0"></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger-subtle text-danger" x-text="data.failedData[index] || 0"></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 100px;">
                                                    <div class="progress-bar"
                                                         :class="'bg-' + hourStatusColor(index)"
                                                         role="progressbar"
                                                         :style="'width: ' + hourRate(index) + '%'">
                                                    </div>
                                                </div>
                                                <span class="badge"
                                                      :class="'bg-' + hourStatusColor(index) + '-subtle text-' + hourStatusColor(index)"
                                                      x-text="hourRate(index) + '%'"></span>
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

        {{-- Monthly Overview --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-calendar-check me-2"></i>Monthly Overview
                        </h5>
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                            <select class="form-select form-select-sm shadow-none border-success-subtle" name="year" onchange="this.form.submit()">
                                @foreach ($yearRange as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="flex-grow-1" style="height: 350px; overflow-y: auto;">
                        <template x-for="(month, index) in months" :key="index">
                            <div class="mb-3 p-2" :class="isActiveMonth(index) ? 'bg-light rounded-3' : ''">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted" x-text="month"></span>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-success-subtle text-success" x-text="data.monthlySuccessfulData[index] || 0"></span>
                                        <span class="badge bg-danger-subtle text-danger" x-text="data.monthlyFailedData[index] || 0"></span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         :style="'width: ' + monthRate(index) + '%'">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Styles: resources/css/dashboard/common.css --}}

@push('scripts')
<script>
function adminDashboard() {
    return {
        polling: false,
        pollInterval: null,
        hours: ['12 AM','1 AM','2 AM','3 AM','4 AM','5 AM','6 AM','7 AM','8 AM','9 AM','10 AM','11 AM',
                '12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM','8 PM','9 PM','10 PM','11 PM'],
        months: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        data: {
            totalUsers: @json($totalUsers),
            loginCount: @json($loginCount),
            failedLoginCount: @json($failedLoginCount),
            successfulData: @json($successfulData),
            failedData: @json($failedData),
            monthlySuccessfulData: @json($monthlySuccessfulData),
            monthlyFailedData: @json($monthlyFailedData),
        },
        get activeRate() {
            return Math.round(this.data.loginCount / Math.max(this.data.totalUsers, 1) * 100) + '%';
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
                const params = new URLSearchParams(window.location.search);
                let url = '{{ route("dashboard.poll") }}';
                if (params.toString()) url += '?' + params.toString();
                const r = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!r.ok) return;
                const d = await r.json();
                const j = JSON.stringify(d);
                if (j === this._lastJson) return;
                this._lastJson = j;
                Object.assign(this.data, d);
            } catch (e) { console.error('Dashboard poll error:', e); }
        },
        hourRate(index) {
            const s = this.data.successfulData[index] || 0;
            const f = this.data.failedData[index] || 0;
            const t = s + f;
            return t > 0 ? Math.round((s / t) * 100) : 0;
        },
        hourStatusColor(index) {
            const rate = this.hourRate(index);
            if (rate >= 90) return 'success';
            if (rate >= 70) return 'info';
            if (rate >= 50) return 'warning';
            return 'danger';
        },
        isPeakHour(index) {
            const totals = this.hours.map((_, i) => ({
                index: i,
                total: (this.data.successfulData[i] || 0) + (this.data.failedData[i] || 0)
            }));
            totals.sort((a, b) => b.total - a.total);
            const peakIndices = totals.slice(0, 8).map(t => t.index);
            return peakIndices.includes(index);
        },
        monthRate(index) {
            const s = this.data.monthlySuccessfulData[index] || 0;
            const f = this.data.monthlyFailedData[index] || 0;
            const t = s + f;
            return t > 0 ? Math.round((s / t) * 100) : 0;
        },
        isActiveMonth(index) {
            const totals = this.months.map((_, i) => ({
                index: i,
                total: (this.data.monthlySuccessfulData[i] || 0) + (this.data.monthlyFailedData[i] || 0)
            }));
            totals.sort((a, b) => b.total - a.total);
            const activeIndices = totals.slice(0, 6).map(t => t.index);
            return activeIndices.includes(index);
        }
    };
}
</script>
@endpush
