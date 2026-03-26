@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
        <h1 class="text-2xl font-bold mb-6">
            <i class="bi bi-person-lines-fill text-success me-2"></i>
            Students in Department
        </h1>

        @if($students->isEmpty())
            <div class="alert alert-warning bg-warning bg-opacity-25 border border-warning text-warning rounded-4 shadow-sm">
                No students found under your department.
            </div>
        @else
            <div class="mb-4">
                <form action="{{ route('dean.students') }}" method="GET" class="d-flex align-items-center gap-3">
                    <label for="courseFilter" class="form-label mb-0">Filter by Course:</label>
                    <select name="course_id" id="courseFilter" class="form-select" style="width: auto;" onchange="this.form.submit()">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $selectedCourseId == $course->id ? 'selected' : '' }}>
                                {{ $course->course_code }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="bg-white shadow-lg rounded-4 overflow-x-auto">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Year Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                <td>{{ $student->course->course_code ?? 'N/A' }}</td>
                                <td>{{ $student->year_level ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
</div>
@endsection
