@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-folder-symlink me-2 text-primary"></i>Batch Drafts
            </h2>
            <p class="text-muted mb-0">Manage student imports and course configurations</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('chairperson.batch-drafts.wizard') }}" class="btn btn-primary rounded-pill shadow-sm">
                <i class="bi bi-stars me-1"></i>Quick Setup
            </a>
            <a href="{{ route('chairperson.batch-drafts.bulk-operations') }}" class="btn btn-warning rounded-pill shadow-sm">
                <i class="bi bi-lightning-charge-fill me-1"></i>Bulk Operations
            </a>
        </div>
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
                                        <a class="dropdown-item" href="{{ route('chairperson.batch-drafts.duplicate', $batch) }}">
                                            <i class="bi bi-files me-2"></i>Duplicate
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
                                              onsubmit="return confirm('Delete {{ addslashes($batch->batch_name) }}? This action cannot be undone.');">
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
                        <a href="{{ route('chairperson.batch-drafts.wizard') }}" class="btn btn-primary rounded-pill">
                            <i class="bi bi-stars me-2"></i>Quick Setup
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
.hover-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 1rem;
    overflow: hidden;
    position: relative;
}

.hover-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.hover-card:hover::before {
    left: 100%;
}

.hover-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
}

.btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
}

.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
}
</style>
@endsection
