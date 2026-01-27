{{--
    Chairperson Page Header Partial
    
    Usage:
    @include('chairperson.partials.page-header', [
        'title' => 'Page Title',
        'subtitle' => 'Optional subtitle description',
        'icon' => 'bi-icon-name', // Bootstrap icon class
        'actions' => [optional view for action buttons]
    ])
--}}

<div class="page-title">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1>
                <i class="bi {{ $icon ?? 'bi-folder' }}"></i>
                {{ $title }}
            </h1>
            @if(!empty($subtitle))
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if(!empty($actions))
            <div class="d-flex align-items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
