{{--
    Year Level Tabs Partial
    
    Usage:
    @include('chairperson.partials.year-level-tabs', [
        'tabId' => 'yearTabs',
        'contentId' => 'yearTabsContent',
        'includeAll' => true, // Include "All Years" tab
        'activeTab' => 'all' // 'all', 1, 2, 3, or 4
    ])
--}}

@php
    $tabId = $tabId ?? 'yearTabs';
    $contentId = $contentId ?? 'yearTabsContent';
    $includeAll = $includeAll ?? false;
    $activeTab = $activeTab ?? ($includeAll ? 'all' : 1);
    
    $yearLabels = [
        1 => '1st Year',
        2 => '2nd Year',
        3 => '3rd Year',
        4 => '4th Year'
    ];
@endphp

<ul class="nav nav-tabs" id="{{ $tabId }}" role="tablist">
    @if($includeAll)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}" 
               id="all-years-tab" 
               data-bs-toggle="tab" 
               href="#all-years" 
               role="tab" 
               aria-controls="all-years" 
               aria-selected="{{ $activeTab === 'all' ? 'true' : 'false' }}">
                All Years
            </a>
        </li>
    @endif
    @for ($level = 1; $level <= 4; $level++)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === $level ? 'active' : '' }}" 
               id="year-level-{{ $level }}-tab" 
               data-bs-toggle="tab" 
               href="#year-level-{{ $level }}" 
               role="tab" 
               aria-controls="year-level-{{ $level }}" 
               aria-selected="{{ $activeTab === $level ? 'true' : 'false' }}">
                {{ $yearLabels[$level] }}
            </a>
        </li>
    @endfor
</ul>
