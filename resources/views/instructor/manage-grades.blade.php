@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div id="grade-section">
        @if (!$subject)
            @if(count($subjects))
                <div class="px-4 pt-4 pb-2">
                    <h1 class="h4 fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-card-checklist text-success me-2" style="font-size: 1.5rem;"></i>
                        <span>Manage Grades</span>
                    </h1>
                </div>
                <div class="row g-4 px-4 py-4" id="subject-selection">
                    @foreach($subjects as $subjectItem)
                        <div class="col-md-4">
                            <div
                                class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden"
                                data-url="{{ route('instructor.grades.index') }}?subject_id={{ $subjectItem->id }}&term=prelim"
                                style="cursor: pointer;"
                            >
                                {{-- Top header --}}
                                <div class="position-relative" style="height: 80px;">
                                    <div class="subject-circle position-absolute start-50 translate-middle"
                                        style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                        <h5 class="mb-0 text-white fw-bold">{{ $subjectItem->subject_code }}</h5>
                                    </div>
                                </div>

                                {{-- Card body --}}
                                <div class="card-body pt-5 text-center">
                                    <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $subjectItem->subject_description }}">
                                        {{ $subjectItem->subject_description }}
                                    </h6>

                                    {{-- Footer badges --}}
                                    <div class="d-flex justify-content-between align-items-center mt-4 px-2">
                                        <span class="badge bg-light border text-secondary px-3 py-2 rounded-pill">
                                            👥 {{ $subjectItem->students_count }} Students
                                        </span>
                                        <span class="badge px-3 py-2 fw-semibold text-uppercase rounded-pill
                                            @if($subjectItem->grade_status === 'completed') bg-success
                                            @elseif($subjectItem->grade_status === 'pending') bg-warning text-dark
                                            @else bg-secondary
                                            @endif">
                                            @if($subjectItem->grade_status === 'completed')
                                                 Completed
                                            @elseif($subjectItem->grade_status === 'pending')
                                                 Pending
                                            @else
                                                 Not Started
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning text-center mt-5 rounded">
                    No subjects have been assigned to you yet.
                </div>
            @endif
        @else
            @include('instructor.partials.term-stepper')
            @include('instructor.partials.activity-header', [
                'subject' => $subject,
                'term' => $term,
                'activityTypes' => $activityTypes,
                'componentStatus' => $componentStatus ?? null,
            ])
            <form id="gradeForm" method="POST" action="{{ route('instructor.grades.store') }}" data-no-page-loader="true">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <input type="hidden" name="term" value="{{ $term }}">
                @include('instructor.partials.grade-table')
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            notify.success('{{ session('success') }}');
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            notify.error('{{ session('error') }}');
        });
    </script>
@endif
@endsection

{{-- Styles: resources/css/instructor/common.css, resources/css/instructor/subject-cards.css --}}
{{-- JavaScript: resources/js/pages/instructor/manage-grades.js --}}

@push('scripts')
@include('instructor.partials.grade-script')

<style>
.subject-card h6 {
    transition: color 0.3s;
}

.subject-card:hover h6 {
    color: #4da674 !important;
}

.subject-card .badge {
    transition: all 0.3s;
}

.subject-card:hover .badge {
    transform: scale(1.05);
}
</style>
@endpush
