@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4"
     x-data="instructorDashboard()"
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
                            <h3 class="fw-bold text-primary mb-0" x-text="data.instructorStudents">{{ $instructorStudents }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Currently enrolled</p>
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
                            <h3 class="fw-bold text-info mb-0" x-text="data.enrolledSubjectsCount">{{ $enrolledSubjectsCount }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Current semester</p>
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
                            <h3 class="fw-bold text-success mb-0" x-text="data.totalPassedStudents">{{ $totalPassedStudents }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Final grades</p>
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
                            <h3 class="fw-bold text-danger mb-0" x-text="data.totalFailedStudents">{{ $totalFailedStudents }}</h3>
                        </div>
                    </div>
                    <p class="text-muted small mb-0"><i class="bi bi-arrow-right"></i> Final grades</p>
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

                    <template x-for="term in ['prelim', 'midterm', 'prefinal', 'final']" :key="term">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-capitalize mb-0" x-text="term.charAt(0).toUpperCase() + term.slice(1)"></h6>
                                <span :class="'text-' + getTermColor(term)" x-text="getTermProgress(term) + '%'"></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar"
                                     :class="'bg-' + getTermColor(term)"
                                     :style="'width: ' + getTermProgress(term) + '%'"
                                     :aria-valuenow="getTermProgress(term)"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted" x-text="getTermGraded(term) + ' of ' + getTermTotal(term) + ' grades submitted'"></small>
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
                                                    <div class="progress-bar"
                                                         :class="'bg-' + getSubjectColor(subject)"
                                                         role="progressbar"
                                                         :style="'width: ' + getSubjectAvg(subject) + '%'">
                                                    </div>
                                                </div>
                                                <span class="badge rounded-pill"
                                                      :class="'bg-' + getSubjectColor(subject) + '-subtle text-' + getSubjectColor(subject)"
                                                      x-text="Math.round(getSubjectAvg(subject)) + '%'">
                                                </span>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.pageData = { subjectCharts: @json($subjectCharts) };

    function instructorDashboard() {
        // Keep Chart.js data and instance COMPLETELY outside Alpine to avoid proxy conflicts
        let _rawSubjectCharts = JSON.parse(JSON.stringify(window.pageData.subjectCharts));
        let _chart = null;

        const COLORS = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1'];

        function buildDatasets(subjects) {
            return (subjects || []).map((s, i) => ({
                label: s.code,
                data: Array.isArray(s.termPercentages) ? [...s.termPercentages] : [],
                borderColor: COLORS[i % COLORS.length],
                backgroundColor: COLORS[i % COLORS.length] + '20',
                tension: 0.3,
                fill: true,
            }));
        }

        function createChart() {
            const canvas = document.getElementById('subjectPerformanceChart');
            if (!canvas || !_rawSubjectCharts || _rawSubjectCharts.length === 0) return;
            // Destroy any existing chart on this canvas
            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();
            if (_chart) { try { _chart.destroy(); } catch(e) {} _chart = null; }

            _chart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Prelim', 'Midterm', 'Prefinal', 'Final'],
                    datasets: buildDatasets(_rawSubjectCharts),
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, max: 100, title: { display: true, text: 'Completion (%)' } },
                        x: { title: { display: true, text: 'Term' } }
                    }
                }
            });
        }

        function refreshChart(newSubjects) {
            _rawSubjectCharts = newSubjects;
            if (!_chart) { createChart(); return; }
            _chart.data.datasets = buildDatasets(_rawSubjectCharts);
            _chart.update('none');
        }

        return {
            polling: false,
            pollInterval: null,
            data: {
                instructorStudents: @json($instructorStudents),
                enrolledSubjectsCount: @json($enrolledSubjectsCount),
                totalPassedStudents: @json($totalPassedStudents),
                totalFailedStudents: @json($totalFailedStudents),
                termCompletions: @json($termCompletions),
                subjectCharts: @json($subjectCharts),
            },
            _lastJson: '',
            init() {
                this.polling = true;
                this.$nextTick(() => createChart());
                this.fetchData();
                this.startPolling();
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) { clearInterval(this.pollInterval); }
                    else { this.fetchData(); this.startPolling(); }
                });
            },
            destroy() {
                if (this.pollInterval) clearInterval(this.pollInterval);
                if (_chart) { try { _chart.destroy(); } catch(e) {} _chart = null; }
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
                    const text = await r.text();
                    if (text === this._lastJson) return;
                    this._lastJson = text;
                    const d = JSON.parse(text);
                    this.data.instructorStudents = d.instructorStudents;
                    this.data.enrolledSubjectsCount = d.enrolledSubjectsCount;
                    this.data.totalPassedStudents = d.totalPassedStudents;
                    this.data.totalFailedStudents = d.totalFailedStudents;
                    this.data.termCompletions = d.termCompletions;
                    this.data.subjectCharts = d.subjectCharts;
                    refreshChart(d.subjectCharts);
                } catch (e) { console.error('Dashboard poll error:', e); }
            },
            getTermProgress(term) {
                const tc = this.data.termCompletions[term];
                if (!tc || tc.total === 0) return 0;
                return Math.round((tc.graded / tc.total) * 100);
            },
            getTermGraded(term) { return this.data.termCompletions[term]?.graded ?? 0; },
            getTermTotal(term) { return this.data.termCompletions[term]?.total ?? 0; },
            getTermColor(term) {
                const p = this.getTermProgress(term);
                if (p === 100) return 'success';
                if (p > 75) return 'info';
                if (p > 50) return 'warning';
                return 'danger';
            },
            getSubjectAvg(subject) {
                if (!subject.termPercentages || subject.termPercentages.length === 0) return 0;
                return subject.termPercentages.reduce((a, b) => a + b, 0) / subject.termPercentages.length;
            },
            getSubjectColor(subject) {
                const avg = this.getSubjectAvg(subject);
                if (avg === 100) return 'success';
                if (avg >= 75) return 'info';
                if (avg >= 50) return 'warning';
                return 'danger';
            },
        };
    }
</script>
@endpush
