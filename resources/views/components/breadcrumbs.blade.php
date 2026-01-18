@props(['items' => []])

@if (!empty($items))
<div class="mb-3">
    <ol class="breadcrumb mb-0 py-0 px-0 border-0 d-flex align-items-center">
        @foreach ($items as $index => $item)
            @php
                $isLast = $index === count($items) - 1;
                $url = $item['url'] ?? null;
                $label = $item['label'] ?? $item;
                $icon = $item['icon'] ?? null;
            @endphp
            
            <li class="breadcrumb-item {{ $isLast ? 'active' : '' }} d-flex align-items-center" {{ $isLast ? 'aria-current=page' : '' }}>
                @if ($isLast)
                    <span class="fw-semibold text-success" style="line-height: 1;">{{ $label }}</span>
                @else
                    <a href="{{ $url }}" class="text-decoration-none text-success">
                        <span style="line-height: 1;">{{ $label }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</div>

<style>
    .breadcrumb {
        --bs-breadcrumb-divider: '›';
        --bs-breadcrumb-bg: transparent;
        background: none !important;
        background-color: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        border-radius: 0 !important;
    }
    
    .breadcrumb-item {
        background: none !important;
        background-color: transparent !important;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d;
        font-weight: 600;
    }
    
    .breadcrumb-item a:hover {
        color: #198754 !important;
        text-decoration: underline !important;
    }
    
    .breadcrumb-item.active {
        color: #198754 !important;
    }
</style></style>
@endif
