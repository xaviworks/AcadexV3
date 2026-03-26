@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
        <h1 class="text-2xl font-bold mb-6">
            <i class="bi bi-person-check text-success me-2"></i>
            Instructors in Department
        </h1>

        @if($instructors->isEmpty())
            <div class="alert alert-warning bg-warning bg-opacity-25 border border-warning text-warning rounded-4 shadow-sm">
                No instructors found under your department.
            </div>
        @else
            <div class="bg-white shadow-lg rounded-4 overflow-x-auto">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instructors as $instructor)
                            <tr>
                                <td>{{ $instructor->name }}</td>
                                <td>{{ $instructor->email }}</td>
                                <td>{{ $instructor->course->course_code ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if($instructor->is_active)
                                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fw-semibold px-3 py-2 rounded-pill">Deactivated</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
</div>
@endsection
