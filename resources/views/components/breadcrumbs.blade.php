@props(['items' => []])

@php
    $normalizedItems = [];

    foreach ($items as $item) {
        $rawLabel = is_array($item) ? ($item['label'] ?? '') : $item;
        $label = trim((string) $rawLabel);

        if ($label === '') {
            continue;
        }

        if (strtolower($label) === 'home') {
            $label = 'Dashboard';
        }

        if (is_array($item)) {
            $item['label'] = $label;
            $normalizedItems[] = $item;
        } else {
            $normalizedItems[] = ['label' => $label];
        }
    }

    // Defensive fix: keep a stable 3-level breadcrumb on Course Outcomes detail pages.
    if (request()->routeIs('*.course_outcomes.index') && request()->filled('subject_id')) {
        $routeName = optional(request()->route())->getName();
        $routePrefix = $routeName ? explode('.', $routeName)[0] : null;

        $dashboardUrl = '/';
        $viewOutcomesUrl = request()->url();

        if ($routePrefix) {
            $dashboardRouteName = $routePrefix . '.dashboard';
            $viewOutcomesRouteName = $routePrefix . '.course_outcomes.index';

            if (\Illuminate\Support\Facades\Route::has($dashboardRouteName)) {
                $dashboardUrl = route($dashboardRouteName);
            }

            if (\Illuminate\Support\Facades\Route::has($viewOutcomesRouteName)) {
                $viewOutcomesUrl = route($viewOutcomesRouteName);
            }
        }

        $subjectLabel = null;
        foreach (array_reverse($normalizedItems) as $entry) {
            $entryLabel = trim((string) ($entry['label'] ?? ''));
            if ($entryLabel !== '' && strtolower($entryLabel) !== 'dashboard' && strtolower($entryLabel) !== 'view outcomes') {
                $subjectLabel = $entryLabel;
                break;
            }
        }

        $normalizedItems = [
            ['label' => 'Dashboard', 'url' => $dashboardUrl],
            ['label' => 'View Outcomes', 'url' => $viewOutcomesUrl],
        ];

        if ($subjectLabel) {
            $normalizedItems[] = ['label' => $subjectLabel];
        }
    }
@endphp

@if (!empty($normalizedItems))
<div class="mb-3">
    <ol class="breadcrumb mb-0 py-0 px-0 border-0 d-flex align-items-center">
        @foreach ($normalizedItems as $index => $item)
            @php
                $isLast = $index === count($normalizedItems) - 1;
                $url = $item['url'] ?? null;
                $label = $item['label'] ?? $item;
                $icon = $item['icon'] ?? null;
            @endphp
            
            <li class="breadcrumb-item {{ $isLast ? 'active' : '' }} d-flex align-items-center" {{ $isLast ? 'aria-current=page' : '' }}>
                @if ($isLast || empty($url))
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
</style>
@endif
