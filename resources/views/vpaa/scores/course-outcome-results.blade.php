@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-bar-chart-line me-2"></i>{{ $selectedSubject->subject_code }} - Course Outcome Results
            </h2>
            <p class="text-muted mb-0">{{ $selectedSubject->subject_description }}</p>
        </div>
    </div>

    {{-- View-only banner --}}
    <div class="alert alert-success bg-success-subtle border-0 text-dark d-flex align-items-center" role="alert">
        <i class="bi bi-eye me-2"></i>
        VPAA view is read-only. Editing is unavailable here.
    </div>

    {{-- Reuse instructor results UI (wrapped to hide non-view actions) --}}
    <div class="vpaa-readonly">
    @include('instructor.scores.course-outcome-results', [
        'students' => $students,
        'coResults' => $coResults,
        'coColumnsByTerm' => $coColumnsByTerm,
        'coDetails' => $coDetails,
        'finalCOs' => $finalCOs,
        'terms' => $terms,
        'subjectId' => $subjectId,
        'selectedSubject' => $selectedSubject,
    ])
    </div>
</div>
@endsection

{{-- Styles: resources/css/vpaa/common.css --}}

{{-- JavaScript moved to: resources/js/pages/vpaa/scores/course-outcome-results.js --}}
