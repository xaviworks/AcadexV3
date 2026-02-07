@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => 'Course Outcomes Summary',
        'subtitle' => 'Select a course to view detailed Course Outcome compliance',
        'icon' => 'bi-book',
        'academicYear' => $academicYear,
        'semester' => $semester,
        'backRoute' => route('dashboard'),
        'backLabel' => 'Back to Dashboard'
    ])

    <div class="row g-4 px-4 py-2">
        @forelse($courses as $c)
            <div class="col-md-4">
                <div class="course-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden" 
                     data-url="{{ route('chairperson.reports.co-course') }}?course_id={{ $c->id }}"
                     style="cursor: pointer;">
                    <div class="position-relative" style="height: 80px;">
                        <div class="course-circle position-absolute start-50 translate-middle"
                            style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <h5 class="mb-0 text-white fw-bold">{{ $c->course_code }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-5 text-center">
                        <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $c->course_description }}">
                            {{ $c->course_description }}
                        </h6>
                        <div class="mt-3">
                            <span class="btn btn-success">
                                <i class="bi bi-arrow-right-circle me-1"></i> View Courses
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <x-empty-state
                    icon="bi-journal-x"
                    title="No Courses Found"
                    message="No courses available at this time."
                />
            </div>
        @endforelse
    </div>
</div>
{{-- Styles: resources/css/chairperson/reports.css --}}
{{-- JavaScript: resources/js/pages/chairperson/reports/co-course-chooser.js --}}
@endsection
