@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="mb-1">
                <a href="{{ route('admin.disaster-recovery.activity') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Activity Log
                </a>
            </div>
            <h1 class="h4 text-dark fw-bold mb-0">
                <i class="fas fa-file-alt text-info me-2"></i>Activity Details
            </h1>
        </div>
        @if($log->event !== 'created' && $log->old_values)
            <button type="button" class="btn btn-warning text-white" onclick="showRollbackModal()">
                <i class="fas fa-undo me-1"></i> Rollback
            </button>
        @endif
    </div>

    @php
        $colors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'restored' => 'info'];
        $icons = ['created' => 'plus-circle', 'updated' => 'edit', 'deleted' => 'trash-alt', 'restored' => 'undo'];
        $color = $colors[$log->event] ?? 'secondary';
        $icon = $icons[$log->event] ?? 'circle';
    @endphp

    {{-- Event Badge --}}
    <div class="mb-4">
        <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
            <i class="fas fa-{{ $icon }} me-2"></i>{{ ucfirst($log->event) }}
        </span>
    </div>

    {{-- Info Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle text-muted me-2"></i>Event Information</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4 col-lg-2">
                    <div class="bg-light rounded p-3 h-100">
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Model</label>
                        <span class="fw-bold text-dark">{{ class_basename($log->auditable_type) }}</span>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="bg-light rounded p-3 h-100">
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Record ID</label>
                        <span class="fw-bold text-dark">{{ $log->auditable_id }}</span>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="bg-light rounded p-3 h-100">
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">User</label>
                        <span class="fw-bold text-dark">{{ $log->user?->name ?? 'System' }}</span>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="bg-light rounded p-3 h-100">
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Email</label>
                        <span class="fw-bold text-dark">{{ $log->user?->email ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="bg-light rounded p-3 h-100">
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">IP Address</label>
                        <span class="fw-bold text-dark">{{ $log->ip_address ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="bg-light rounded p-3 h-100">
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Date & Time</label>
                        <span class="fw-bold text-dark">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Changes --}}
    @if($log->event === 'updated' && $log->old_values && $log->new_values)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-exchange-alt text-warning me-2"></i>Changes Made</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%">Field</th>
                            <th style="width: 40%">Old Value</th>
                            <th style="width: 40%">New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $oldValues = is_array($log->old_values) ? $log->old_values : json_decode($log->old_values, true) ?? [];
                            $newValues = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true) ?? [];
                        @endphp
                        @foreach($oldValues as $field => $oldValue)
                            <tr>
                                <td class="fw-bold bg-light">{{ Str::headline($field) }}</td>
                                <td class="bg-danger bg-opacity-10 text-danger">
                                    {{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}
                                </td>
                                <td class="bg-success bg-opacity-10 text-success">
                                    {{ is_array($newValues[$field] ?? null) ? json_encode($newValues[$field] ?? null) : ($newValues[$field] ?? '-') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Created Data --}}
    @if($log->event === 'created' && $log->new_values)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-success me-2"></i>Created Data</h6>
            </div>
            <div class="card-body bg-dark">
                <pre class="text-white mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode(is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true), JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    {{-- Deleted Data --}}
    @if($log->event === 'deleted' && $log->old_values)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-trash-alt text-danger me-2"></i>Deleted Data</h6>
            </div>
            <div class="card-body bg-dark">
                <pre class="text-white mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode(is_array($log->old_values) ? $log->old_values : json_decode($log->old_values, true), JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    {{-- Raw Data --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-code text-secondary me-2"></i>Raw Data</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                @if($log->old_values)
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">Old Values</label>
                        <div class="bg-dark rounded p-3">
                            <pre class="text-white mb-0 small" style="max-height: 200px; overflow-y: auto;"><code>{{ json_encode(is_array($log->old_values) ? $log->old_values : json_decode($log->old_values, true), JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif
                @if($log->new_values)
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">New Values</label>
                        <div class="bg-dark rounded p-3">
                            <pre class="text-white mb-0 small" style="max-height: 200px; overflow-y: auto;"><code>{{ json_encode(is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true), JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Rollback Modal --}}
<div class="modal fade" id="rollbackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.disaster-recovery.activity.rollback', $log) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-warning">
                        <i class="fas fa-undo me-2"></i>Confirm Rollback
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will revert <strong>{{ class_basename($log->auditable_type) }}</strong> to its previous state.
                    </div>
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
function showRollbackModal() {
    new bootstrap.Modal(document.getElementById('rollbackModal')).show();
}
</script>
@endpush
@endsection
