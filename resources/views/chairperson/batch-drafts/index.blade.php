@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-success mb-1">
                <i class="bi bi-folder-symlink me-2"></i>Batch Drafts
            </h2>
            <p class="text-muted mb-0">Configure student imports and CO templates for multiple subjects</p>
        </div>
        <a href="{{ route('chairperson.batch-drafts.create') }}" class="btn btn-success rounded-pill">
            <i class="bi bi-plus-circle me-2"></i>Create Batch Draft
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

    <!-- Batch Drafts List -->
    <div class="row g-3">
        @forelse($batchDrafts as $batch)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1 fw-bold text-dark">{{ $batch->batch_name }}</h5>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $batch->course->course_code ?? 'N/A' }}
                                    </span>
                                    <small class="text-muted">Year {{ $batch->year_level }}</small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light rounded-circle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('chairperson.batch-drafts.show', $batch) }}">
                                            <i class="bi bi-eye me-2"></i>View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('chairperson.batch-drafts.edit', $batch) }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('chairperson.batch-drafts.destroy', $batch) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete this batch draft?\n\nThis action cannot be undone.');">
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

                        <!-- Quick Stats -->
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-center">
                                <div class="fs-5 fw-bold text-primary">{{ $batch->students->count() }}</div>
                                <small class="text-muted">Students</small>
                            </div>
                            <div class="vr"></div>
                            <div class="text-center">
                                <div class="fs-5 fw-bold text-info">{{ $batch->subjects->count() }}</div>
                                <small class="text-muted">Subjects</small>
                            </div>
                            <div class="vr"></div>
                            <div class="text-center">
                                @php
                                    $totalSubjects = $batch->subjects->count();
                                    $appliedSubjects = $batch->batchDraftSubjects->where('configuration_applied', true)->count();
                                    $progress = $totalSubjects > 0 ? ($appliedSubjects / $totalSubjects) * 100 : 0;
                                @endphp
                                <div class="fs-5 fw-bold 
                                    @if($progress == 100) text-success 
                                    @elseif($progress > 0) text-warning 
                                    @else text-muted 
                                    @endif">
                                    {{ number_format($progress, 0) }}%
                                </div>
                                <small class="text-muted">Complete</small>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($totalSubjects == 0)
                            <span class="badge bg-secondary-subtle text-secondary">
                                <i class="bi bi-circle me-1"></i>No Subjects
                            </span>
                        @elseif($progress == 100)
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-check-circle-fill me-1"></i>All Configured
                            </span>
                        @elseif($progress > 0)
                            <span class="badge bg-warning-subtle text-warning">
                                <i class="bi bi-hourglass-split me-1"></i>In Progress
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info">
                                <i class="bi bi-clock me-1"></i>Pending
                            </span>
                        @endif
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-light border-0">
                        <a href="{{ route('chairperson.batch-drafts.show', $batch) }}" 
                           class="btn btn-sm btn-primary w-100">
                            View Details <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-symlink display-1 text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">No Batch Drafts Found</h4>
                        <p class="text-muted mb-4">
                            Create your first batch draft to import students and configure subjects.
                        </p>
                        <a href="{{ route('chairperson.batch-drafts.create') }}" class="btn btn-success rounded-pill">
                            <i class="bi bi-plus-circle me-2"></i>Create Batch Draft
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient {
    background: linear-gradient(135deg, #0d6efd, #0dcaf0);
}
</style>
@endsection
