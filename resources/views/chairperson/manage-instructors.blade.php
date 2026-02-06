@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-person-lines-fill text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Instructor Account Management</span>
    </h1>
    <p class="text-muted mb-4">Manage instructor accounts, view status, and assign GE subject permissions</p>

    {{-- Toast Notifications --}}
    @include('chairperson.partials.toast-notifications')

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="instructorTabs" role="tablist" style="background: transparent; border-bottom: 2px solid #dee2e6;">
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

    <style>
        #instructorTabs {
            background: transparent !important;
        }
        #instructorTabs .nav-link {
            background-color: transparent !important;
            color: #6c757d !important;
            transition: all 0.3s ease;
            position: relative;
        }
        #instructorTabs .nav-link:not(.active):hover {
            background-color: rgba(25, 135, 84, 0.08) !important;
            color: var(--dark-green) !important;
        }
        #instructorTabs .nav-link.active {
            background-color: rgba(25, 135, 84, 0.12) !important;
            color: var(--dark-green) !important;
            border-bottom: 3px solid var(--dark-green) !important;
            margin-bottom: -2px;
            z-index: 1;
        }
        #instructorTabsContent {
            background: transparent !important;
            padding-top: 1.5rem;
        }
        #instructorTabsContent .tab-pane {
            background: transparent !important;
        }
    </style>

    <div class="tab-content" id="instructorTabsContent" style="background: transparent;">
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

{{-- Modals --}}
@include('chairperson.partials.instructor-modals')

{{-- JavaScript: resources/js/pages/chairperson/manage-instructors.js --}}
@endsection
