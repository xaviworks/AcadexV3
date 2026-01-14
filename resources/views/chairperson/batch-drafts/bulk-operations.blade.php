@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold text-success mb-1">
                <i class="bi bi-lightning-charge-fill me-2"></i>Bulk Operations
            </h4>
            <p class="text-muted small mb-0">Configure multiple subjects at once</p>
        </div>
        <a href="{{ route('chairperson.batch-drafts.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm hover-lift">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    <!-- Quick Stats -->
    @php
        $configured = $subjects->filter(function($s) { 
            return $s->batchDraftSubject && $s->batchDraftSubject->configuration_applied; 
        })->count();
        $notConfigured = $subjects->count() - $configured;
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center py-2">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-book-half fs-4 text-primary"></i>
                        <div class="text-start">
                            <h5 class="fw-bold mb-0">{{ $subjects->count() }}</h5>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center py-2">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                        <div class="text-start">
                            <h5 class="fw-bold mb-0 text-success">{{ $configured }}</h5>
                            <small class="text-muted">Configured</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center py-2">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                        <div class="text-start">
                            <h5 class="fw-bold mb-0 text-warning">{{ $notConfigured }}</h5>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apply Configuration Form -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('chairperson.batch-drafts.bulk-apply') }}" method="POST" id="bulkForm">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">Select Batch Draft</label>
                        <select name="batch_draft_id" id="batch_draft_id" class="form-select" required>
                            <option value="">Choose batch draft...</option>
                            @foreach($batchDrafts as $batch)
                                <option value="{{ $batch->id }}">
                                    {{ $batch->batch_name }} ({{ $batch->students->count() }} students)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success w-100 rounded-pill shadow-sm hover-lift" onclick="applyToSelected()" id="applyBtn" disabled>
                            <i class="bi bi-check2-circle me-1"></i> Apply (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary w-100 rounded-pill shadow-sm hover-lift" onclick="selectAll()">
                            <i class="bi bi-check-all me-1"></i> Select All
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Subjects List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-list-check me-2 text-success"></i>Subjects ({{ $subjects->count() }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center"></th>
                            <th width="150">Code</th>
                            <th>Description</th>
                            <th width="120" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            <tr class="subject-row">
                                <td class="text-center">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" 
                                           class="form-check-input subject-checkbox"
                                           onchange="updateSelectedCount()">
                                </td>
                                <td class="fw-semibold">{{ $subject->subject_code }}</td>
                                <td class="small">{{ $subject->subject_description }}</td>
                                <td class="text-center">
                                    @if($subject->batchDraftSubject && $subject->batchDraftSubject->configuration_applied)
                                        <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Done</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">
                                    <i class="bi bi-inbox fs-4 d-block mb-2 opacity-50"></i>
                                    No subjects found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Update selected count and button state
function updateSelectedCount() {
    const count = document.querySelectorAll('.subject-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
    const batchSelected = document.getElementById('batch_draft_id').value;
    document.getElementById('applyBtn').disabled = !(batchSelected && count > 0);
}

// Batch selection handler
document.getElementById('batch_draft_id').addEventListener('change', updateSelectedCount);

// Select all checkboxes
function selectAll() {
    const allChecked = document.querySelectorAll('.subject-checkbox:checked').length === document.querySelectorAll('.subject-checkbox').length;
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = !allChecked;
    });
    updateSelectedCount();
}

// Apply to selected subjects
function applyToSelected() {
    const selected = document.querySelectorAll('.subject-checkbox:checked');
    
    if (selected.length === 0) {
        alert('Please select at least one subject');
        return;
    }
    
    if (confirm(`Apply configuration to ${selected.length} subject(s)?`)) {
        document.getElementById('bulkForm').submit();
    }
}
</script>

<style>
.stat-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 1rem;
    overflow: hidden;
    position: relative;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.stat-card:hover::before {
    left: 100%;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}

.stat-icon {
    transition: transform 0.3s;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1);
}

.card {
    border-radius: 1rem;
    transition: all 0.3s;
}

.form-select,
.form-control {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s;
}

.form-select:focus,
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}

.btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-outline-secondary {
    border-width: 2px;
}

.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.subject-row {
    transition: all 0.3s;
}

.subject-row:hover {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.05), transparent);
}

.table {
    border-radius: 0.5rem;
}

.badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
}
</style>
@endsection
