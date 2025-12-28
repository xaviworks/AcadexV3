@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="mb-1">
                <a href="{{ route('admin.disaster-recovery.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
            <h1 class="h4 text-dark fw-bold mb-0">Activity Log</h1>
            <p class="text-muted small mb-0">Track all changes made in the system</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.disaster-recovery.activity') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold small">Event Type</label>
                    <select name="event" class="form-select">
                        <option value="">All Events</option>
                        <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                        <option value="restored" {{ request('event') === 'restored' ? 'selected' : '' }}>Restored</option>
                        <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning text-white flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.disaster-recovery.activity') }}" class="btn btn-outline-secondary">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Activity List --}}
    <div class="card border-0 shadow-sm">
        <div class="list-group list-group-flush">
            @forelse($logs as $log)
                @php
                    $colors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'restored' => 'info'];
                    $icons = ['created' => 'plus', 'updated' => 'edit', 'deleted' => 'trash', 'restored' => 'undo'];
                    $color = $colors[$log->event] ?? 'secondary';
                    $icon = $icons[$log->event] ?? 'circle';
                @endphp
                <div class="list-group-item p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-{{ $icon }} fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 fw-bold">
                                        {{ class_basename($log->auditable_type) }} 
                                        <span class="badge bg-{{ $color }} ms-2">{{ ucfirst($log->event) }}</span>
                                    </h6>
                                    <p class="mb-0 text-muted small">
                                        by <strong>{{ $log->user?->name ?? 'System' }}</strong>
                                        @if($log->ip_address)
                                            <span class="mx-1">•</span>
                                            {{ $log->ip_address }}
                                        @endif
                                    </p>
                                    @if($log->auditable_type === 'App\Models\Backup' && !empty($log->auditable->notes))
                                        <div class="small text-muted fst-italic mt-1">
                                            <i class="fas fa-sticky-note me-1 text-secondary"></i> "{{ $log->auditable->notes }}"
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted mb-1">{{ $log->created_at->format('M d, Y h:i A') }}</div>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.disaster-recovery.activity.show', $log) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($log->event !== 'created' && $log->old_values)
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="showRollbackModal({{ $log->id }})" title="Rollback">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-history fs-1 mb-3 opacity-25"></i>
                    <h5>No Activity Found</h5>
                    <p class="mb-0">Activity will appear here when changes are made.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Rollback Modal --}}
<div class="modal fade" id="rollbackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="rollbackForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-warning"><i class="fas fa-undo me-2"></i>Rollback Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to revert this change to the previous state?</p>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Rollback</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showRollbackModal(id) {
    document.getElementById('rollbackForm').action = `/admin/disaster-recovery/activity/${id}/rollback`;
    new bootstrap.Modal(document.getElementById('rollbackModal')).show();
}
</script>
@endpush
@endsection
