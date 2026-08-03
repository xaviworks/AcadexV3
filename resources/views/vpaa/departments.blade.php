@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4" id="vpaa-departments-section">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-building me-2"></i>Departments Overview
            </h2>
            <p class="text-muted mb-0">View and manage all departments in the institution</p>
        </div>
    </div>

    <!-- Status Alert -->
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>
                {{ session('status') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Departments Grid -->
        {{-- Departments Grid --}}
    <div class="row g-4">
        @foreach($departments as $department)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" 
                     style="cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease;"
                     onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'"
                     onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'"
                     onclick="window.location.href='{{ route('vpaa.instructors', ['department_id' => $department->id]) }}'">
                    
                    {{-- Green Header Section --}}
                    <div class="position-relative" style="height: 70px; background: linear-gradient(135deg, #4ecd85, #3ba76a);">
                        <div class="position-absolute start-50 translate-middle"
                             style="top: 100%; transform: translate(-50%, -50%); width: 70px; height: 70px; 
                                    background: linear-gradient(135deg, #4da674, #023336); 
                                    border-radius: 15%; display: flex; align-items: center; justify-content: center; 
                                    box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s ease;">
                            <i class="bi bi-building-fill text-white" style="font-size: 28px;"></i>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body pt-4 text-center px-3 pb-4">
                        <h6 class="fw-bold mt-3 mb-3 text-dark" title="{{ $department->department_description }}" style="font-size: 1rem; line-height: 1.4; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; min-height: 2.8em;">
                            {{ $department->department_description }}
                        </h6>
                        
                        {{-- Department Badge --}}
                        <div class="mb-3">
                            <span class="badge bg-primary text-white px-3 py-1" style="font-size: 0.8rem;">Department</span>
                        </div>
                        
                        {{-- Mini Stats --}}
                        <div class="row g-2 px-2">
                            <div class="col-6">
                                <div class="bg-success-subtle rounded-3 p-2 text-center border border-success border-opacity-25">
                                    <div class="fw-bold text-success mb-1" style="font-size: 1.4rem;">{{ $department->instructor_count ?? 0 }}</div>
                                    <div class="small text-success fw-semibold" style="font-size: 0.8rem;">Instructors</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-info-subtle rounded-3 p-2 text-center border border-info border-opacity-25">
                                    <div class="fw-bold text-info mb-1" style="font-size: 1.4rem;">{{ $department->student_count ?? 0 }}</div>
                                    <div class="small text-info fw-semibold" style="font-size: 0.8rem;">Students</div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Click Indicator --}}
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right-circle me-1"></i>View Instructors
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <x-modal.form
        id="addDepartmentModal"
        title="Add New Department"
        size="medium"
        form="{{ route('vpaa.departments.store') }}"
        form-class="ajax-action-form"
        form-attributes='data-refresh-target="#vpaa-departments-section" data-close-modal="addDepartmentModal" data-loading-text="Saving..." data-reset-on-success="true"'
    >
        @csrf
        <div class="mb-3">
            <label for="department_code" class="form-label">Department Code</label>
            <input type="text" class="form-control" id="department_code" name="department_code" required>
        </div>
        <div class="mb-3">
            <label for="department_description" class="form-label">Description</label>
            <input type="text" class="form-control" id="department_description" name="department_description" required>
        </div>

        <x-slot:footer>
            <x-modal.actions primary-text="Save Department" />
        </x-slot:footer>
    </x-modal.form>

    <!-- Edit Department Modals -->
    @foreach($departments as $department)
        <x-modal.form
            id="editDepartmentModal{{ $department->id }}"
            title="Edit Department"
            size="medium"
            form="{{ route('vpaa.departments.update', $department->id) }}"
            variant="default"
            form-class="ajax-action-form"
            form-attributes='data-refresh-target="#vpaa-departments-section" data-close-modal="editDepartmentModal{{ $department->id }}" data-loading-text="Saving..."'
        >
            @csrf
            @method('PUT')
            <x-slot:icon><i class="bi bi-pencil-square me-1"></i></x-slot:icon>
            <div class="mb-3">
                <label for="edit_department_code_{{ $department->id }}" class="form-label">Department Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_department_code_{{ $department->id }}" name="department_code" value="{{ $department->department_code }}" required>
            </div>
            <div class="mb-3">
                <label for="edit_department_description_{{ $department->id }}" class="form-label">Department Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_department_description_{{ $department->id }}" name="department_description" value="{{ $department->department_description }}" required>
            </div>

            <x-slot:footer>
                <x-modal.actions>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </x-modal.actions>
            </x-slot:footer>
        </x-modal.form>
    @endforeach
</div>

{{-- Styles: resources/css/vpaa/common.css --}}
{{-- JavaScript: resources/js/pages/vpaa/departments.js --}}
@endsection
