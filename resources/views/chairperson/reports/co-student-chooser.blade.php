@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => 'Student Outcomes Summary',
        'subtitle' => 'Select a course and student to view detailed Course Outcome performance',
        'icon' => 'bi-person-lines-fill',
        'academicYear' => $academicYear,
        'semester' => $semester,
        'backRoute' => route('dashboard'),
        'backLabel' => 'Back to Dashboard'
    ])

    {{-- Single Selection Card --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('chairperson.reports.co-student') }}" id="studentReportForm">
                <div class="row g-4 align-items-end">
                    {{-- Step 1: Course --}}
                    <div class="col-md-5">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.8rem;">1</span>
                            <label for="subject_id" class="form-label fw-semibold mb-0">Select Course</label>
                        </div>
                        <select name="subject_id" id="subject_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Choose a course --</option>
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->subject_code }} – {{ $sub->subject_description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Step 2: Student --}}
                    <div class="col-md-5">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge {{ $selectedSubjectId ? 'bg-success' : 'bg-secondary' }} rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.8rem;">2</span>
                            <label for="student_id" class="form-label fw-semibold mb-0">Select Student</label>
                        </div>
                        <select name="student_id" id="student_id" class="form-select" {{ !$selectedSubjectId ? 'disabled' : '' }}>
                            <option value="">-- Choose a student --</option>
                            @foreach($students as $stu)
                                <option value="{{ $stu->id }}">
                                    {{ $stu->last_name }}, {{ $stu->first_name }} {{ $stu->middle_name ? substr($stu->middle_name, 0, 1).'.' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Generate Button --}}
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100" {{ !$selectedSubjectId ? 'disabled' : '' }}>
                            <i class="bi bi-file-earmark-bar-graph me-1"></i>Generate
                        </button>
                    </div>
                </div>
            </form>

            {{-- Enrolled count hint --}}
            @if($selectedSubjectId && $students->isNotEmpty())
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">
                        <i class="bi bi-people me-1"></i>{{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }} enrolled in this course
                    </small>
                </div>
            @endif
        </div>
    </div>

    @if(!$selectedSubjectId)
        <div class="mt-4">
            <x-empty-state
                icon="bi-arrow-up-circle"
                title="Select a Course"
                message="Choose a course above to see enrolled students and generate their outcome report."
            />
        </div>
    @elseif($students->isEmpty())
        <div class="mt-4">
            <x-empty-state
                icon="bi-person-x"
                title="No Students Enrolled"
                message="No students are enrolled in this course."
            />
        </div>
    @endif
</div>
@endsection
