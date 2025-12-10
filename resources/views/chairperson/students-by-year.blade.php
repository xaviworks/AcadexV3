@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        <!-- Page Title -->
        <div class="page-title">
            <h1>
                <i class="bi bi-people-fill"></i>
                Students List
            </h1>
            <p class="page-subtitle">View all students under your department and filter by year level</p>
        </div>

    @if($students->isEmpty())
        <div class="bg-warning bg-opacity-25 text-warning border border-warning px-4 py-3 rounded-4 shadow-sm">
            No students found under your department and course.
        </div>
    @else
        {{-- Year Level Tabs --}}
        <ul class="nav nav-tabs" id="yearTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="all-years-tab" data-bs-toggle="tab" href="#all-years" role="tab" aria-controls="all-years" aria-selected="true">All Years</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="first-year-tab" data-bs-toggle="tab" href="#first-year" role="tab" aria-controls="first-year" aria-selected="false">1st Year</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="second-year-tab" data-bs-toggle="tab" href="#second-year" role="tab" aria-controls="second-year" aria-selected="false">2nd Year</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="third-year-tab" data-bs-toggle="tab" href="#third-year" role="tab" aria-controls="third-year" aria-selected="false">3rd Year</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="fourth-year-tab" data-bs-toggle="tab" href="#fourth-year" role="tab" aria-controls="fourth-year" aria-selected="false">4th Year</a>
            </li>
        </ul>

        <div class="tab-content" id="yearTabsContent">
            <div class="tab-pane fade show active" id="all-years" role="tabpanel" aria-labelledby="all-years-tab">
                <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th class="text-center">Year Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr class="hover:bg-light">
                                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                    <td>{{ $student->course->course_code ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">
                                            {{ $student->formatted_year_level }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="first-year" role="tabpanel" aria-labelledby="first-year-tab">
                <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th class="text-center">Year Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students->where('year_level', 1) as $student)
                                <tr class="hover:bg-light">
                                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                    <td>{{ $student->course->course_code ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">
                                            {{ $student->formatted_year_level }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="second-year" role="tabpanel" aria-labelledby="second-year-tab">
                <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th class="text-center">Year Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students->where('year_level', 2) as $student)
                                <tr class="hover:bg-light">
                                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                    <td>{{ $student->course->course_code ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">
                                            {{ $student->formatted_year_level }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="third-year" role="tabpanel" aria-labelledby="third-year-tab">
                <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th class="text-center">Year Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students->where('year_level', 3) as $student)
                                <tr class="hover:bg-light">
                                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                    <td>{{ $student->course->course_code ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">
                                            {{ $student->formatted_year_level }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="fourth-year" role="tabpanel" aria-labelledby="fourth-year-tab">
                <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th class="text-center">Year Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students->where('year_level', 4) as $student)
                                <tr class="hover:bg-light">
                                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                    <td>{{ $student->course->course_code ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">
                                            {{ $student->formatted_year_level }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>


    </div>
</div>
@endsection
