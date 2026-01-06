@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-person-gear me-2"></i>Edit Instructor
            </h2>
            <p class="text-muted mb-0">Update {{ $instructor->first_name }} {{ $instructor->last_name }}'s information</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('vpaa.instructors.update', $instructor->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <!-- Name Fields -->
                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-semibold">First Name</label>
                        <input type="text" name="first_name" id="first_name" 
                               value="{{ old('first_name', $instructor->first_name) }}" 
                               class="form-control @error('first_name') is-invalid @enderror">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-semibold">Last Name</label>
                        <input type="text" name="last_name" id="last_name" 
                               value="{{ old('last_name', $instructor->last_name) }}" 
                               class="form-control @error('last_name') is-invalid @enderror">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div class="col-12">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="email" 
                               value="{{ old('email', $instructor->email) }}" 
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Department -->
                    <div class="col-12">
                        <label for="department_id" class="form-label fw-semibold">Department</label>
                        <select name="department_id" id="department_id" 
                                class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">Select a department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $instructor->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_code }} - {{ $department->department_description }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Status -->
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" 
                                   {{ old('is_active', $instructor->is_active) ? 'checked' : '' }}
                                   class="form-check-input">
                            <label for="is_active" class="form-check-label">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('vpaa.instructors') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Update Instructor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
