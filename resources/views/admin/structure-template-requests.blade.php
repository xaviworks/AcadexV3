@extends('layouts.app')

@section('content')
@php
    // Prepare initial data for Alpine.js hydration (same shape as poll endpoint)
    $requestsData = $requests->map(function ($r) {
        return [
            'id' => $r->id,
            'label' => $r->label,
            'description' => $r->description,
            'structure_config' => $r->structure_config,
            'status' => $r->status,
            'admin_notes' => $r->admin_notes,
            'created_at' => $r->created_at->toIso8601String(),
            'created_at_formatted' => $r->created_at->format('M d, Y'),
            'created_at_time' => $r->created_at->format('h:i A'),
            'chairperson' => [
                'first_name' => $r->chairperson->first_name,
                'last_name' => $r->chairperson->last_name,
                'email' => $r->chairperson->email,
            ],
            'reviewer' => $r->reviewer ? [
                'first_name' => $r->reviewer->first_name,
                'last_name' => $r->reviewer->last_name,
            ] : null,
            'reviewed_at' => $r->reviewed_at?->format('M d, Y'),
            'approve_url' => route('admin.structureTemplateRequests.approve', $r),
            'reject_url' => route('admin.structureTemplateRequests.reject', $r),
            'show_url' => route('admin.structureTemplateRequests.show', $r),
        ];
    })->values();
@endphp

<script>
    window.templateRequestsConfig = {
        requests: @json($requestsData),
        pendingCount: @json($pendingCount),
        pollUrl: @json(route('admin.structureTemplateRequests.poll')),
        status: @json($status),
    };
</script>

<div class="container-fluid py-4" x-data="templateRequestsAdmin()" x-init="init()">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-clipboard-check-fill text-success me-2"></i>Structure Formula Requests</h1>
            <p class="text-muted mb-0">Review and approve chairperson formula submissions</p>
        </div>
        <a href="{{ route('admin.gradesFormula', ['view' => 'formulas']) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Grades Formula
        </a>
    </div>

    @if (session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => window.notify?.success(@json(session('success'))));</script>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.structureTemplateRequests.index', ['status' => 'all']) }}" 
                   class="btn btn-sm {{ $status === 'all' ? 'btn-success' : 'btn-outline-success' }}">
                    All Requests
                </a>
                <a href="{{ route('admin.structureTemplateRequests.index', ['status' => 'pending']) }}" 
                   class="btn btn-sm {{ $status === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                    Pending <span x-show="pendingCount > 0" class="badge bg-dark ms-1" x-text="pendingCount"></span>
                </a>
                <a href="{{ route('admin.structureTemplateRequests.index', ['status' => 'approved']) }}" 
                   class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-success' }}">
                    Approved
                </a>
                <a href="{{ route('admin.structureTemplateRequests.index', ['status' => 'rejected']) }}" 
                   class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Rejected
                </a>
            </div>
        </div>
    </div>

    {{-- Empty State --}}
    <template x-if="requests.length === 0">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-inbox icon-xxl icon-muted-gray"></i>
                </div>
                <h5 class="text-muted mb-2">No Requests Found</h5>
                <p class="text-muted mb-0">
                    @if ($status === 'pending')
                        There are no pending formula requests at the moment.
                    @elseif ($status === 'approved')
                        No approved formula requests found.
                    @elseif ($status === 'rejected')
                        No rejected formula requests found.
                    @else
                        No formula requests have been submitted yet.
                    @endif
                </p>
            </div>
        </div>
    </template>

    {{-- Table (rendered from Alpine data) --}}
    <template x-if="requests.length > 0">
        <div class="table-responsive">
            <table class="table table-hover bg-white shadow-sm">
                <thead class="table-success">
                    <tr>
                        <th>Template Name</th>
                        <th>Submitted By</th>
                        <th>Structure Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="req in requests" :key="req.id">
                        <tr>
                            <td>
                                <div class="fw-bold" x-text="req.label"></div>
                                <template x-if="req.description">
                                    <small class="text-muted" x-text="truncate(req.description, 60)"></small>
                                </template>
                            </td>
                            <td>
                                <div x-text="req.chairperson.first_name + ' ' + req.chairperson.last_name"></div>
                                <small class="text-muted" x-text="req.chairperson.email"></small>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark" x-text="getStructureLabel(req.structure_config)"></span>
                            </td>
                            <td>
                                <span class="badge" :class="getStatusBadgeClass(req.status)">
                                    <i class="me-1" :class="getStatusIcon(req.status)"></i><span x-text="req.status.charAt(0).toUpperCase() + req.status.slice(1)"></span>
                                </span>
                            </td>
                            <td>
                                <div x-text="req.created_at_formatted"></div>
                                <small class="text-muted" x-text="req.created_at_time"></small>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-info"
                                            @click="viewRequest($event.currentTarget)"
                                            :data-request-id="req.id"
                                            :data-label="req.label"
                                            :data-description="req.description || ''"
                                            :data-structure="JSON.stringify(req.structure_config)"
                                            :data-chairperson="req.chairperson.first_name + ' ' + req.chairperson.last_name"
                                            :data-status="req.status"
                                            :data-admin-notes="req.admin_notes || ''"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewRequestModal"
                                            title="View Details">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    <template x-if="req.status === 'pending'">
                                        <button type="button" class="btn btn-sm btn-success"
                                                @click="approveRequest($event.currentTarget)"
                                                :data-template-name="req.label"
                                                :data-approve-url="req.approve_url"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approveModal"
                                                title="Approve Request">
                                            <i class="bi bi-check-circle me-1"></i>Approve
                                        </button>
                                    </template>
                                    <template x-if="req.status === 'pending'">
                                        <button type="button" class="btn btn-sm btn-danger"
                                                @click="rejectRequest($event.currentTarget)"
                                                :data-template-name="req.label"
                                                :data-reject-url="req.reject_url"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal"
                                                title="Reject Request">
                                            <i class="bi bi-x-circle me-1"></i>Reject
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-eye-fill icon-xl"></i>
                    <h5 class="modal-title mb-0" id="viewRequestModalLabel">Template Request Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="viewRequestBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" id="approveForm">
                @csrf
                <div class="modal-header bg-success text-white border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill icon-xl"></i>
                        <h5 class="modal-title mb-0" id="approveModalLabel">Approve Template Request</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success border-0 shadow-sm mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        This will create a new structure template that can be used by instructors.
                    </div>
                    
                    <p class="mb-3">Are you sure you want to approve <strong id="approveTemplateName" class="text-success"></strong>?</p>
                    
                    <div class="mb-0">
                        <label for="approveAdminNotes" class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea class="form-control" id="approveAdminNotes" name="admin_notes" rows="3" placeholder="Add any notes or comments for the chairperson..."></textarea>
                        <small class="text-muted">These notes will be visible to the chairperson.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i>Approve Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" id="rejectForm">
                @csrf
                <div class="modal-header bg-danger text-white border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-x-circle-fill icon-xl"></i>
                        <h5 class="modal-title mb-0" id="rejectModalLabel">Reject Template Request</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger border-0 shadow-sm mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        The chairperson will be notified that their template request was rejected.
                    </div>
                    
                    <p class="mb-3">Are you sure you want to reject <strong id="rejectTemplateName" class="text-danger"></strong>?</p>
                    
                    <div class="mb-0">
                        <label for="rejectAdminNotes" class="form-label fw-semibold">
                            Reason for Rejection <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="rejectAdminNotes" name="admin_notes" rows="4" required placeholder="Explain why this template request is being rejected..."></textarea>
                        <small class="text-muted">This message will be visible to the chairperson.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-x-circle me-1"></i>Reject Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript moved to: resources/js/pages/admin/structure-template-requests.js --}}
