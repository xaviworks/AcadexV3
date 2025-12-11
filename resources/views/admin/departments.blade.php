@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0">🏢 Departments</h1>
            <p class="text-muted mb-0">Manage academic departments</p>
        </div>
        <button class="btn btn-success" onclick="showDepartmentModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Department
        </button>
    </div>

    {{-- Departments Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="departmentsTable" class="table table-hover align-middle w-100-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th class="text-center">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr>
                                <td>{{ $department->id }}</td>
                                <td class="fw-semibold">{{ $department->department_code }}</td>
                                <td>{{ $department->department_description }}</td>
                                <td class="text-center">{{ $department->created_at->format('Y-m-d') }}</td>
                                {{-- Actions removed as requested --}}
                            </tr>
                        @empty
                            {{-- DataTables will handle empty state --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Department Modal --}}
<div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="departmentModalLabel">Add New Department</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.storeDepartment') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Code</label>
                        <input type="text" name="department_code" class="form-control" placeholder="e.g. CITE" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Description</label>
                        <input type="text" name="department_description" class="form-control" placeholder="e.g. College of Information Technology Education" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript is loaded via resources/js/pages/admin/departments.js --}}
@endsection
