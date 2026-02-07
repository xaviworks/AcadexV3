{{--
    Empty State Component
    =====================
    A reusable, consistent empty/no-data state card for all views.

    Usage:
        <x-empty-state
            icon="bi-folder-x"
            title="No Subjects Found"
            message="No subjects found for the current academic period."
        />

    Props:
        icon       (string)   Bootstrap icon class (default: 'bi-inbox')
        title      (string)   Heading text (default: 'No Data Found')
        message    (string)   Description text below heading (optional)
        compact    (bool)     Use a smaller inline style without card wrapper (default: false)

    Slots:
        default    (slot)     Extra content (e.g., info alerts, checklists) below message
        actions    (slot)     Action buttons at the bottom
--}}

@props([
    'icon' => 'bi-inbox',
    'title' => 'No Data Found',
    'message' => '',
    'compact' => false,
])

@if($compact)
    {{-- Compact variant: no card wrapper, used inside tables/cards --}}
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="bi {{ $icon }} text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
        </div>
        <h5 class="text-muted mb-2">{{ $title }}</h5>
        @if($message)
            <p class="text-muted mb-0">{!! $message !!}</p>
        @endif

        {{-- Extra content slot --}}
        @if(!$slot->isEmpty())
            <div class="mt-3">
                {{ $slot }}
            </div>
        @endif

        {{-- Actions slot --}}
        @if(isset($actions) && !$actions->isEmpty())
            <div class="d-flex justify-content-center gap-3 mt-4">
                {{ $actions }}
            </div>
        @endif
    </div>
@else
    {{-- Full card variant: standalone empty state --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <div class="text-muted mb-3">
                <i class="bi {{ $icon }} fs-1 opacity-50"></i>
            </div>
            <h5 class="text-muted mb-2">{{ $title }}</h5>
            @if($message)
                <p class="text-muted mb-4">{!! $message !!}</p>
            @endif

            {{-- Extra content slot --}}
            @if(!$slot->isEmpty())
                <div class="mb-4">
                    {{ $slot }}
                </div>
            @endif

            {{-- Actions slot --}}
            @if(isset($actions) && !$actions->isEmpty())
                <div class="d-flex justify-content-center gap-3">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
@endif
