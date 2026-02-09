{{--
    Reports Page Header Partial
    
    Usage:
    @include('chairperson.partials.reports-header', [
        'title' => 'Page Title',
        'subtitle' => 'Optional subtitle',
        'icon' => 'bi-icon-name',
        'academicYear' => $academicYear,
        'semester' => $semester,
        'backRoute' => route('some.route'),
        'backLabel' => 'Back to Dashboard'
    ])
--}}

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi {{ $icon ?? 'bi-bar-chart' }} text-success me-2"></i>{{ $title }}
        </h2>
        @if(!empty($subtitle))
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(!empty($backRoute))
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>{{ $backLabel ?? 'Back' }}
            </a>
        @endif
    </div>
</div>
