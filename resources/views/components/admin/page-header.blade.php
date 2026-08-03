@props([
    'title',
    'subtitle' => null,
    'icon' => 'bi-speedometer2',
])

<div class="admin-page-header">
    <div class="admin-page-header__title-group">
        <div>
            <h1 class="admin-page-header__title">
                <i class="{{ $icon }} admin-page-header__icon" aria-hidden="true"></i>
                <span>{{ $title }}</span>
            </h1>
            @if($subtitle)
                <p class="admin-page-header__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(trim($slot) !== '')
        <div class="admin-page-header__actions">
            {{ $slot }}
        </div>
    @endif
</div>
