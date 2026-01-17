@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4" x-data="adminDashboard(@js($totalUsers), @js($loginCount), @js($failedLoginCount), @js($successfulData), @js($failedData), @js($monthlySuccessfulData), @js($monthlyFailedData))" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-sliders text-success me-2"></i>Admin Control Panel</h2>
            <p class="text-muted mb-0">Monitor system activity and user management</p>
        </div>
        <div></div>
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
                            <h3 class="fw-bold text-primary mb-0" x-text="data.totalUsers"></h3>
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
                            <h3 class="fw-bold text-success mb-0" x-text="data.loginCount"></h3>
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
                            <h3 class="fw-bold text-danger mb-0" x-text="data.failedLoginCount"></h3>
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
                            <h3 class="fw-bold text-info mb-0" x-text="activeUsersPercentage + '%'"></h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> User activity today</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        {{-- Login Activity Chart --}}
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
                                @php
                                    $hours = ['12 AM', '1 AM', '2 AM', '3 AM', '4 AM', '5 AM', '6 AM', '7 AM', '8 AM', '9 AM', '10 AM', '11 AM',
                                            '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM', '10 PM', '11 PM'];
                                @endphp
                                @foreach($hours as $index => $hour)
                                    <tr>
                                        <td>{{ $hour }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success" x-text="getSuccessful({{ $index }})"></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger-subtle text-danger" x-text="getFailed({{ $index }})"></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 100px;">
                                                    <div class="progress-bar" 
                                                         :class="'bg-' + getRateColor(getRate({{ $index }}))"
                                                         role="progressbar" 
                                                         :style="'width: ' + getRate({{ $index }}) + '%'">
                                                    </div>
                                                </div>
                                                <span class="badge" 
                                                      :class="'bg-' + getRateColor(getRate({{ $index }})) + '-subtle text-' + getRateColor(getRate({{ $index }}))"
                                                      x-text="getRate({{ $index }}) + '%'">
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
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
                        <template x-for="(month, index) in ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']" :key="index">
                            <div class="mb-3 p-2" :class="{ 'bg-light rounded-3': isHighlightMonth(index) }">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted" x-text="month"></span>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-success-subtle text-success" x-text="getMonthlySuccessful(index)"></span>
                                        <span class="badge bg-danger-subtle text-danger" x-text="getMonthlyFailed(index)"></span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         :style="`width: ${getMonthlyRate(index)}%`">
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
