@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => 'Program Outcomes Summary',
        'subtitle' => 'Course Outcome compliance across all courses in ' . ($department->department_description ?? 'your department'),
        'icon' => 'bi-diagram-3',
        'academicYear' => $academicYear,
        'semester' => $semester,
        'backRoute' => route('dashboard'),
        'backLabel' => 'Back to Dashboard'
    ])

    @if(!$department)
        <x-inline-alert type="warning" message="Your account has no department assigned. Please contact admin." />
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start" style="width: 30%;">
                                <i class="bi bi-journal-text text-primary me-2"></i>Course
                            </th>
                            @for($i=1; $i<=6; $i++)
                                <th class="text-center">CO{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byCourse as $courseId => $row)
                            <tr class="hover:bg-light">
                                <td class="text-start">
                                    <div class="fw-semibold">{{ $row['course']->course_code ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $row['course']->course_description ?? '' }}</small>
                                </td>
                                @for($i=1; $i<=6; $i++)
                                    @php($val = $row['co'][$i] ?? null)
                                    <td class="text-center">
                                        @if($val)
                                            <span class="badge {{ $val['percent'] >= 75 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill">
                                                {{ number_format($val['percent'], 2) }}%
                                            </span>
                                            <div><small class="text-muted">{{ $val['raw'] }}/{{ $val['max'] }}</small></div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                                    <p class="text-muted mb-0">No courses or assessed COs found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
