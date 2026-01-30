@extends('layouts.app')

{{-- Styles: resources/css/gecoordinator/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-person-plus-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Add New Instructor</span>
    </h1>
    <p class="text-muted mb-4">Create a new instructor account for GE subjects</p>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('gecoordinator.storeInstructor') }}" method="POST">
                        @csrf

                        {{-- Name --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                            </div>
                        </div>

                        {{-- Email (username only) --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email Username</label>
                            <div class="input-group">
                                <input type="text" name="email" class="form-control" value="{{ old('email') }}" pattern="^[^@]+$" required placeholder="jdelacruz">
                                <span class="input-group-text bg-light text-muted">@brokenshire.edu.ph</span>
                            </div>
                        </div>

                        {{-- Department and Course --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Department</label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->department_description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Course</label>
                                <select name="course_id" class="form-select" required>
                                    <option value="">-- Select Course --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check-circle me-2"></i>Submit for Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
