@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => $course->course_code . ' – Course Outcomes Summary',
        'subtitle' => $course->course_description,
        'icon' => 'bi-book',
        'academicYear' => $academicYear,
        'semester' => $semester
    ])

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Course Outcomes Reports', 'url' => route('vpaa.reports.co-course')],
        ['label' => $course->course_code]
    ]" />

    {{-- CO Results Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            @if(empty($subjectCOs))
                <x-empty-state
                    :compact="true"
                    icon="bi-journal-x"
                    title="No Courses Found"
                    message="No Courses found for this course in the selected academic period."
                />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="table-light border-bottom border-2">
                                <th style="width: 35%;" class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-2 p-2 bg-primary-subtle me-2">
                                            <i class="bi bi-book-half text-primary"></i>
                                        </div>
                                        <span class="fw-bold">Course</span>
                                    </div>
                                </th>
                                @for($i=1; $i<=6; $i++)
                                    <th class="text-center fw-bold">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-mortarboard-fill text-success mb-1"></i>
                                            <span>CO{{ $i }}</span>
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectCOs as $item)
                                @php($subject = $item['subject'])
                                @php($co = $item['co'])
                                <tr class="border-bottom">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-2">
                                            <div class="rounded-2 p-2 bg-light me-3">
                                                <i class="bi bi-journal-code text-primary fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $subject->subject_code }}</div>
                                                <small class="text-muted">{{ $subject->subject_description }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    @for($i=1; $i<=6; $i++)
                                        @php($val = $co[$i] ?? null)
                                        <td class="text-center py-3">
                                            @if($val)
                                                @php($threshold = (int) ($val['target_percentage'] ?? 75))
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge {{ $val['percent'] >= $threshold ? 'bg-success text-white' : 'bg-danger text-white' }} px-3 py-2 rounded-pill mb-1 fs-6">
                                                        {{ number_format($val['percent'], 1) }}%
                                                    </span>
                                                    <small class="text-muted">{{ $val['raw'] }}/{{ $val['max'] }} | target {{ $threshold }}%</small>
                                                </div>
                                            @else
                                                <span class="text-muted fs-5">—</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="mt-4 p-3 bg-light rounded-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-info-circle text-primary me-2"></i>Performance Legend
                            </h6>
                            <div class="d-flex gap-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success text-white px-2 py-1 me-2">>= target</span>
                                    <small class="text-muted">On Track</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-danger text-white px-2 py-1 me-2">&lt; target</span>
                                    <small class="text-muted">Needs Improvement</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <small class="text-muted">
                                <i class="bi bi-calculator me-1"></i>
                                Showing {{ count($subjectCOs) }} subject{{ count($subjectCOs) != 1 ? 's' : '' }} with Course Outcome data
                            </small>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

