@extends('layouts.app')

@section('content')
@php
    $showDepartmentColumn = !request('department_id');
    $activeInstructors = $instructors->filter(fn($instructor) => $instructor->is_active);
    $inactiveInstructors = $instructors->filter(fn($instructor) => !$instructor->is_active);
    $selectedDepartmentCode = $selectedDepartment->department_code ?? null;

    $roleLabels = [
        0 => 'Instructor',
        1 => 'Chairperson',
        2 => 'Dean',
        3 => 'Admin',
        4 => 'GE Coordinator',
        5 => 'VPAA',
    ];

    $roleBadgeClasses = [
        0 => 'bg-primary',
        1 => 'bg-warning text-dark',
        2 => 'bg-info text-dark',
        3 => 'bg-danger',
        4 => 'bg-success',
        5 => 'bg-dark',
    ];
@endphp

<div class="container-fluid px-4 py-4">
    <h1 class="text-2xl font-bold mb-2 d-flex align-items-center">
        <i class="bi bi-people-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Instructor Management</span>
        @if($selectedDepartmentCode)
            <span class="badge bg-light text-success border ms-3 px-3 py-2 rounded-pill">{{ $selectedDepartmentCode }}</span>
        @endif
    </h1>
    <p class="text-muted mb-4">View instructor accounts across departments with a cleaner active and inactive split.</p>

    @if(request('department_id'))
        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Departments', 'url' => route('vpaa.departments')],
            ['label' => $selectedDepartmentCode ?? 'Department']
        ]" />
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('vpaa.instructors') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-lg-9">
                    <label for="department_id" class="form-label fw-semibold">Department</label>
                    <select name="department_id" id="department_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (request('department_id') == $dept->id || ($selectedDepartment && $selectedDepartment->id == $dept->id)) ? 'selected' : '' }}>
                                {{ $dept->department_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    @if($selectedDepartmentCode)
                        <a href="{{ route('vpaa.instructors') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Clear Filter
                        </a>
                    @else
                        <div class="small text-muted text-lg-end">
                            Filter by department acronym.
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs mb-0" id="vpaaInstructorTabs" role="tablist" style="background: transparent; border-bottom: 2px solid #dee2e6;">
        <li class="nav-item" role="presentation">
            <a class="nav-link active"
               id="active-instructors-tab"
               data-bs-toggle="tab"
               href="#active-instructors"
               role="tab"
               aria-controls="active-instructors"
               aria-selected="true">
                Active Instructors
                <span class="badge bg-light text-muted ms-2">{{ $activeInstructors->count() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link"
               id="inactive-instructors-tab"
               data-bs-toggle="tab"
               href="#inactive-instructors"
               role="tab"
               aria-controls="inactive-instructors"
               aria-selected="false">
                Inactive Instructors
                <span class="badge bg-light text-muted ms-2">{{ $inactiveInstructors->count() }}</span>
            </a>
        </li>
    </ul>

    <style>
        #vpaaInstructorTabs {
            background: transparent !important;
        }

        #vpaaInstructorTabs .nav-link {
            background-color: transparent !important;
            color: #6c757d !important;
            transition: all 0.3s ease;
            position: relative;
        }

        #vpaaInstructorTabs .nav-link:not(.active):hover {
            background-color: rgba(25, 135, 84, 0.08) !important;
            color: var(--dark-green) !important;
        }

        #vpaaInstructorTabs .nav-link.active {
            background-color: rgba(25, 135, 84, 0.12) !important;
            color: var(--dark-green) !important;
            border-bottom: 3px solid var(--dark-green) !important;
            margin-bottom: -2px;
            z-index: 1;
        }

        #vpaaInstructorTabsContent {
            background: transparent !important;
            padding-top: 1.5rem;
        }

        #vpaaInstructorTabsContent .tab-pane {
            background: transparent !important;
        }
    </style>

    <div class="tab-content" id="vpaaInstructorTabsContent" style="background: transparent;">
        <div class="tab-pane fade show active" id="active-instructors" role="tabpanel" aria-labelledby="active-instructors-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-4 py-3 fw-semibold">Name</th>
                                <th scope="col" class="px-4 py-3 fw-semibold">Role</th>
                                @if($showDepartmentColumn)
                                    <th scope="col" class="px-4 py-3 fw-semibold">Department</th>
                                @endif
                                <th scope="col" class="px-4 py-3 fw-semibold">Email</th>
                                <th scope="col" class="px-4 py-3 fw-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeInstructors as $instructor)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-fill text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $instructor->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $roleBadgeClasses[$instructor->role] ?? 'bg-secondary' }} px-3 py-1">
                                            {{ $roleLabels[$instructor->role] ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    @if($showDepartmentColumn)
                                        <td class="px-4 py-3">
                                            <span class="text-muted">{{ $instructor->department->department_code ?? 'N/A' }}</span>
                                        </td>
                                    @endif
                                    <td class="px-4 py-3">
                                        <span class="text-muted">{{ $instructor->email }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-success px-3 py-2">Active</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $showDepartmentColumn ? 5 : 4 }}" class="text-center py-5">
                                        <div class="text-muted mb-3">
                                            <i class="bi bi-people-x fs-1 opacity-50"></i>
                                        </div>
                                        <h6 class="text-muted mb-1">No active instructors found</h6>
                                        <p class="text-muted small mb-0">
                                            @if($selectedDepartmentCode)
                                                No active instructors are assigned to {{ $selectedDepartmentCode }}.
                                            @else
                                                No active instructors are currently registered.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="inactive-instructors" role="tabpanel" aria-labelledby="inactive-instructors-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-4 py-3 fw-semibold">Name</th>
                                <th scope="col" class="px-4 py-3 fw-semibold">Role</th>
                                @if($showDepartmentColumn)
                                    <th scope="col" class="px-4 py-3 fw-semibold">Department</th>
                                @endif
                                <th scope="col" class="px-4 py-3 fw-semibold">Email</th>
                                <th scope="col" class="px-4 py-3 fw-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inactiveInstructors as $instructor)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-fill text-secondary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $instructor->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $roleBadgeClasses[$instructor->role] ?? 'bg-secondary' }} px-3 py-1">
                                            {{ $roleLabels[$instructor->role] ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    @if($showDepartmentColumn)
                                        <td class="px-4 py-3">
                                            <span class="text-muted">{{ $instructor->department->department_code ?? 'N/A' }}</span>
                                        </td>
                                    @endif
                                    <td class="px-4 py-3">
                                        <span class="text-muted">{{ $instructor->email }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-danger px-3 py-2">Inactive</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $showDepartmentColumn ? 5 : 4 }}" class="text-center py-5">
                                        <div class="text-muted mb-3">
                                            <i class="bi bi-person-dash fs-1 opacity-50"></i>
                                        </div>
                                        <h6 class="text-muted mb-1">No inactive instructors found</h6>
                                        <p class="text-muted small mb-0">
                                            @if($selectedDepartmentCode)
                                                No inactive instructors are assigned to {{ $selectedDepartmentCode }}.
                                            @else
                                                No inactive instructors are currently registered.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
