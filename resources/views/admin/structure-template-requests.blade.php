@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-3 bg-gradient-light min-vh-100">
    <div class="row mb-3">
        <div class="col">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle bg-gradient-green">
                        <i class="bi bi-clipboard-check text-white icon-xl"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-primary-green">Structure Formula Requests</h3>
                        <p class="text-muted mb-0">Review and approve chairperson formula submissions</p>
                    </div>
                </div>
                <a href="{{ route('admin.gradesFormula', ['view' => 'formulas']) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Grades Formula
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <script>notify.success('{{ session('success') }}');</script>
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
                    Pending @if($pendingCount > 0)<span class="badge bg-dark ms-1">{{ $pendingCount }}</span>@endif
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

    @if ($requests->isEmpty())
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
    @else
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
                    @foreach ($requests as $request)
                        @php
                            $statusBadge = match ($request->status) {
                                'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'clock-history'],
                                'approved' => ['class' => 'bg-success', 'icon' => 'check-circle'],
                                'rejected' => ['class' => 'bg-danger', 'icon' => 'x-circle'],
                                default => ['class' => 'bg-secondary', 'icon' => 'question-circle'],
                            };
                            
                            $structureType = $request->structure_config['type'] ?? 'unknown';
                            $structureLabel = match ($structureType) {
                                'lecture_only' => 'Lecture Only',
                                'lecture_lab' => 'Lecture + Lab',
                                'custom' => 'Custom',
                                default => 'Unknown',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $request->label }}</div>
                                @if ($request->description)
                                    <small class="text-muted">{{ Str::limit($request->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                <div>{{ $request->chairperson->first_name }} {{ $request->chairperson->last_name }}</div>
                                <small class="text-muted">{{ $request->chairperson->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $structureLabel }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadge['class'] }}">
                                    <i class="bi bi-{{ $statusBadge['icon'] }} me-1"></i>{{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $request->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-info" 
                                            onclick="viewRequest(this)"
                                            data-request-id="{{ $request->id }}"
                                            data-label="{{ $request->label }}"
                                            data-description="{{ $request->description }}"
                                            data-structure='@json($request->structure_config)'
                                            data-chairperson="{{ $request->chairperson->first_name }} {{ $request->chairperson->last_name }}"
                                            data-status="{{ $request->status }}"
                                            data-admin-notes="{{ $request->admin_notes }}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewRequestModal"
                                            title="View Details">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    @if ($request->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="approveRequest(this)"
                                                data-template-name="{{ $request->label }}"
                                                data-approve-url="{{ route('admin.structureTemplateRequests.approve', $request) }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approveModal"
                                                title="Approve Request">
                                            <i class="bi bi-check-circle me-1"></i>Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="rejectRequest(this)"
                                                data-template-name="{{ $request->label }}"
                                                data-reject-url="{{ route('admin.structureTemplateRequests.reject', $request) }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal"
                                                title="Reject Request">
                                            <i class="bi bi-x-circle me-1"></i>Reject
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
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
