@php
    $ordinalLabels = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'];
@endphp

@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        {{-- Page Header --}}
        @include('chairperson.partials.page-header', [
            'title' => 'Assign Courses to Instructors',
            'subtitle' => 'Assign subjects to instructors by year level',
            'icon' => 'bi-person-badge'
        ])

        {{-- Toast Notifications --}}
        @include('chairperson.partials.toast-notifications')

        {{-- YEAR VIEW (Tabbed) --}}
        <div>
            <ul class="nav nav-tabs" id="yearTabs" role="tablist">
                @for ($level = 1; $level <= 4; $level++)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $level === 1 ? 'active' : '' }}"
                           id="year-level-{{ $level }}-tab"
                           data-bs-toggle="tab"
                           href="#level-{{ $level }}"
                           role="tab"
                           aria-controls="level-{{ $level }}"
                           aria-selected="{{ $level === 1 ? 'true' : 'false' }}">
                            {{ $ordinalLabels[$level] }} Year
                        </a>
                    </li>
                @endfor
            </ul>

            <div class="tab-content" id="yearTabsContent">
                @for ($level = 1; $level <= 4; $level++)
                    @php
                        $subjectsByYear = $yearLevels[$level] ?? collect();
                    @endphp

                    <div class="tab-pane fade {{ $level === 1 ? 'show active' : '' }}"
                         id="level-{{ $level }}"
                         role="tabpanel"
                         aria-labelledby="year-level-{{ $level }}-tab">
                        @include('chairperson.partials.subject-assignment-table', [
                            'subjects' => $subjectsByYear,
                            'yearLevel' => $level
                        ])
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('chairperson.partials.assign-modals')

{{-- JavaScript: resources/js/pages/chairperson/assign-subjects.js --}}
@endsection