@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-sliders text-success me-2"></i>Admin Control Panel</h2>
            <p class="text-muted mb-0">Monitor system activity and user management</p>
        </div>
        <div></div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4">
        @php
            $cards = [
                [
                    'label' => 'Total Users',
                    'icon' => 'bi bi-people-fill',
                    'value' => $totalUsers,
                    'color' => 'primary',
                    'trend' => 'Registered accounts'
                ],
                [
                    'label' => 'Successful Logins',
                    'icon' => 'bi bi-shield-check',
                    'value' => $loginCount,
                    'color' => 'success',
                    'trend' => 'Today\'s activity'
                ],
                [
                    'label' => 'Failed Attempts',
                    'icon' => 'bi bi-shield-exclamation',
                    'value' => $failedLoginCount,
                    'color' => 'danger',
                    'trend' => 'Today\'s failed logins'
                ],
                [
                    'label' => 'Active Users',
                    'icon' => 'bi bi-person-check',
                    'value' => round($loginCount / max($totalUsers, 1) * 100) . '%',
                    'color' => 'info',
                    'trend' => 'User activity today'
                ]
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-3 p-2 bg-{{ $card['color'] }}-subtle me-3">
                                <i class="{{ $card['icon'] }} text-{{ $card['color'] }} fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">{{ $card['label'] }}</h6>
                                <h3 class="fw-bold text-{{ $card['color'] }} mb-0">{{ $card['value'] }}</h3>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-arrow-right"></i> {{ $card['trend'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
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
                                    // Get peak hours (hours with most activity)
                                    $peakHours = collect($hours)->map(function($hour, $index) use($successfulData, $failedData) {
                                        return [
                                            'hour' => $hour,
                                            'index' => $index,
                                            'total' => ($successfulData[$index] ?? 0) + ($failedData[$index] ?? 0)
                                        ];
                                    })->sortByDesc('total')->take(8)->pluck('index')->toArray();
                                @endphp
                                @foreach($hours as $index => $hour)
                                    @php
                                        $successful = $successfulData[$index] ?? 0;
                                        $failed = $failedData[$index] ?? 0;
                                        $total = $successful + $failed;
                                        $rate = $total > 0 ? round(($successful / $total) * 100) : 0;
                                        $statusColor = match(true) {
                                            $rate >= 90 => 'success',
                                            $rate >= 70 => 'info',
                                            $rate >= 50 => 'warning',
                                            default => 'danger'
                                        };
                                        $isHighlight = in_array($index, $peakHours);
                                    @endphp
                                    <tr class="{{ $isHighlight ? 'table-active' : '' }}">
                                        <td>{{ $hour }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success">{{ $successful }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger-subtle text-danger">{{ $failed }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 100px;">
                                                    <div class="progress-bar bg-{{ $statusColor }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $rate }}%">
                                                    </div>
                                                </div>
                                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                                    {{ $rate }}%
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
                        @php
                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            // Get months with highest activity
                            $activeMonths = collect($months)->map(function($month, $index) use($monthlySuccessfulData, $monthlyFailedData) {
                                return [
                                    'month' => $month,
                                    'index' => $index,
                                    'total' => ($monthlySuccessfulData[$index] ?? 0) + ($monthlyFailedData[$index] ?? 0)
                                ];
                            })->sortByDesc('total')->take(6)->pluck('index')->toArray();
                        @endphp
                        @foreach($months as $index => $month)
                            @php
                                $successful = $monthlySuccessfulData[$index] ?? 0;
                                $failed = $monthlyFailedData[$index] ?? 0;
                                $total = $successful + $failed;
                                $rate = $total > 0 ? round(($successful / $total) * 100) : 0;
                                $isHighlight = in_array($index, $activeMonths);
                            @endphp
                            <div class="mb-3 p-2 {{ $isHighlight ? 'bg-light rounded-3' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">{{ $month }}</span>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-success-subtle text-success">{{ $successful }}</span>
                                        <span class="badge bg-danger-subtle text-danger">{{ $failed }}</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $rate }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
{{-- Styles: resources/css/dashboard/common.css --}}
