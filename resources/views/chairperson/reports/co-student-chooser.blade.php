@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => 'Student Outcomes Summary',
        'subtitle' => 'Select a subject and student to view detailed Course Outcome performance',
        'icon' => 'bi-person-lines-fill',
        'academicYear' => $academicYear,
        'semester' => $semester,
        'backRoute' => route('dashboard'),
        'backLabel' => 'Back to Dashboard'
    ])

    <div class="row g-4">
        {{-- Subject Selection Card --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-primary-subtle me-3">
                            <i class="bi bi-journal-text text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-semibold mb-0">Step 1: Select Course</h5>
                            <small class="text-muted">Choose the course to analyze</small>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('chairperson.reports.co-student') }}">
                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Course</label>
                            <select name="subject_id" id="subject_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Select Course --</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->subject_code }} – {{ $sub->subject_description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Student Selection Card --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-3 p-2 bg-success-subtle me-3">
                            <i class="bi bi-person-check text-success fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-semibold mb-0">Step 2: Select Student</h5>
                            <small class="text-muted">Choose the student to view report</small>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('chairperson.reports.co-student') }}">
                        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student</label>
                            <select name="student_id" id="student_id" class="form-select" {{ !$selectedSubjectId ? 'disabled' : '' }}>
                                <option value="">-- Select Student --</option>
                                @foreach($students as $stu)
                                    <option value="{{ $stu->id }}">
                                        {{ $stu->last_name }}, {{ $stu->first_name }} {{ $stu->middle_name ? substr($stu->middle_name, 0, 1).'.' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100" {{ !$selectedSubjectId ? 'disabled' : '' }}>
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Generate Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!$selectedSubjectId)
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-arrow-up-circle text-muted fs-1 d-block mb-3"></i>
                <p class="text-muted mb-0">Please select a subject first to see enrolled students</p>
            </div>
        </div>
    @elseif($students->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted fs-1 d-block mb-3"></i>
                <p class="text-muted mb-0">No students enrolled in this subject</p>
            </div>
        </div>
    @endif
</div>
@endsection
