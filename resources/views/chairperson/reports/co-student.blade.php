@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => 'Student Outcomes Summary',
        'subtitle' => 'Individual student Course Outcome performance report',
        'icon' => 'bi-person-lines-fill',
        'backRoute' => route('chairperson.reports.co-student'),
        'backLabel' => 'Choose Student'
    ])

    {{-- Student & Subject Info Strip --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center g-3">
                {{-- Student --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-person-fill text-success fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted text-uppercase fw-medium" style="font-size: 0.7rem; letter-spacing: 0.5px;">Student</small>
                            <div class="fw-semibold text-dark">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name ?? '' }}</div>
                        </div>
                    </div>
                </div>
                {{-- Subject --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-journal-text text-primary fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted text-uppercase fw-medium" style="font-size: 0.7rem; letter-spacing: 0.5px;">Course</small>
                            <div class="fw-semibold text-dark">{{ $selectedSubject->subject_code }}</div>
                            <small class="text-muted">{{ $selectedSubject->subject_name }}</small>
                        </div>
                    </div>
                </div>
                {{-- Period --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-calendar3 text-warning fs-5"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted text-uppercase fw-medium" style="font-size: 0.7rem; letter-spacing: 0.5px;">Academic Period</small>
                            <div class="fw-semibold text-dark">{{ $selectedSubject->academicPeriod->academic_year ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $selectedSubject->academicPeriod->semester ?? '' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CO Results Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-table text-success me-2"></i>Course Outcome Scores by Term
            </h6>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr class="table-light">
                            <th style="width: 15%;">
                                <i class="bi bi-calendar-week text-muted me-1"></i>Term
                            </th>
                            @foreach($coColumnsByTerm as $coCode)
                                <th class="text-center">
                                    <span class="fw-bold">{{ $coCode }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($terms as $term)
                            <tr>
                                <td class="fw-semibold">{{ ucfirst($term) }}</td>
                                @foreach($coColumnsByTerm as $coCode)
                                    @php($val = $coResults[$term][$coCode] ?? null)
                                    <td class="text-center">
                                        @if($val)
                                            <span class="badge {{ $val['percent'] >= 75 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill fw-semibold">
                                                {{ number_format($val['percent'], 2) }}%
                                            </span>
                                            <div><small class="text-muted">{{ $val['raw'] }}/{{ $val['max'] }}</small></div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- Overall Row --}}
                        <tr class="table-success">
                            <td class="fw-bold">
                                <i class="bi bi-trophy text-success me-1"></i>Overall
                            </td>
                            @foreach($coColumnsByTerm as $coCode)
                                @php($val = $finalCOs[$coCode] ?? null)
                                <td class="text-center">
                                    @if($val)
                                        <span class="badge {{ $val['percent'] >= 75 ? 'bg-success' : 'bg-danger' }} px-3 py-2 rounded-pill text-white fw-bold">
                                            {{ number_format($val['percent'], 2) }}%
                                        </span>
                                        <div><small class="text-muted">{{ $val['raw'] }}/{{ $val['max'] }}</small></div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="mt-3 d-flex align-items-center gap-4 px-1">
        <div class="d-flex align-items-center">
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill me-2">75%+</span>
            <small class="text-muted">Passing</small>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill me-2">&lt;75%</span>
            <small class="text-muted">Below Standard</small>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-muted me-2">—</span>
            <small class="text-muted">No Data</small>
        </div>
    </div>
</div>
@endsection
