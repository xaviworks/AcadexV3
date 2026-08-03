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

    {{-- Toast Notifications (replaces inline alert) --}}
    @if(session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify?.success(@json(session('status')));
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify?.error(@json(session('error')));
            });
        </script>
    @endif

    @php
        // Precompute filtered lists and counts for nav badges and tab usage
        $geDepartment = \App\Models\Department::where('department_code', 'GE')->first();
        $activeInstructors = $instructors->filter(fn($i) => $i->is_active);
        $inactiveInstructors = $instructors->filter(fn($i) => !$i->is_active);
        $pendingAccountsCount = $pendingAccounts->count();
        $geRequestsCount = \App\Models\GESubjectRequest::where('status', 'pending')->count();
    @endphp

    <div id="ge-instructors-section">
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
                            <x-empty-state
                                compact="true"
                                icon="bi-people-x"
                                title="No Active Instructors"
                                message="No active instructors."
                            />
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
                            <x-empty-state
                                compact="true"
                                icon="bi-person-dash"
                                title="No Inactive Instructors"
                                message="No inactive instructors."
                            />
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
                            <x-empty-state
                                compact="true"
                                icon="bi-hourglass-split"
                                title="No Pending Applications"
                                message="No pending instructor applications."
                            />
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
            <x-empty-state
                compact="true"
                icon="bi-inbox"
                title="No Pending GE Requests"
                message="No pending GE courses requests."
            />
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
<x-modal.destructive
    id="confirmDeactivateModal"
    title="Confirm Account Deactivation"
    form=""
    form-id="deactivateForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#ge-instructors-section" data-close-modal="confirmDeactivateModal" data-loading-text="Deactivating..."'
>
    @csrf
    <p>Are you sure you want to deactivate <strong id="instructorName"></strong>'s account?</p>
    <p class="text-muted small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        This will completely deactivate the instructor's account and revoke all GE teaching access.
    </p>

    <x-slot:footer>
        <x-modal.actions destructive-text="Deactivate" />
    </x-slot:footer>
</x-modal.destructive>

<x-modal.warning
    id="confirmRemoveGEAccessModal"
    title="Remove GE Teaching Access"
    form=""
    form-id="removeGEAccessForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#ge-instructors-section" data-close-modal="confirmRemoveGEAccessModal" data-loading-text="Removing..."'
>
    @csrf
    <p>Are you sure you want to remove GE teaching access for <strong id="removeGEAccessName"></strong>?</p>
    <p class="text-muted small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        This will only revoke their ability to teach GE courses. Their account will remain active under their department.
    </p>

    <x-slot:footer>
        <x-modal.actions primary-text="Remove GE Access" primary-variant="warning" />
    </x-slot:footer>
</x-modal.warning>

<x-modal.success
    id="confirmApproveModal"
    title="Confirm Approval"
    form=""
    form-id="approveForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#ge-instructors-section" data-close-modal="confirmApproveModal" data-loading-text="Approving..."'
>
    @csrf
    Are you sure you want to approve <strong id="approveName"></strong>'s account?

    <x-slot:footer>
        <x-modal.actions primary-text="Approve" primary-variant="success" />
    </x-slot:footer>
</x-modal.success>

<x-modal.destructive
    id="confirmRejectModal"
    title="Confirm Rejection"
    form=""
    form-id="rejectForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#ge-instructors-section" data-close-modal="confirmRejectModal" data-loading-text="Rejecting..."'
>
    @csrf
    Are you sure you want to reject <strong id="rejectName"></strong>'s account?

    <x-slot:footer>
        <x-modal.actions destructive-text="Reject" />
    </x-slot:footer>
</x-modal.destructive>

<x-modal.success
    id="confirmActivateModal"
    title="Confirm Activation"
    form=""
    form-id="activateForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#ge-instructors-section" data-close-modal="confirmActivateModal" data-loading-text="Activating..."'
>
    @csrf
    Are you sure you want to activate <strong id="activateName"></strong>'s account?

    <x-slot:footer>
        <x-modal.actions primary-text="Activate" primary-variant="success" />
    </x-slot:footer>
</x-modal.success>

<x-modal.success
    id="approveGERequestModal"
    title="Approve GE Course Request"
    form=""
    form-id="approveGERequestForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#ge-instructors-section" data-close-modal="approveGERequestModal" data-loading-text="Approving..."'
>
    @csrf
    Are you sure you want to approve the GE Course request for <strong id="approveGERequestName"></strong>?
    <p class="text-muted small mt-2 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        This will allow the instructor to be assigned to GE courses.
    </p>

    <x-slot:footer>
        <x-modal.actions primary-text="Approve Request" primary-variant="success" />
    </x-slot:footer>
</x-modal.success>

<x-modal.destructive
    id="rejectGERequestModal"
    title="Reject GE Course Request"
    form=""
    form-id="rejectGERequestForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#ge-instructors-section" data-close-modal="rejectGERequestModal" data-loading-text="Rejecting..."'
>
    @csrf
    Are you sure you want to reject the GE course request for <strong id="rejectGERequestName"></strong>?
    <p class="text-muted small mt-2 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        This will deny the instructor from being assigned to GE courses.
    </p>

    <x-slot:footer>
        <x-modal.actions destructive-text="Reject Request" />
    </x-slot:footer>
</x-modal.destructive>
@endsection
