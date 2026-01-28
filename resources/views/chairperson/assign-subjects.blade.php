@php
    $ordinalLabels = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'];
@endphp

@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-person-badge text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Assign Courses to Instructors</span>
    </h1>
    <p class="text-muted mb-4">Assign subjects to instructors by year level</p>

    {{-- Toast Notifications --}}
    @include('chairperson.partials.toast-notifications')

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="yearTabs" role="tablist" style="background: transparent; border-bottom: 2px solid #dee2e6;">
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

    <style>
        #yearTabs {
            background: transparent !important;
        }
        #yearTabs .nav-link {
            background-color: transparent !important;
            color: #6c757d !important;
            transition: all 0.3s ease;
            position: relative;
        }
        #yearTabs .nav-link:not(.active):hover {
            background-color: rgba(25, 135, 84, 0.08) !important;
            color: var(--dark-green) !important;
        }
        #yearTabs .nav-link.active {
            background-color: rgba(25, 135, 84, 0.12) !important;
            color: var(--dark-green) !important;
            border-bottom: 3px solid var(--dark-green) !important;
            margin-bottom: -2px;
            z-index: 1;
        }
        #yearTabsContent {
            background: transparent !important;
            padding-top: 1.5rem;
        }
        #yearTabsContent .tab-pane {
            background: transparent !important;
        }
    </style>

    <div class="tab-content" id="yearTabsContent" style="background: transparent;">
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

{{-- Modals --}}
@include('chairperson.partials.assign-modals')

{{-- JavaScript: resources/js/pages/chairperson/assign-subjects.js --}}
@endsection