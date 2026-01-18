@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
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
            <p class="text-muted mb-0">Here's what's happening with your classes today.</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4">
        @php
            $cards = [
                [
                    'label' => 'Total Students',
                    'icon' => 'bi bi-people-fill',
                    'value' => $instructorStudents,
                    'color' => 'primary',
                    'trend' => 'Currently enrolled'
                ],
                [
                    'label' => 'Course Load',
                    'icon' => 'bi bi-journal-text',
                    'value' => $enrolledSubjectsCount,
                    'color' => 'info',
                    'trend' => 'Current semester'
                ],
                [
                    'label' => 'Students Passed',
                    'icon' => 'bi bi-check-circle-fill',
                    'value' => $totalPassedStudents,
                    'color' => 'success',
                    'trend' => 'Final grades'
                ],
                [
                    'label' => 'Students Failed',
                    'icon' => 'bi bi-x-circle-fill',
                    'value' => $totalFailedStudents,
                    'color' => 'danger',
                    'trend' => 'Final grades'
                ],
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
                    
                    @foreach(['prelim', 'midterm', 'prefinal', 'final'] as $term)
                        @php
                            $progress = $termCompletions[$term]['total'] > 0
                                ? round(($termCompletions[$term]['graded'] / $termCompletions[$term]['total']) * 100)
                                : 0;

                            $color = match(true) {
                                $progress === 100 => 'success',
                                $progress > 75 => 'info',
                                $progress > 50 => 'warning',
                                default => 'danger'
                            };
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-capitalize mb-0">{{ ucfirst($term) }}</h6>
                                <span class="text-{{ $color }}">{{ $progress }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                     style="width: {{ $progress }}%;" 
                                     aria-valuenow="{{ $progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $termCompletions[$term]['graded'] }} of {{ $termCompletions[$term]['total'] }} grades submitted
                            </small>
                        </div>
                    @endforeach
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
                                @foreach($subjectCharts as $subject)
                                    @php
                                        $avgCompletion = array_sum($subject['termPercentages']) / count($subject['termPercentages']);
                                        $statusColor = match(true) {
                                            $avgCompletion === 100 => 'success',
                                            $avgCompletion >= 75 => 'info',
                                            $avgCompletion >= 50 => 'warning',
                                            default => 'danger'
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $subject['code'] }}</td>
                                        <td>{{ $subject['description'] }}</td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $statusColor }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $avgCompletion }}%">
                                                    </div>
                                                </div>
                                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} rounded-pill">
                                                    {{ round($avgCompletion) }}%
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
