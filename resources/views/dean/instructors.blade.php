@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-person-lines-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Instructor Account Management</span>
    </h1>
    <p class="text-muted mb-4">View all active instructors under your department.</p>

    @if($instructors->isEmpty())
        <x-empty-state
            icon="bi-people"
            title="No Instructors Found"
            message="There are no active instructors assigned to your department."
        />
    @else
        <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Instructor Name</th>
                        <th>Email Address</th>
                        <th>Course</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($instructors as $instructor)
                        <tr>
                            <td>
                                {{ trim(($instructor->last_name ?? '') . ', ' . ($instructor->first_name ?? '') . ' ' . ($instructor->middle_name ?? '')) ?: ($instructor->name ?? 'N/A') }}
                            </td>
                            <td>{{ $instructor->email }}</td>
                            <td>{{ $instructor->course->course_code ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge border border-success text-success px-3 py-2 rounded-pill">Active</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
