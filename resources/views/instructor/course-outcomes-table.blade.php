@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Page Title --}}
    @if(isset($selectedSubject))
        <div class="mb-2">
            <h4 class="fw-bold mb-0" style="color: #2c3e50;">
                <i class="bi bi-bullseye me-2" style="color: #198754;"></i>
                Course: {{ $selectedSubject->subject_code }} - {{ $selectedSubject->subject_description }}
            </h4>
            <p class="text-muted mb-0 mt-1">Define and manage course outcomes for this course</p>
        </div>
    @endif

    {{-- Breadcrumbs --}}
    @php
        $dashboardRouteName = $routePrefix . '.dashboard';
        $dashboardUrl = \Illuminate\Support\Facades\Route::has($dashboardRouteName)
            ? route($dashboardRouteName)
            : route('dashboard');

        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => $dashboardUrl],
            ['label' => 'View Outcomes', 'url' => route($routePrefix . '.course_outcomes.index')]
        ];
        if(isset($selectedSubject)) {
            $breadcrumbItems[] = ['label' => $selectedSubject->subject_code . ' - ' . $selectedSubject->subject_description];
        }
    @endphp
    <x-breadcrumbs :items="$breadcrumbItems" />

    {{-- Course Outcomes Management Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-bar-chart-fill text-success fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Course Outcomes Management</h5>
                                <p class="text-muted mb-0">
                                    Course: {{ $selectedSubject->subject_code ?? 'N/A' }} - {{ $selectedSubject->subject_description ?? 'N/A' }}
                                    @if($currentPeriod)
                                        | {{ $currentPeriod->academic_year }} - {{ $currentPeriod->semester }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            @php
                                $coCount = $cos ? $cos->count() : 0;
                                $isLimitReached = $coCount >= 6;
                            @endphp
                            
                            {{-- Add Button --}}
                            <div>
                                @if(Auth::user()->isChairperson())
                                    @if($isLimitReached)
                                        <button class="btn btn-outline-secondary" disabled title="Maximum 6 course outcomes reached">
                                            <i class="bi bi-exclamation-triangle me-2"></i>Limit Reached
                                        </button>
                                    @else
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCourseOutcomeModal">
                                            <i class="bi bi-plus-circle me-2"></i>Add Course Outcome
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- No Data Available Section --}}
    @if(!$cos || $cos->count() == 0)
        <div class="row mb-5">
            <div class="col-12">
                <x-empty-state
                    icon="bi-file-earmark-x"
                    title="No Course Outcome Data Available"
                    :message="'No course outcomes have been set for ' . e($selectedSubject->subject_code ?? 'this course') . ' yet.'"
                >
                    <x-slot:actions>
                        @if(Auth::user()->isChairperson())
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCourseOutcomeModal">
                                <i class="bi bi-plus-circle me-2"></i>Create First Course Outcome
                            </button>
                        @endif
                    </x-slot:actions>
                </x-empty-state>
            </div>
        </div>
    @else
        {{-- Course Outcomes Table --}}
        <div class="row mb-5">
            <div class="col-12">
                <x-data-table
                    title="Course Outcomes List"
                    :subtitle="'Course outcomes for ' . ($selectedSubject->subject_code ?? 'this course')"
                    icon="bi-bullseye"
                    table-class="course-outcomes-table"
                    responsive-class="course-outcomes-table-container"
                    :scroll-y="$cos->count() > 5 ? '32rem' : null"
                >
                        @if($cos->count() > 0)
                                    <thead>
                                        <tr>
                                            <th>
                                                <i class="bi bi-hash me-2"></i>CO Code
                                            </th>
                                            <th>
                                                <i class="bi bi-tag me-2"></i>Identifier
                                            </th>
                                            <th>
                                                <i class="bi bi-file-text me-2"></i>Description 
                                                @if(Auth::user()->isChairperson() || Auth::user()->isGECoordinator())
                                                    <small class="text-muted fw-normal">(Double-click to edit)</small>
                                                @else
                                                    <small class="text-muted fw-normal">(Read-only for Instructors)</small>
                                                @endif
                                            </th>
                                            <th class="text-center">
                                                <i class="bi bi-calendar-event me-2"></i>Academic Period
                                            </th>
                                            <th class="text-center">
                                                <i class="bi bi-percent me-2"></i>Target %
                                            </th>
                                            @if(Auth::user()->isChairperson() || Auth::user()->isGECoordinator())
                                                <th class="text-center">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cos as $co)
                                            <tr class="border-bottom">
                                                <td class="fw-bold px-4 text-success">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                                            <i class="bi bi-mortarboard text-success"></i>
                                                        </div>
                                                        {{ $co->co_code }}
                                                    </div>
                                                </td>
                                                <td class="fw-semibold">{{ $co->co_identifier }}</td>
                                                <td class="editable-cell" 
                                                     data-co-id="{{ $co->id }}" 
                                                     data-original-text="{{ $co->description }}"
                                                     @if(Auth::user()->isChairperson() || Auth::user()->isGECoordinator())
                                                         title="Double-click to edit description"
                                                         ondblclick="makeEditable(this)"
                                                     @else
                                                         title="Only the Chairperson can edit the description"
                                                     @endif>
                                                    <div class="description-container @if(!(Auth::user()->isChairperson() || Auth::user()->isGECoordinator())) non-editable @endif">
                                                        <div class="position-relative">
                                                            @if(strlen($co->description) > 100)
                                                                <span class="description-truncated">{{ substr($co->description, 0, 100) }}...</span>
                                                                <span class="description-full" style="display: none;">{{ $co->description }}</span>
                                                                <button type="button" class="expand-toggle" onclick="toggleDescription(this)">Show more</button>
                                                            @else
                                                                {{ $co->description }}
                                                            @endif
                                                            @if(Auth::user()->isChairperson() || Auth::user()->isGECoordinator())
                                                                <div class="edit-indicator">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                    <span class="edit-tooltip">Double-click to edit</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if($co->academicPeriod)
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                            {{ $co->academicPeriod->academic_year }} - {{ $co->academicPeriod->semester }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success fs-6 px-3 py-2">{{ (int) $co->target_percentage }}%</span>
                                                </td>
                                                @if(Auth::user()->isChairperson() || Auth::user()->isGECoordinator())
                                                    <td class="text-center px-4">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                                    onclick="openEditModal({{ $co->id }}, '{{ $co->co_code }}', '{{ $co->co_identifier }}', '{{ addslashes($co->description) }}', {{ (int) $co->target_percentage }})"
                                                                    title="Edit Course Outcome">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    onclick="openDeleteModal({{ $co->id }}, '{{ $co->co_code }}')"
                                                                    title="Delete Course Outcome">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                        @else
                            <x-empty-state
                                icon="bi-mortarboard"
                                title="No Course Outcomes Found"
                                message="Get started by creating your first course outcome for this course."
                                :compact="true"
                            >
                                <x-slot:actions>
                                    @if(Auth::user()->isChairperson())
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCourseOutcomeModal">
                                            <i class="bi bi-plus-circle me-2"></i>Create First Course Outcome
                                        </button>
                                    @endif
                                </x-slot:actions>
                            </x-empty-state>
                        @endif
                </x-data-table>
            </div>
        </div>
    @endif

    {{-- Information Cards Section --}}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="bi bi-question-circle-fill text-success fs-4"></i>
                        </div>
                        <h6 class="fw-bold mb-0">What are Course Outcomes?</h6>
                    </div>
                    <p class="text-muted mb-0">
                        Specific, measurable statements that describe what students should be able to demonstrate, know, or do by the end of the course.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="bi bi-lightbulb-fill text-warning fs-4"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Why Set Course Outcomes?</h6>
                    </div>
                    <ul class="text-muted mb-0 small">
                        <li>Track student learning progress</li>
                        <li>Align assessments with goals</li>
                        <li>Generate performance reports</li>
                        <li>Meet accreditation requirements</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="bi bi-gear-fill text-primary fs-4"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Getting Started</h6>
                    </div>
                    <p class="text-muted mb-0">
                        Click the button above to create your first course outcome. Define specific learning objectives that students should achieve.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Course Outcome Modal --}}
@if(Auth::user()->isChairperson())
<x-modal.form
    id="addCourseOutcomeModal"
    title="Add Course Outcome"
    form="{{ route($routePrefix . '.course_outcomes.store') }}"
>
    @csrf
    <x-slot:icon><i class="bi bi-plus-circle me-1"></i></x-slot:icon>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label fw-semibold">CO Code <span class="text-danger">*</span></label>
                <input type="text" name="co_code" id="co_code" class="form-control bg-light text-muted" readonly aria-readonly="true" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label fw-semibold">Identifier <span class="text-danger">*</span></label>
                <input type="text" name="co_identifier" id="co_identifier" class="form-control bg-light text-muted" readonly aria-readonly="true" required>
            </div>
        </div>
    </div>
    <div class="mb-3 mt-n2">
        <small class="text-muted d-flex align-items-center gap-1">
            <i class="bi bi-lock-fill"></i>
            CO Code and Identifier are auto-assigned and cannot be edited.
        </small>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4" placeholder="Enter the course outcome description..." required></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Target % <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" name="target_percentage" class="form-control" min="0" max="100" step="1" value="75" required>
            <span class="input-group-text">%</span>
        </div>
    </div>
    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id ?? request('subject_id') }}">

    <x-slot:footer>
        <x-modal.actions>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Add Outcome
            </button>
        </x-modal.actions>
    </x-slot:footer>
</x-modal.form>
@endif

<x-modal.form
    id="editCourseOutcomeModal"
    title="Edit Course Outcome"
    form=""
    form-id="editForm"
    variant="default"
>
    @csrf
    @method('PUT')
    <x-slot:icon><i class="bi bi-pencil-square me-1"></i></x-slot:icon>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label fw-semibold">CO Code <span class="text-danger">*</span></label>
                <input type="text" name="co_code" id="edit_co_code" class="form-control bg-light text-muted" readonly aria-readonly="true" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label fw-semibold">Identifier <span class="text-danger">*</span></label>
                <input type="text" name="co_identifier" id="edit_co_identifier" class="form-control bg-light text-muted" readonly aria-readonly="true" required>
            </div>
        </div>
    </div>
    <div class="mb-3 mt-n2">
        <small class="text-muted d-flex align-items-center gap-1">
            <i class="bi bi-lock-fill"></i>
            CO Code and Identifier are auto-assigned and cannot be edited.
        </small>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
        <textarea name="description" id="edit_description" class="form-control" rows="4" placeholder="Enter the course outcome description..." required></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Target % <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" name="target_percentage" id="edit_target_percentage" class="form-control" min="0" max="100" step="1" required>
            <span class="input-group-text">%</span>
        </div>
    </div>

    <x-slot:footer>
        <x-modal.actions>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-2"></i>Update Outcome
            </button>
        </x-modal.actions>
    </x-slot:footer>
</x-modal.form>

<x-modal.destructive id="deleteCourseOutcomeModal" title="Confirm Deletion" form="" form-id="deleteForm" body-class="text-center">
    @csrf
    @method('DELETE')
    <x-slot:icon><i class="bi bi-exclamation-triangle me-1"></i></x-slot:icon>
    <div class="mb-3">
        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
    </div>
    <h6 class="mb-3">Are you sure you want to delete this course outcome?</h6>
    <div class="alert alert-warning border-0 text-start">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle text-warning me-2"></i>
            <div>
                <strong>Course Outcome:</strong> <span id="delete_co_code" class="fw-bold text-danger"></span><br>
                <small class="text-muted">This action cannot be undone and will remove all associated activities and scores.</small>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <x-modal.actions destructive-text="Delete Permanently" />
    </x-slot:footer>
</x-modal.destructive>
@endsection

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/instructor/course-outcomes-table.js --}}
{{-- Pass PHP data to JavaScript --}}
<script>
@if(isset($selectedSubject))
    window.pageData = {
        subjectCode: '{{ $selectedSubject->subject_code }}',
        userCanEdit: {{ (Auth::user()->isChairperson() || Auth::user()->isGECoordinator()) ? 'true' : 'false' }},
        routePrefix: '{{ $routePrefix }}'
    };
@endif
</script>
@endpush

{{-- Styles: resources/css/instructor/course-outcomes.css --}}
