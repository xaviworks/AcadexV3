@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-2 d-flex align-items-center">
        <i class="bi bi-bullseye text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Course Outcomes</span>
    </h1>
    <p class="text-muted mb-4">Select a subject to view and manage its course outcomes</p>

    {{-- Breadcrumbs --}}
    @php
        $breadcrumbItems = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Course Outcomes']
        ];
        
        if(request('subject_id') && isset($subjects)) {
            $selectedSubject = $subjects->firstWhere('id', request('subject_id'));
            if($selectedSubject) {
                $breadcrumbItems[] = ['label' => $selectedSubject->subject_code . ' - ' . $selectedSubject->subject_description];
            }
        }
    @endphp
    <x-breadcrumbs :items="$breadcrumbItems" />

    {{-- Subject Wild Cards --}}
    @if(isset($subjects) && count($subjects))
        <div class="row g-4 px-4 py-4" id="subject-selection">
            @foreach($subjects as $subjectItem)
                <div class="col-md-4">
                    <div
                        class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden"
                        data-url="{{ route($routePrefix . '.course_outcomes.index', ['subject_id' => $subjectItem->id]) }}"
                        style="cursor: pointer;"
                    >
                        <div class="position-relative" style="height: 80px;">
                            <div class="subject-circle position-absolute start-50 translate-middle"
                                style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                <h5 class="mb-0 text-white fw-bold">{{ $subjectItem->subject_code }}</h5>
                            </div>
                        </div>
                        <div class="card-body pt-5 text-center">
                            <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $subjectItem->subject_description }}">
                                {{ $subjectItem->subject_description }}
                            </h6>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add Course Outcome Button --}}
    <div class="mb-3 text-end">
        @if(Auth::user()->isChairperson())
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCourseOutcomeModal">
                + Add Course Outcome
            </button>
        @endif
    </div>

    {{-- Course Outcomes Table Section --}}
    <div class="mt-4">
        @if(request('subject_id'))
            @if($cos && $cos->count())
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>CO Code</th>
                                    <th>Identifier</th>
                                    <th>Description</th>
                                    <th>Academic Period</th>
                                    <th>Percentage</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cos as $co)
                                    <tr>
                                        <td class="fw-semibold">{{ $co->co_code }}</td>
                                        <td>{{ $co->co_identifier }}</td>
                                        <td>{{ $co->description }}</td>
                                        <td>
                                            @if($co->academicPeriod)
                                                {{ $co->academicPeriod->academic_year }} - {{ $co->academicPeriod->semester }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-success">75%</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route($routePrefix . '.course_outcomes.edit', $co->id) }}" class="btn btn-success btn-sm">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <form action="{{ route($routePrefix . '.course_outcomes.destroy', $co->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this course outcome?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-warning bg-warning-subtle text-dark border-0 text-center">
                    No course outcomes found for this subject.
                </div>
            @endif
        @else
            <div class="alert alert-info bg-info-subtle text-dark border-0 text-center">
                Please select a subject to view its course outcomes.
            </div>
        @endif
    </div>
</div>

{{-- Add Course Outcome Modal --}}
@if(Auth::user()->isChairperson())
<div class="modal fade" id="addCourseOutcomeModal" tabindex="-1" aria-labelledby="addCourseOutcomeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route($routePrefix . '.course_outcomes.store') }}">
            @csrf
            <div class="modal-content shadow-sm border-0 rounded-3">
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="addCourseOutcomeModalLabel">➕ Add Course Outcome</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">CO Code <span class="text-danger">*</span></label>
                        <input type="text" name="co_code" id="co_code" class="form-control" readonly style="background-color: #f8f9fa;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Identifier <span class="text-danger">*</span></label>
                        <input type="text" name="co_identifier" id="co_identifier" class="form-control" readonly style="background-color: #f8f9fa;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Outcome</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

{{-- Pass PHP data to JavaScript --}}
@push('scripts')
<script>
    // Set page data for external JS to use
    window.courseOutcomesPageData = {
        @if(request('subject_id') && isset($subjects))
            @php
                $selectedSubject = $subjects->firstWhere('id', request('subject_id'));
            @endphp
            @if($selectedSubject)
                subjectCode: '{{ $selectedSubject->subject_code }}'
            @else
                subjectCode: ''
            @endif
        @else
            subjectCode: ''
        @endif
    };
</script>
@endpush

{{-- Styles: resources/css/instructor/course-outcomes.css --}}
{{-- JavaScript: resources/js/pages/instructor/course-outcomes.js --}}
<style>
/* Enhanced Card Animations */
.enhanced-card {
    position: relative;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.enhanced-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 12px 24px rgba(77, 166, 116, 0.3) !important;
}

/* Shimmer Effect */
.shimmer-overlay {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.enhanced-card:hover .shimmer-overlay {
    left: 100%;
}

/* Card Header Enhancement */
.card-header-enhanced {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.enhanced-card:hover .card-header-enhanced {
    background: linear-gradient(135deg, #3db872, #2da05f) !important;
}

/* Icon Animation */
.card-icon {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.enhanced-card:hover .card-icon {
    transform: translate(-50%, -50%) rotate(360deg) scale(1.1) !important;
}

/* Card Body Enhancement */
.card-body-enhanced {
    transition: background 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.enhanced-card:hover .card-body-enhanced {
    background: linear-gradient(135deg, #ffffff, #f0fdf4);
}

/* Text Color Change */
.card-title-enhanced {
    transition: color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.enhanced-card:hover .card-title-enhanced {
    color: #4da674 !important;
}

/* Badge Enhancement */
.badge-enhanced {
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.enhanced-card:hover .badge-enhanced {
    transform: scale(1.05);
}

/* Ripple Effect */
.enhanced-card::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(77, 166, 116, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.enhanced-card:active::after {
    width: 300px;
    height: 300px;
}
</style>
