@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-success mb-1">
                <i class="bi bi-file-earmark-text me-2"></i>Course Outcome Templates
            </h2>
            <p class="text-muted mb-0">Manage reusable CO configurations for subjects</p>
        </div>
        <a href="{{ route('chairperson.co-templates.create') }}" class="btn btn-success rounded-pill">
            <i class="bi bi-plus-circle me-2"></i>Create New Template
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Templates Grid -->
    <div class="row g-4">
        @forelse($templates as $template)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <!-- Card Header -->
                    <div class="card-header bg-gradient text-white" 
                         style="background: linear-gradient(135deg, {{ $template->is_universal ? '#17a2b8' : '#198754' }}, {{ $template->is_universal ? '#20c997' : '#20c997' }});">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1 fw-bold">{{ $template->template_name }}</h5>
                                <small class="opacity-75">
                                    @if($template->is_universal)
                                        <i class="bi bi-globe me-1"></i>Universal Template
                                    @else
                                        <i class="bi bi-mortarboard me-1"></i>{{ $template->course->course_code ?? 'N/A' }}
                                    @endif
                                </small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light rounded-circle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('chairperson.co-templates.show', $template) }}">
                                            <i class="bi bi-eye me-2"></i>View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('chairperson.co-templates.edit', $template) }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-warning" onclick="toggleStatus({{ $template->id }})">
                                            <i class="bi bi-toggle-{{ $template->is_active ? 'on' : 'off' }} me-2"></i>
                                            {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </li>
                                    <li>
                                        <form action="{{ route('chairperson.co-templates.destroy', $template) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this template?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        @if($template->description)
                            <p class="text-muted small mb-3">{{ Str::limit($template->description, 100) }}</p>
                        @endif

                        <!-- Template Stats -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-center">
                                <h4 class="mb-0 text-success fw-bold">{{ $template->items->count() }}</h4>
                                <small class="text-muted">CO Items</small>
                            </div>
                            <div class="vr"></div>
                            <div class="text-center">
                                <h4 class="mb-0 text-info fw-bold">{{ $template->batchDrafts->count() }}</h4>
                                <small class="text-muted">In Use</small>
                            </div>
                            <div class="vr"></div>
                            <div class="text-center">
                                @if($template->is_active)
                                    <span class="badge bg-success rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">
                                        <i class="bi bi-x-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- CO Items Preview -->
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted fw-semibold d-block mb-2">CO Items:</small>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($template->items->take(3) as $item)
                                    <li class="mb-1">
                                        <span class="badge bg-success-subtle text-success me-1">{{ $item->co_code }}</span>
                                        {{ Str::limit($item->description, 50) }}
                                    </li>
                                @endforeach
                                @if($template->items->count() > 3)
                                    <li class="text-muted fst-italic">
                                        +{{ $template->items->count() - 3 }} more...
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span>
                                <i class="bi bi-person me-1"></i>{{ $template->creator->name ?? 'Unknown' }}
                            </span>
                            <span>
                                <i class="bi bi-calendar me-1"></i>{{ $template->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">No CO Templates Found</h4>
                        <p class="text-muted mb-4">
                            Create your first Course Outcome template to streamline subject configuration.
                        </p>
                        <a href="{{ route('chairperson.co-templates.create') }}" class="btn btn-success rounded-pill">
                            <i class="bi bi-plus-circle me-2"></i>Create Template
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(templateId) {
    if (!confirm('Are you sure you want to toggle the status of this template?')) {
        return;
    }

    fetch(`/chairperson/co-templates/${templateId}/toggle-status`, {
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

<style>
.hover-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient {
    background: linear-gradient(135deg, #198754, #20c997);
}
</style>
@endsection
