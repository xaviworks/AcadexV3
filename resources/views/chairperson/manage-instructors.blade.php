@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/chairperson/common.css --}}

<div class="import-courses-wrapper">
    <div class="import-courses-container">
        {{-- Page Header --}}
        @include('chairperson.partials.page-header', [
            'title' => 'Instructor Account Management',
            'subtitle' => 'Manage instructor accounts, view status, and assign GE subject permissions',
            'icon' => 'bi-person-lines-fill'
        ])

        {{-- Toast Notifications --}}
        @include('chairperson.partials.toast-notifications')

        {{-- Status Alert --}}
        @if(session('status'))
            <div class="alert alert-success shadow-sm rounded">
                {{ session('status') }}
            </div>
        @endif

        {{-- Instructor Tabs --}}
        <ul class="nav nav-tabs" id="instructorTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" 
                   id="active-instructors-tab" 
                   data-bs-toggle="tab" 
                   href="#active-instructors" 
                   role="tab" 
                   aria-controls="active-instructors" 
                   aria-selected="true">
                    Active Instructors
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" 
                   id="inactive-instructors-tab" 
                   data-bs-toggle="tab" 
                   href="#inactive-instructors" 
                   role="tab" 
                   aria-controls="inactive-instructors" 
                   aria-selected="false">
                    Inactive Instructors
                </a>
            </li>
        </ul>

        <div class="tab-content" id="instructorTabsContent">
            {{-- Active Instructors Tab --}}
            <div class="tab-pane fade show active" id="active-instructors" role="tabpanel" aria-labelledby="active-instructors-tab">
                @include('chairperson.partials.instructor-table', [
                    'instructors' => $instructors,
                    'filterActive' => true,
                    'showGERequest' => true,
                    'geRequests' => $geRequests
                ])
            </div>

            {{-- Inactive Instructors Tab --}}
            <div class="tab-pane fade" id="inactive-instructors" role="tabpanel" aria-labelledby="inactive-instructors-tab">
                @include('chairperson.partials.instructor-table', [
                    'instructors' => $instructors,
                    'filterActive' => false,
                    'showGERequest' => false,
                    'geRequests' => collect()
                ])
            </div>
        </div>

        {{-- Pending Account Approvals Section --}}
        @include('chairperson.partials.pending-accounts-table', [
            'pendingAccounts' => $pendingAccounts
        ])
    </div>
</div>

{{-- Modals --}}
@include('chairperson.partials.instructor-modals')

{{-- JavaScript: resources/js/pages/chairperson/manage-instructors.js --}}
@endsection
