@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vpaa/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vpaa.course-outcome-attainment') }}">Course Outcome Attainment Results</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $selectedSubject->subject_code }} - {{ $selectedSubject->subject_description }}
            </li>
        </ol>
    </nav>

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
