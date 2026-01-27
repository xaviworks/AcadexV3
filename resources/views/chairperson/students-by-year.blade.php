@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        {{-- Page Header --}}
        @include('chairperson.partials.page-header', [
            'title' => 'Students List',
            'subtitle' => 'View all students under your department and filter by year level',
            'icon' => 'bi-people-fill'
        ])

        @if($students->isEmpty())
            <div class="bg-warning bg-opacity-25 text-warning border border-warning px-4 py-3 rounded-4 shadow-sm">
                No students found under your department and course.
            </div>
        @else
            {{-- Year Level Tabs --}}
            <ul class="nav nav-tabs" id="yearTabs" role="tablist">
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

            <div class="tab-content" id="yearTabsContent">
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
</div>
@endsection
