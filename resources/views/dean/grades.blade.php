@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">
        <i class="bi bi-card-checklist text-success me-2"></i>
        View Grades
    </h1>

    {{-- Breadcrumb Navigation --}}
    @php
        $breadcrumbItems = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Select Course', 'url' => route('dean.grades')]
        ];

        if (!empty(request('course_id')) && empty(request('instructor_id'))) {
            $breadcrumbItems[] = ['label' => 'Select Instructor'];
        } elseif (!empty(request('instructor_id')) && empty(request('subject_id'))) {
            $breadcrumbItems[] = ['label' => 'Select Subject'];
        } elseif (!empty(request('subject_id'))) {
            $breadcrumbItems[] = ['label' => 'Students\' Final Grades'];
        }
    @endphp
    <x-breadcrumbs :items="$breadcrumbItems" />

    {{-- Step 1: Course Selection --}}
    @if(empty(request('course_id')))
        @if($courses->isEmpty())
            <div class="alert alert-warning text-center rounded-4 mt-5">
                No data found for available courses.
            </div>
        @else
            <div class="row g-4 px-4 py-4">
                @foreach($courses as $course)
                    <div class="col-md-4">
                        <a href="{{ route('dean.grades', ['course_id' => $course->id]) }}" class="text-decoration-none">
                            <div class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden transform transition hover:scale-105 hover:shadow-xl hover:border-primary">
                                <div class="position-relative" style="height: 80px; background-color: #4ecd85;">
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
            <div class="alert alert-warning text-center rounded-4 mt-5">
                No instructors found for this course.
            </div>
        @else
            <div class="row g-4 px-4 py-4">
                @foreach($instructors as $instructor)
                    <div class="col-md-4">
                        <a href="{{ route('dean.grades', ['course_id' => request('course_id'), 'instructor_id' => $instructor->id]) }}" class="text-decoration-none">
                            <div class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden transform transition hover:scale-105 hover:shadow-xl hover:border-primary">
                                <div class="position-relative" style="height: 80px; background-color: #4ecd85;">
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

    {{-- Step 3: Subject Selection --}}
    @elseif(empty(request('subject_id')))
        @if($subjects->isEmpty())
            <div class="alert alert-warning text-center rounded-4 mt-5">
                No subjects found for this instructor.
            </div>
        @else
            <div class="row g-4 px-4 py-4">
                @foreach($subjects as $subject)
                    <div class="col-md-4">
                        <a href="{{ route('dean.grades', ['course_id' => request('course_id'), 'instructor_id' => request('instructor_id'), 'subject_id' => $subject->id]) }}" class="text-decoration-none">
                            <div class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden transform transition hover:scale-105 hover:shadow-xl hover:border-primary">
                                <div class="position-relative" style="height: 80px; background-color: #4ecd85;">
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
            <div class="bg-white shadow-lg rounded-4 overflow-x-auto mt-6">
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
                                $termGrades = $student->termGrades->keyBy('term_id');
                                $prelim = $termGrades[1]->term_grade ?? null;
                                $midterm = $termGrades[2]->term_grade ?? null;
                                $prefinal = $termGrades[3]->term_grade ?? null;
                                $final = $termGrades[4]->term_grade ?? null;
                                $hasAll = !is_null($prelim) && !is_null($midterm) && !is_null($prefinal) && !is_null($final);
                                $average = $hasAll ? round(($prelim + $midterm + $prefinal + $final) / 4) : null;
                                $remarks = $average !== null ? ($average >= 75 ? 'Passed' : 'Failed') : null;
                            @endphp
                            <tr class="hover:bg-light">
                                <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                <td class="text-center">{{ $prelim !== null ? round($prelim) : '-' }}</td>
                                <td class="text-center">{{ $midterm !== null ? round($midterm) : '-' }}</td>
                                <td class="text-center">{{ $prefinal !== null ? round($prefinal) : '-' }}</td>
                                <td class="text-center">{{ $final !== null ? round($final) : '-' }}</td>
                                <td class="text-center fw-semibold text-success">{{ $average !== null ? $average : '-' }}</td>
                                <td class="text-center">
                                    @if($remarks === 'Passed')
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
            <div class="alert alert-warning text-center rounded-4 mt-5">
                No students or grades found for the selected subject.
            </div>
        @endif
    @endif
</div>
@endsection
