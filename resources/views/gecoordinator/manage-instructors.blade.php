@extends('layouts.app')

{{-- Styles: resources/css/gecoordinator/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-person-lines-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Instructor Account Management</span>
    </h1>
    <p class="text-muted mb-4">Manage instructor accounts, requests, and GE courses assignments</p>

    @if(session('status'))
        <div class="alert alert-success shadow-sm rounded">
            {{ session('status') }}
        </div>
    @endif

    @php
        // Precompute filtered lists and counts for nav badges and tab usage
        $geDepartment = \App\Models\Department::where('department_code', 'GE')->first();
        $activeInstructors = $instructors->filter(fn($i) => $i->is_active);
        $inactiveInstructors = $instructors->filter(fn($i) => !$i->is_active);
        $pendingAccountsCount = $pendingAccounts->count();
        $geRequestsCount = \App\Models\GESubjectRequest::where('status', 'pending')->count();
    @endphp

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="instructorTabs" role="tablist" style="background: transparent; border-bottom: 2px solid #dee2e6;">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="active-instructors-tab" data-bs-toggle="tab" href="#active-instructors" role="tab" aria-controls="active-instructors" aria-selected="true">
                Active Instructors
                <span class="badge bg-light text-muted ms-2">{{ $activeInstructors->count() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="inactive-instructors-tab" data-bs-toggle="tab" href="#inactive-instructors" role="tab" aria-controls="inactive-instructors" aria-selected="false">
                Inactive Instructors
                <span class="badge bg-light text-muted ms-2">{{ $inactiveInstructors->count() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="pending-approvals-tab" data-bs-toggle="tab" href="#pending-approvals" role="tab" aria-controls="pending-approvals" aria-selected="false">
                Pending Approvals
                <span class="badge bg-light text-muted ms-2">{{ $pendingAccountsCount }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="ge-requests-tab" data-bs-toggle="tab" href="#ge-requests" role="tab" aria-controls="ge-requests" aria-selected="false">
                GE Courses Requests
                <span class="badge bg-light text-muted ms-2">{{ $geRequestsCount }}</span>
            </a>
        </li>
    </ul>

    <style>
        #instructorTabs {
            background: transparent !important;
        }
        #instructorTabs .nav-link {
            background-color: transparent !important;
            color: #6c757d !important;
            transition: all 0.3s ease;
            position: relative;
        }
        #instructorTabs .nav-link:not(.active):hover {
            background-color: rgba(25, 135, 84, 0.08) !important;
            color: var(--dark-green) !important;
        }
        #instructorTabs .nav-link.active {
            background-color: rgba(25, 135, 84, 0.12) !important;
            color: var(--dark-green) !important;
            border-bottom: 3px solid var(--dark-green) !important;
            margin-bottom: -2px;
            z-index: 1;
        }
        #instructorTabsContent {
            background: transparent !important;
            padding-top: 1.5rem;
        }
        #instructorTabsContent .tab-pane {
            background: transparent !important;
        }
    </style>

    <div class="tab-content" id="instructorTabsContent" style="background: transparent;">
                    {{-- Active/Inactive lists already computed above --}}
                    {{-- Active Instructors Tab --}}
                    <div class="tab-pane fade show active" id="active-instructors" role="tabpanel" aria-labelledby="active-instructors-tab">
                        <h2 class="visually-hidden">Active Instructors</h2>

                        @if($activeInstructors->isEmpty())
                            <div class="alert alert-warning shadow-sm rounded">No active instructors.</div>
                        @else
                            <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Instructor Name</th>
                                            <th>Email Address</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeInstructors as $instructor)
                                                <tr>
                                                    <td>
                                                        {{ $instructor->last_name }}, {{ $instructor->first_name }} {{ $instructor->middle_name }}
                                                        @if($instructor->department_id !== $geDepartment?->id)
                                                            <span class="badge bg-info text-white ms-1" title="Has GE teaching access">GE Access</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $instructor->email }}</td>
                                                    <td class="text-center">
                                                        @if($instructor->department_id === $geDepartment?->id)
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmDeactivateModal"
                                                                data-instructor-id="{{ $instructor->id }}"
                                                                data-instructor-name="{{ $instructor->last_name }}, {{ $instructor->first_name }}"
                                                                data-is-ge-dept="true">
                                                                <i class="bi bi-person-x-fill"></i> Deactivate
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmRemoveGEAccessModal"
                                                                data-instructor-id="{{ $instructor->id }}"
                                                                data-instructor-name="{{ $instructor->last_name }}, {{ $instructor->first_name }}">
                                                                <i class="bi bi-shield-x"></i> Remove GE Access
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>        
                        @endif
                    </div>

                    {{-- Inactive Instructors Tab --}}
                    <div class="tab-pane fade" id="inactive-instructors" role="tabpanel" aria-labelledby="inactive-instructors-tab">
                        <h2 class="visually-hidden">Inactive Instructors</h2>

                        {{-- $inactiveInstructors already computed above --}}

                        @if($inactiveInstructors->isEmpty())
                            <div class="alert alert-warning shadow-sm rounded">No inactive instructors.</div>
                        @else
                            <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Instructor Name</th>
                                            <th>Email Address</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($inactiveInstructors as $instructor)
                                            <tr>
                                                <td>
                                                    {{ $instructor->last_name }}, {{ $instructor->first_name }} {{ $instructor->middle_name }}
                                                    @if($instructor->department_id !== $geDepartment?->id)
                                                        <span class="badge bg-info text-white ms-1" title="Has GE teaching access (not GE Dept)">GE Access Only</span>
                                                    @endif
                                                </td>
                                                <td>{{ $instructor->email }}</td>
                                                <td class="text-center">
                                                    <span class="badge border border-secondary text-secondary px-3 py-2 rounded-pill">
                                                        Inactive
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($instructor->department_id === $geDepartment?->id)
                                                        {{-- GE Department instructors can be fully activated --}}
                                                        <button type="button"
                                                            class="btn btn-success btn-sm d-inline-flex align-items-center gap-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmActivateModal"
                                                            data-id="{{ $instructor->id }}"
                                                            data-name="{{ $instructor->last_name }}, {{ $instructor->first_name }}">
                                                            <i class="bi bi-person-check-fill"></i>
                                                            Activate
                                                        </button>
                                                    @else
                                                        {{-- Non-GE Department instructors cannot be activated by GE Coordinator --}}
                                                        <span class="text-muted small" title="Contact the department chairperson to activate this instructor">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Managed by {{ $instructor->department->department_code ?? 'Dept' }} Chairperson
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>        
                        @endif
                    </div>

                    {{-- Pending Approvals Tab --}}
                    <div class="tab-pane fade" id="pending-approvals" role="tabpanel" aria-labelledby="pending-approvals-tab">
                        <h2 class="visually-hidden">Pending Approvals</h2>

                        @if($pendingAccounts->isEmpty())
                            <div class="alert alert-info shadow-sm rounded">No pending instructor applications.</div>
                        @else
                            <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Applicant Name</th>
                                            <th>Email Address</th>
                                            <th>Department</th>
                                            <th>Course</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingAccounts as $account)
                                            <tr>
                                                <td>{{ $account->last_name }}, {{ $account->first_name }} {{ $account->middle_name }}</td>
                                                <td>{{ $account->email }}</td>
                                                <td>{{ $account->department?->department_code ?? 'N/A' }}</td>
                                                <td>{{ $account->course?->course_code ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-success btn-sm d-inline-flex align-items-center gap-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmApproveModal"
                                                        data-id="{{ $account->id }}"
                                                        data-name="{{ $account->last_name }}, {{ $account->first_name }}">
                                                        <i class="bi bi-check-circle-fill"></i> Approve
                                                    </button>

                                                    <button type="button"
                                                        class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 ms-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmRejectModal"
                                                        data-id="{{ $account->id }}"
                                                        data-name="{{ $account->last_name }}, {{ $account->first_name }}">
                                                        <i class="bi bi-x-circle-fill"></i> Reject
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- GE Courses Requests Tab --}}
                    <div class="tab-pane fade" id="ge-requests" role="tabpanel" aria-labelledby="ge-requests-tab">
                        <h2 class="visually-hidden">GE Courses Requests</h2>
        @php
            $geRequests = \App\Models\GESubjectRequest::with(['instructor', 'requestedBy'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        @endphp

        @if($geRequests->isEmpty())
            <div class="alert alert-warning shadow-sm rounded">No pending GE courses requests.</div>
        @else
            <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Instructor Name</th>
                            <th>Department</th>
                            <th>Requested By</th>
                            <th>Request Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($geRequests as $request)
                            <tr>
                                <td>{{ $request->instructor->last_name }}, {{ $request->instructor->first_name }} {{ $request->instructor->middle_name }}</td>
                                <td>{{ $request->instructor->department->department_code ?? 'N/A' }}</td>
                                <td>{{ $request->requestedBy->last_name }}, {{ $request->requestedBy->first_name }}</td>
                                <td>{{ $request->created_at->format('M d, Y h:i A') }}</td>
                                <td class="text-center">
                                    <button type="button"
                                        class="btn btn-success btn-sm d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#approveGERequestModal"
                                        data-request-id="{{ $request->id }}"
                                        data-instructor-name="{{ $request->instructor->last_name }}, {{ $request->instructor->first_name }}">
                                        <i class="bi bi-check-circle-fill"></i> Approve
                                    </button>

                                    <button type="button"
                                        class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 ms-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectGERequestModal"
                                        data-request-id="{{ $request->id }}"
                                        data-instructor-name="{{ $request->instructor->last_name }}, {{ $request->instructor->first_name }}">
                                        <i class="bi bi-x-circle-fill"></i> Reject
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="modal fade" id="confirmDeactivateModal" tabindex="-1" aria-labelledby="confirmDeactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deactivateForm" method="POST">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeactivateModalLabel">Confirm Account Deactivation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to deactivate <strong id="instructorName"></strong>'s account?</p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        This will completely deactivate the instructor's account and revoke all GE teaching access.
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Deactivate</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Remove GE Access Modal (for non-GE department instructors) --}}
<div class="modal fade" id="confirmRemoveGEAccessModal" tabindex="-1" aria-labelledby="confirmRemoveGEAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="removeGEAccessForm" method="POST">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="confirmRemoveGEAccessModalLabel">Remove GE Teaching Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove GE teaching access for <strong id="removeGEAccessName"></strong>?</p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        This will only revoke their ability to teach GE courses. Their account will remain active under their department.
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Remove GE Access</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="confirmApproveModal" tabindex="-1" aria-labelledby="confirmApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="approveForm">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmApproveModalLabel">Confirm Approval</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to approve <strong id="approveName"></strong>'s account?
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="confirmRejectModal" tabindex="-1" aria-labelledby="confirmRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="rejectForm">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmRejectModalLabel">Confirm Rejection</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to reject <strong id="rejectName"></strong>'s account?
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="confirmActivateModal" tabindex="-1" aria-labelledby="confirmActivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="activateForm">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmActivateModalLabel">Confirm Activation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to activate <strong id="activateName"></strong>'s account?
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Activate</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Approve GE Subject Request Modal --}}
<div class="modal fade" id="approveGERequestModal" tabindex="-1" aria-labelledby="approveGERequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="approveGERequestForm">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveGERequestModalLabel">Approve GE Subject Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to approve the GE subject request for <strong id="approveGERequestName"></strong>?
                    <p class="text-muted small mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        This will allow the instructor to be assigned to GE subjects.
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Reject GE Subject Request Modal --}}
<div class="modal fade" id="rejectGERequestModal" tabindex="-1" aria-labelledby="rejectGERequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="rejectGERequestForm">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectGERequestModalLabel">Reject GE Subject Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to reject the GE subject request for <strong id="rejectGERequestName"></strong>?
                    <p class="text-muted small mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        This will deny the instructor from being assigned to GE subjects.
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- JavaScript is loaded via resources/js/pages/gecoordinator/manage-instructors.js --}}
@endsection
