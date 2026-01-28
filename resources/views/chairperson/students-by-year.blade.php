@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-people-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Students List</span>
    </h1>
    <p class="text-muted mb-4">View all students under your department and filter by year level</p>

        @if($students->isEmpty())
            <div class="bg-warning bg-opacity-25 text-warning border border-warning px-4 py-3 rounded-4 shadow-sm">
                No students found under your department and course.
            </div>
        @else
            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-0" id="yearTabs" role="tablist" style="background: transparent; border-bottom: 2px solid #dee2e6;">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" 
                       id="all-years-tab" 
                       data-bs-toggle="tab" 
                       href="#all-years" 
                       role="tab" 
                       aria-controls="all-years" 
                       aria-selected="true">
                        All Years
                    </a>
                </li>
                @for ($level = 1; $level <= 4; $level++)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" 
                           id="year-{{ $level }}-tab" 
                           data-bs-toggle="tab" 
                           href="#year-{{ $level }}" 
                           role="tab" 
                           aria-controls="year-{{ $level }}" 
                           aria-selected="false">
                            {{ $level == 1 ? '1st' : ($level == 2 ? '2nd' : ($level == 3 ? '3rd' : '4th')) }} Year
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
                {{-- All Years Tab --}}
                <div class="tab-pane fade show active" id="all-years" role="tabpanel" aria-labelledby="all-years-tab">
                    @include('chairperson.partials.student-table', [
                        'students' => $students,
                        'showCourse' => true,
                        'showYearLevel' => true,
                        'emptyMessage' => 'No students found'
                    ])
                </div>

                {{-- Year Level Tabs --}}
                @for ($level = 1; $level <= 4; $level++)
                    <div class="tab-pane fade" id="year-{{ $level }}" role="tabpanel" aria-labelledby="year-{{ $level }}-tab">
                        @include('chairperson.partials.student-table', [
                            'students' => $students->where('year_level', $level),
                            'showCourse' => true,
                            'showYearLevel' => true,
                            'emptyMessage' => 'No students found for this year level'
                        ])
                    </div>
                @endfor
            </div>
        @endif
</div>
@endsection
