@extends('layouts.app')

{{-- Styles: resources/css/gecoordinator/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-calendar-week text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Manage Schedule - GE Subjects</span>
    </h1>
    <p class="text-muted mb-4">View and manage GE subjects, instructor assignments, and enrollment information</p>

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => window.notify?.success(@json(session('success'))));</script>
    @endif

    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => window.notify?.error(@json(session('error'))));</script>
    @endif

    @if($subjects->count() > 0)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Subject Title</th>
                            <th class="text-center">Units</th>
                            <th class="text-center">Year Level</th>
                            <th>Assigned Instructors</th>
                            <th class="text-center">Enrolled Students</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                            <tr>
                                <td class="fw-semibold">{{ $subject->subject_code }}</td>
                                <td>{{ $subject->subject_title }}</td>
                                <td class="text-center">{{ $subject->units }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                        Year {{ $subject->year_level }}
                                    </span>
                                </td>
                                <td>
                                    @if($subject->instructors->count() > 0)
                                        @foreach($subject->instructors as $instructor)
                                            <span class="badge bg-primary-subtle text-primary me-1 mb-1">
                                                {{ $instructor->first_name }} {{ $instructor->last_name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted fst-italic">No instructor assigned</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $subject->students->count() }} students</span>
                                </td>
                                <td class="text-center">
                                    <button onclick="openScheduleModal({{ $subject->id }}, '{{ $subject->subject_code }}', '{{ addslashes($subject->subject_title) }}')" 
                                            class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-calendar-week me-1"></i>Schedule
                                    </button>
                                    <button onclick="viewSubjectDetails({{ $subject->id }})" 
                                            class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-eye me-1"></i>Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted mb-3 d-block" style="font-size: 4rem;"></i>
            <h5 class="text-muted mb-2">No GE Subjects Found</h5>
            <p class="text-muted">No GE subjects found for the current academic period. Please check if subjects have been created for this academic period.</p>
        </div>
    @endif
</div>
@endsection
