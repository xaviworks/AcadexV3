@extends('layouts.app')

@section('content')
@php
    $showCourseColumn = empty($selectedCourseId);
    $showProgramFilter = isset($courses) && $courses->count() > 1;
@endphp
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-mortarboard me-2"></i>Students Overview
            @if(isset($department))
                <span class="text-muted fs-5">• {{ $department->department_code ?? '' }}</span>
            @endif
        </h2>
        <p class="text-muted mb-0">View and manage students across departments and courses</p>
    </div>

    @if(isset($department))
    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Students', 'url' => route('vpaa.students')],
        ['label' => $department->department_code ?? 'Department']
    ]" />
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('vpaa.students') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="department_id" class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (isset($department) && $department->id == $dept->id) ? 'selected' : '' }}>
                                {{ $dept->department_description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($showProgramFilter)
                    <div class="col-md-4">
                        <label for="course_id" class="form-label">Program</label>
                        <select name="course_id" id="course_id" class="form-select" {{ !isset($department) ? 'disabled' : '' }} onchange="this.form.submit()">
                            <option value="">All Programs</option>
                            @if(isset($courses) && isset($department))
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ (isset($selectedCourseId) && $selectedCourseId == $course->id) ? 'selected' : '' }}>
                                        {{ $course->course_code }} - {{ $course->course_description }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="px-4 py-3 fw-semibold">Name</th>
                        @if($showCourseColumn)
                            <th scope="col" class="px-4 py-3 fw-semibold">Program</th>
                        @endif
                        <th scope="col" class="px-4 py-3 fw-semibold">Year Level</th>
                        <th scope="col" class="px-4 py-3 fw-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $droppedStudentIds = $droppedStudentIds ?? collect(); @endphp
                    @forelse($students as $student)
                        @php $isDropped = isset($droppedStudentIds[$student->id]); @endphp
                        <tr style="{{ $isDropped ? 'opacity:0.75;background-color:#fff8f8;' : '' }}">
                            <td class="px-4 py-3" style="{{ $isDropped ? 'border-left:4px solid #dc3545;' : '' }}">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold {{ $isDropped ? 'text-muted' : '' }}">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</div>
                                    </div>
                                </div>
                            </td>
                            @if($showCourseColumn)
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                        {{ $student->course->course_code ?? 'N/A' }}
                                    </span>
                                </td>
                            @endif
                            <td class="px-4 py-3">
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                    Year {{ $student->year_level }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($isDropped)
                                    <span class="badge bg-danger-subtle text-danger fw-medium px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-slash-circle"></i> Dropped
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success fw-medium px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle"></i> Enrolled
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showCourseColumn ? 4 : 3 }}" class="text-center py-5">
                                <x-empty-state
                                    compact="true"
                                    icon="bi-people-x"
                                    title="No Students Found"
                                    :message="isset($department)
                                        ? 'No students are assigned to this department.'
                                        : 'Try selecting a different department.'"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
{{-- JavaScript: resources/js/pages/vpaa/students.js --}}
@endsection
