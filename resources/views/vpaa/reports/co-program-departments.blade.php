@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => 'Program Outcomes Summary',
        'subtitle' => 'Select a department to view Course Outcome compliance',
        'icon' => 'bi-diagram-3',
        'academicYear' => $academicYear ?? null,
        'semester' => $semester ?? null
    ])

    <div class="row g-4 px-4 py-2">
        @forelse($departments as $dept)
            <div class="col-md-4">
                <div class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden"
                     data-url="{{ route('vpaa.reports.co-program') }}?department_id={{ $dept->id }}"
                     style="cursor: pointer;">
                    <div class="position-relative" style="height: 80px;">
                        <div class="subject-circle position-absolute start-50 translate-middle"
                            style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <h5 class="mb-0 text-white fw-bold">{{ $dept->department_code }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-5 text-center">
                        <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $dept->department_description }}">
                            {{ $dept->department_description }}
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
                    title="No Departments Found"
                    message="Departments data is required to browse reports."
                />
            </div>
        @endforelse
    </div>
</div>

{{-- Styles: resources/css/vpaa/reports.css --}}
{{-- JavaScript: resources/js/pages/vpaa/reports/co-course-chooser.js --}}
@endsection
