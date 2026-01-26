@extends('layouts.app')

@section('content')
<div class="container-fluid px-0" data-page="instructor-manage-grades">
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
                                data-subject-id="{{ $subjectItem->id }}"
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
                                                ✔ Completed
                                            @elseif($subjectItem->grade_status === 'pending')
                                                ⏳ Pending
                                            @else
                                                ⭕ Not Started
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning text-center mt-5 rounded" id="no-subjects-alert">
                    No subjects have been assigned to you yet.
                    <p class="small mb-0 mt-2 text-muted">
                        <i class="bi bi-info-circle"></i> This page will automatically update when subjects are assigned.
                    </p>
                </div>
                {{-- Hidden container for live updates to add cards --}}
                <div class="row g-4 px-4 py-4 d-none" id="subject-selection"></div>
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
/* Base card transitions */
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

/* ===== Live Update Animations ===== */

/* New card glow effect */
.subject-card-new {
    animation: cardAppear 0.5s ease-out forwards;
}

.subject-card-glow {
    box-shadow: 0 0 20px rgba(77, 166, 116, 0.5), 0 4px 15px rgba(0,0,0,0.1) !important;
    animation: pulseGlow 1.5s ease-in-out 2;
}

@keyframes cardAppear {
    0% {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes pulseGlow {
    0%, 100% {
        box-shadow: 0 0 15px rgba(77, 166, 116, 0.4), 0 4px 15px rgba(0,0,0,0.1);
    }
    50% {
        box-shadow: 0 0 30px rgba(77, 166, 116, 0.7), 0 4px 20px rgba(0,0,0,0.15);
    }
}

/* Badge update flash */
.badge-updated {
    animation: badgeFlash 0.6s ease-out;
}

@keyframes badgeFlash {
    0% {
        transform: scale(1);
        background-color: rgba(77, 166, 116, 0.3);
    }
    50% {
        transform: scale(1.1);
        background-color: rgba(77, 166, 116, 0.5);
    }
    100% {
        transform: scale(1);
    }
}

/* Card removal animation */
.subject-card-removing {
    animation: cardRemove 0.3s ease-out forwards;
}

@keyframes cardRemove {
    0% {
        opacity: 1;
        transform: scale(1);
    }
    100% {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
}

/* Subject circle animation for new cards */
.subject-card-new .subject-circle {
    animation: circlePopIn 0.4s ease-out 0.2s both;
}

@keyframes circlePopIn {
    0% {
        transform: translate(-50%, -50%) scale(0);
    }
    70% {
        transform: translate(-50%, -50%) scale(1.1);
    }
    100% {
        transform: translate(-50%, -50%) scale(1);
    }
}
</style>
@endpush
