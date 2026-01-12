@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('chairperson.co-templates.index') }}">CO Templates</a></li>
                    <li class="breadcrumb-item active">{{ $coTemplate->template_name }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-success mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>{{ $coTemplate->template_name }}
            </h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('chairperson.co-templates.edit', $coTemplate) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-2"></i>Edit Template
            </a>
            <a href="{{ route('chairperson.co-templates.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Template Details -->
        <div class="col-lg-8">
            <!-- Template Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Template Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">Template Name</label>
                            <p class="mb-0 fw-semibold">{{ $coTemplate->template_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">Status</label>
                            <p class="mb-0">
                                @if($coTemplate->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">Type</label>
                            <p class="mb-0">
                                @if($coTemplate->is_universal)
                                    <span class="badge bg-info">
                                        <i class="bi bi-globe me-1"></i>Universal Template
                                    </span>
                                @else
                                    <span class="badge bg-primary">
                                        <i class="bi bi-mortarboard me-1"></i>{{ $coTemplate->course->course_code ?? 'Course-Specific' }}
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">Created By</label>
                            <p class="mb-0">{{ $coTemplate->creator->name ?? 'Unknown' }}</p>
                        </div>
                        @if($coTemplate->description)
                            <div class="col-12">
                                <label class="text-muted small fw-semibold">Description</label>
                                <p class="mb-0">{{ $coTemplate->description }}</p>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">Created Date</label>
                            <p class="mb-0">{{ $coTemplate->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">Last Updated</label>
                            <p class="mb-0">{{ $coTemplate->updated_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CO Items Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #198754, #20c997);">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>Course Outcome Items ({{ $coTemplate->items->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">CO Code</th>
                                    <th>Description</th>
                                    <th style="width: 80px;" class="text-center">Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coTemplate->items as $item)
                                    <tr>
                                        <td>
                                            <span class="badge bg-success-subtle text-success fs-6">
                                                {{ $item->co_code }}
                                            </span>
                                        </td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $item->order }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                            No CO items defined
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Usage Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="display-4 text-info mb-0">{{ $coTemplate->batchDrafts()->count() }}</h2>
                        <small class="text-muted">Batch Drafts Using This Template</small>
                    </div>
                    <div class="text-center">
                        <h3 class="display-6 text-success mb-0">{{ $coTemplate->items->count() }}</h3>
                        <small class="text-muted">Total CO Items</small>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-gear me-2"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('chairperson.co-templates.edit', $coTemplate) }}" 
                           class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i>Edit Template
                        </a>
                        
                        <button type="button" 
                                class="btn btn-outline-{{ $coTemplate->is_active ? 'secondary' : 'success' }}"
                                onclick="toggleStatus()">
                            <i class="bi bi-toggle-{{ $coTemplate->is_active ? 'on' : 'off' }} me-2"></i>
                            {{ $coTemplate->is_active ? 'Deactivate' : 'Activate' }} Template
                        </button>
                        
                        @if($coTemplate->batchDrafts()->count() === 0)
                            <form action="{{ route('chairperson.co-templates.destroy', $coTemplate) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-trash me-2"></i>Delete Template
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-danger" disabled title="Cannot delete: Template is in use">
                                <i class="bi bi-trash me-2"></i>Delete Template (In Use)
                            </button>
                        @endif>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatus() {
    if (!confirm('Are you sure you want to toggle the status of this template?')) {
        return;
    }

    fetch(`{{ route('chairperson.co-templates.toggle-status', $coTemplate) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to toggle status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while toggling status.');
    });
}
</script>
@endpush
@endsection
