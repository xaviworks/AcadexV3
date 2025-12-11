@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header Section --}}
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-2" style="color: #2c3e50;">
                    <i class="bi bi-bullseye me-2" style="color: #198754;"></i>Course Outcome Management
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/" style="color: #198754; text-decoration: none;">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page" style="color: #6c757d;">Course Outcomes</li>
                    </ol>
                </nav>
            </div>
            
            {{-- Generate CO Button (Chairperson and GE Coordinator Only) --}}
            @if(Auth::user()->role === 1 || Auth::user()->role === 4)
            <div>
                <button type="button" class="btn btn-success rounded-pill shadow-sm" id="openGenerateCOModalBtn" style="font-weight: 600;">
                    <i class="bi bi-magic me-1"></i>Generate COs
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Year Level Sections --}}
    @if(isset($subjectsByYear) && count($subjectsByYear) > 0)
        <div class="mb-3">
            @if(isset($currentPeriod))
                <small class="text-muted d-block mb-3">
                    <i class="bi bi-calendar3 me-1"></i>{{ $currentPeriod->academic_year }} - {{ $currentPeriod->semester }}
                    @if((Auth::user()->role === 1 || Auth::user()->role === 4) && Auth::user()->course)
                        • {{ Auth::user()->course->course_code }} Program
                    @endif
                </small>
            @endif
        </div>
    @endif

    {{-- Subject Cards Grouped by Year Level --}}
    @if(isset($subjectsByYear) && count($subjectsByYear))
        @foreach($subjectsByYear as $yearLevel => $subjects)
            <div class="mb-4 year-section" id="year-{{ $yearLevel }}" data-year="{{ $yearLevel }}">
                {{-- Year Level Header --}}
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-award me-2" style="color: #198754; font-size: 1.2rem;"></i>
                    <h5 class="fw-bold mb-0" style="color: #2c3e50;">
                        @php
                            $yearLabels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
                        @endphp
                        {{ $yearLabels[$yearLevel] ?? ($yearLevel ? 'Year ' . $yearLevel : 'Unspecified Year') }}
                    </h5>
                    <span class="badge bg-success ms-2 rounded-pill">
                        {{ count($subjects) }} {{ count($subjects) == 1 ? 'subject' : 'subjects' }}
                    </span>
                </div>

                {{-- Subject Cards Grid --}}
                <div class="row g-3" id="subject-selection-year-{{ $yearLevel }}">
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
            </div>
        @endforeach
    @else
        {{-- Enhanced Empty State --}}
        <div class="text-center py-5">
            <div class="card border-0 shadow-sm mx-auto" style="max-width: 500px;">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <div class="p-4 rounded-circle mx-auto d-inline-flex" style="background: linear-gradient(135deg, #198754, #20c997);">
                            <i class="bi bi-search text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3" style="color: #198754;">No Subjects Found</h4>
                    @if(Auth::user()->role === 1 || Auth::user()->role === 4)
                        <p class="text-muted mb-4">
                            No subjects are currently available for your program 
                            <strong style="color: #198754;">{{ Auth::user()->course->course_code ?? 'Unknown' }}</strong> 
                            in the current academic period.
                        </p>
                        <div class="alert alert-light border border-warning">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle text-warning me-3 mt-1"></i>
                                <div>
                                    <strong>What to do:</strong>
                                    <ul class="mb-0 mt-2 text-start">
                                        <li>Contact the administrator to assign subjects to your program</li>
                                        <li>Ensure subjects are properly configured for this academic period</li>
                                        <li>Check if the academic period is correctly set</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-4">
                            No subjects have been assigned to you for the current academic period.
                        </p>
                        <div class="alert alert-light border border-info">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle text-info me-2"></i>
                                <span>Please contact your department chairperson for subject assignments.</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Generate Course Outcomes Modal --}}
@if(Auth::user()->role === 1 || Auth::user()->role === 4)
<div class="modal fade" id="generateCOModal" tabindex="-1" aria-labelledby="generateCOModalLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #198754, #20c997); border-radius: 1rem 1rem 0 0;">
                <h5 class="modal-title text-white fw-bold" id="generateCOModalLabel">
                    <i class="bi bi-magic me-2"></i>Generate Course Outcomes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Display validation errors --}}
                @if($errors->any())
                    <div class="alert alert-danger border-0 mb-3" style="background: rgba(220, 53, 69, 0.1);">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-3 mt-1"></i>
                            <div>
                                <h6 class="text-danger fw-bold mb-2">Validation Error</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li class="text-danger">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="alert alert-info border-0" style="background: rgba(13, 202, 240, 0.1);">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle text-info me-3 mt-1"></i>
                        <div>
                            <h6 class="text-info fw-bold mb-1">Auto-Generate Course Outcomes</h6>
                            <p class="mb-0 text-muted small">
                                This will generate course outcomes for subjects based on their current CO status. 
                                Each CO will have the description: <strong>"Students have achieved 75% of the course outcomes"</strong><br>
                                <strong>Identifiers:</strong> Generated as SubjectCode.1, SubjectCode.2, etc. (e.g., IT102.1, IT102.2)<br>
                                <strong>Maximum limit:</strong> 6 course outcomes per subject (CO1 through CO6)
                            </p>
                        </div>
                    </div>
                </div>

                <form id="generateCOForm" action="{{ route($routePrefix . '.course_outcomes.generate') }}" method="POST">
                    @csrf
                    
                    {{-- Subject Selection --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: #198754;">
                            <i class="bi bi-list-check me-1"></i>Select Generation Mode
                        </label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="generation_mode" id="mode_missing" value="missing_only" checked>
                                    <label class="form-check-label" for="mode_missing">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-plus-circle text-success me-2 mt-1"></i>
                                            <div>
                                                <strong class="text-success">Add to subjects without COs</strong>
                                                <br><small class="text-muted">Only generate for subjects that have 0 course outcomes</small>
                                                <br><small class="text-success"><i class="bi bi-shield-check me-1"></i>Safe option - no data loss</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="generation_mode" id="mode_override" value="override_all">
                                    <label class="form-check-label" for="mode_override">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-exclamation-triangle text-danger me-2 mt-1"></i>
                                            <div>
                                                <strong class="text-danger">Override all existing COs</strong>
                                                <br><small class="text-muted">Replace all existing course outcomes with fresh set of 6 COs (CO1-CO6)</small>
                                                <br><small class="text-danger"><i class="bi bi-shield-exclamation me-1"></i>⚠️ This will permanently delete existing COs!</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Danger Warning for Override Mode --}}
                    <div class="alert alert-danger border-0 mb-4" id="overrideWarning" style="display: none; background: rgba(220, 53, 69, 0.1);">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <h6 class="text-danger fw-bold mb-2">
                                    <i class="bi bi-shield-exclamation me-1"></i>DANGER: This action cannot be undone!
                                </h6>
                                <p class="mb-2 text-danger">
                                    <strong>Override mode will permanently delete ALL existing course outcomes</strong> from the selected subjects and replace them with the standard template.
                                </p>
                                <ul class="mb-3 text-danger small">
                                    <li>All custom course outcome descriptions will be lost</li>
                                    <li>Any associated course outcome attainment data may be affected</li>
                                    <li>Student progress tracking linked to specific COs will be disrupted</li>
                                    <li>This action cannot be reversed</li>
                                </ul>
                                <div class="bg-white p-3 rounded border border-danger">
                                    <label class="form-label fw-bold text-danger mb-2">
                                        <i class="bi bi-key-fill me-1"></i>Confirm your password to proceed:
                                    </label>
                                    <input type="password" class="form-control border-danger" name="password_confirmation" id="passwordConfirmation" 
                                           placeholder="Enter your password to confirm this dangerous action" required disabled>
                                    <div class="form-text text-danger">
                                        <i class="bi bi-info-circle me-1"></i>Password confirmation is required for destructive operations
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Year Level Filter --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: #198754;">
                            <i class="bi bi-mortarboard me-1"></i>Select Year Levels (Optional)
                        </label>
                        <div class="row g-2">
                            <div class="col-auto">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="year_levels[]" value="all" id="year_all" checked>
                                    <label class="form-check-label" for="year_all">All Years</label>
                                </div>
                            </div>
                            @if(isset($subjectsByYear))
                                @foreach($subjectsByYear->keys()->sort() as $year)
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input year-specific" type="checkbox" name="year_levels[]" value="{{ $year }}" id="year_{{ $year }}">
                                        <label class="form-check-label" for="year_{{ $year }}">
                                            @php
                                                $yearLabels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
                                            @endphp
                                            {{ $yearLabels[$year] ?? 'Year ' . $year }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- Preview Section --}}
                    <div class="card border-0" style="background: rgba(248, 249, 250, 0.8);">
                        <div class="card-body p-3">
                            <h6 class="fw-semibold mb-2" style="color: #198754;">
                                <i class="bi bi-eye me-1"></i>Preview: Course Outcomes to be Generated
                            </h6>
                            <div class="mb-2">
                                <small class="text-muted">Course outcomes will be generated to fill missing CO positions (maximum 6 per subject):</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 px-0 py-1">
                                            <small><strong>SubjectCode.1:</strong> Students have achieved 75% of the course outcomes</small>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-1">
                                            <small><strong>SubjectCode.2:</strong> Students have achieved 75% of the course outcomes</small>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-1">
                                            <small><strong>SubjectCode.3:</strong> Students have achieved 75% of the course outcomes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 px-0 py-1">
                                            <small><strong>SubjectCode.4:</strong> Students have achieved 75% of the course outcomes</small>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-1">
                                            <small><strong>SubjectCode.5:</strong> Students have achieved 75% of the course outcomes</small>
                                        </div>
                                        <div class="list-group-item border-0 px-0 py-1">
                                            <small><strong>SubjectCode.6:</strong> Students have achieved 75% of the course outcomes</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info border-0 mt-3 mb-0" style="background: rgba(13, 202, 240, 0.1);">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-info-circle text-info me-2 mt-1"></i>
                                    <div>
                                        <small class="text-info">
                                            <strong>Smart Generation:</strong> The system will automatically identify which CO numbers (1-6) are missing for each subject and generate only those COs. Subjects that already have 6 COs will be skipped.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 bg-light" style="border-radius: 0 0 1rem 1rem;">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success rounded-pill fw-semibold" onclick="submitGenerateForm()" id="generateSubmitBtn">
                    <i class="bi bi-magic me-1"></i>Generate Course Outcomes
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/instructor/course-outcomes-wildcards.js --}}
{{-- Pass PHP data to JavaScript --}}
<script>
    window.pageData = {
        userRole: {{ Auth::user()->role }},
        isChairpersonOrGE: {{ (Auth::user()->role === 1 || Auth::user()->role === 4) ? 'true' : 'false' }},
        hasValidationErrors: {{ $errors->any() ? 'true' : 'false' }},
        hasErrors: {{ $errors->any() ? 'true' : 'false' }},
        oldGenerationMode: '{{ old('generation_mode', '') }}',
        @if(old('year_levels'))
        oldYearLevels: @json(old('year_levels')),
        @else
        oldYearLevels: null,
        @endif
        @if(Auth::user()->role === 1 || Auth::user()->role === 4)
        validatePasswordUrl: '{{ route($routePrefix . ".course_outcomes.validate_password") }}'
        @else
        validatePasswordUrl: ''
        @endif
    };
</script>
@endpush

{{-- Styles: resources/css/instructor/course-outcomes.css --}}

@section('content')