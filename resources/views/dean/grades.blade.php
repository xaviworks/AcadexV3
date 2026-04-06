@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-bar-chart-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Students' Final Grades</span>
    </h1>
    <p class="text-muted mb-4">Select a course, instructor, and subject to view students' final grades.</p>

    {{-- Breadcrumb Navigation --}}
    @php
        $breadcrumbItems = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Select Course', 'url' => route('dean.grades')]
        ];

        if (!empty(request('course_id')) && empty(request('instructor_id'))) {
            $breadcrumbItems[] = ['label' => 'Select Instructor'];
        } elseif (!empty(request('instructor_id')) && empty(request('subject_id'))) {
            $breadcrumbItems[] = ['label' => 'Select Course'];
        } elseif (!empty(request('subject_id'))) {
            $breadcrumbItems[] = ['label' => 'Students\' Final Grades'];
        }
    @endphp
    <x-breadcrumbs :items="$breadcrumbItems" />

    {{-- Step 1: Course Selection --}}
    @if(empty(request('course_id')))
        @if($courses->isEmpty())
            <x-empty-state
                icon="bi-journal-x"
                title="No Courses Found"
                message="No courses are available for your department in the selected academic period."
            />
        @else
            <div class="row g-4 px-4 py-4">
                @foreach($courses as $course)
                    <div class="col-md-4">
                        <a href="{{ route('dean.grades', ['course_id' => $course->id]) }}" class="text-decoration-none">
                            <div class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                <div class="position-relative" style="height: 80px;">
                                    <div class="subject-circle position-absolute start-50 translate-middle"
                                         style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                        <h5 class="mb-0 text-white fw-bold">{{ $course->course_code }}</h5>
                                    </div>
                                </div>
                                <div class="card-body pt-5 text-center">
                                    <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $course->course_description }}">
                                        {{ $course->course_description }}
                                    </h6>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

    {{-- Step 2: Instructor Selection --}}
    @elseif(empty(request('instructor_id')))
        @if($instructors->isEmpty())
            <x-empty-state
                icon="bi-people"
                title="No Instructors Available"
                message="No active instructors are teaching this course in the selected academic period."
            />
        @else
            <div class="row g-4 px-4 py-4">
                @foreach($instructors as $instructor)
                    <div class="col-md-4">
                        <a href="{{ route('dean.grades', ['course_id' => request('course_id'), 'instructor_id' => $instructor->id]) }}" class="text-decoration-none">
                            <div class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                <div class="position-relative" style="height: 80px;">
                                    <div class="subject-circle position-absolute start-50 translate-middle"
                                         style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 10%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                        <i class="bi bi-person-circle text-white" style="font-size: 40px;"></i>
                                    </div>
                                </div>
                                <div class="card-body pt-5 text-center">
                                    <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $instructor->last_name }}, {{ $instructor->first_name }}">
                                        {{ $instructor->last_name }}, {{ $instructor->first_name }}
                                    </h6>
                                    <div class="mt-2">
                                        <span class="badge bg-primary text-white">Instructor</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

    {{-- Step 3: Course Selection --}}
    @elseif(empty(request('subject_id')))
        @if($subjects->isEmpty())
            <x-empty-state
                icon="bi-journal-x"
                title="No Courses Found"
                message="This instructor has no assigned subjects for the selected academic period."
            />
        @else
            <div class="row g-4 px-4 py-4">
                @foreach($subjects as $subject)
                    <div class="col-md-4">
                        <a href="{{ route('dean.grades', ['course_id' => request('course_id'), 'instructor_id' => request('instructor_id'), 'subject_id' => $subject->id]) }}" class="text-decoration-none">
                            <div class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
                                <div class="position-relative" style="height: 80px;">
                                    <div class="subject-circle position-absolute start-50 translate-middle"
                                         style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                        <h5 class="mb-0 text-white fw-bold">{{ $subject->subject_code }}</h5>
                                    </div>
                                </div>
                                <div class="card-body pt-5 text-center">
                                    <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $subject->subject_description }}">
                                        {{ $subject->subject_description }}
                                    </h6>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

    {{-- Step 4: Final Grades --}}
    @else
        @if ($students->count())
            <div class="bg-white shadow-lg rounded-4 overflow-x-auto mt-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Student Name</th>
                            <th class="text-center">Prelim</th>
                            <th class="text-center">Midterm</th>
                            <th class="text-center">Prefinal</th>
                            <th class="text-center">Final</th>
                            <th class="text-center text-success">Final Average</th>
                            <th class="text-center">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            @php
                                $isDropped = (bool) ($student->pivot->is_deleted ?? false);
                                $termGrades = $student->termGrades->keyBy('term_id');
                                $prelim = $termGrades[1]->term_grade ?? null;
                                $midterm = $termGrades[2]->term_grade ?? null;
                                $prefinal = $termGrades[3]->term_grade ?? null;
                                $final = $termGrades[4]->term_grade ?? null;
                                $hasAll = !is_null($prelim) && !is_null($midterm) && !is_null($prefinal) && !is_null($final);
                                $average = $hasAll ? round(($prelim + $midterm + $prefinal + $final) / 4) : null;
                                $remarks = $average !== null ? ($average >= 75 ? 'Passed' : 'Failed') : null;
                            @endphp
                            <tr class="hover:bg-light{{ $isDropped ? ' student-dropped' : '' }}" style="{{ $isDropped ? 'opacity:0.75;background-color:#fff8f8;' : '' }}">
                                <td style="{{ $isDropped ? 'border-left:4px solid #dc3545;' : '' }}">
                                    <span class="{{ $isDropped ? 'text-muted' : '' }}">{{ $student->last_name }}, {{ $student->first_name }}</span>
                                </td>
                                <td class="text-center">{{ $prelim !== null ? round($prelim) : '-' }}</td>
                                <td class="text-center">{{ $midterm !== null ? round($midterm) : '-' }}</td>
                                <td class="text-center">{{ $prefinal !== null ? round($prefinal) : '-' }}</td>
                                <td class="text-center">{{ $final !== null ? round($final) : '-' }}</td>
                                <td class="text-center fw-semibold text-success">{{ $average !== null ? $average : '-' }}</td>
                                <td class="text-center">
                                    @if($isDropped)
                                        <span class="badge bg-danger-subtle text-danger fw-medium px-3 py-2 rounded-pill">Dropped</span>
                                    @elseif($remarks === 'Passed')
                                        <span class="badge bg-success-subtle text-success fw-medium px-3 py-2 rounded-pill">Passed</span>
                                    @elseif($remarks === 'Failed')
                                        <span class="badge bg-danger-subtle text-danger fw-medium px-3 py-2 rounded-pill">Failed</span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state
                icon="bi-person-x"
                title="No Students Found"
                message="No students or grades were found for the selected subject."
            />
        @endif
    @endif
</div>
@endsection
