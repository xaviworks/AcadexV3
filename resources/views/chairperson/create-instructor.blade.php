@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        {{-- Page Header --}}
        @include('chairperson.partials.page-header', [
            'title' => 'Add New Instructor',
            'subtitle' => 'Create a new instructor account for your department',
            'icon' => 'bi-person-plus-fill'
        ])

        {{-- Toast Notifications --}}
        @include('chairperson.partials.toast-notifications')

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm rounded-4 mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please fix the following errors:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Card --}}
        <div class="bg-white rounded-4 shadow-sm p-4">
            <form action="{{ route('chairperson.storeInstructor') }}" method="POST">
                @csrf

                {{-- Name Fields --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="first_name" 
                               class="form-control" 
                               value="{{ old('first_name') }}" 
                               required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Middle Name</label>
                        <input type="text" 
                               name="middle_name" 
                               class="form-control" 
                               value="{{ old('middle_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="last_name" 
                               class="form-control" 
                               value="{{ old('last_name') }}" 
                               required>
                    </div>
                </div>

                {{-- Email Field --}}
                <div class="mb-4">
                    <label class="form-label fw-medium">Email Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" 
                               name="email" 
                               class="form-control" 
                               value="{{ old('email') }}" 
                               pattern="^[^@]+$" 
                               required 
                               placeholder="jdelacruz">
                        <span class="input-group-text bg-light text-muted">@brokenshire.edu.ph</span>
                    </div>
                </div>

                {{-- Department and Course --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-medium">Course <span class="text-danger">*</span></label>
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

                {{-- Password Fields --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-send me-2"></i>Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
