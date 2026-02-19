@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css --}}
{{-- Styles: resources/css/chairperson/structure-templates.css --}}

@section('content')
@php
    // Prepare initial data for Alpine.js hydration (same shape as poll endpoint)
    $requestsData = $requests->map(function ($r) {
        return [
            'id' => $r->id,
            'label' => $r->label,
            'description' => $r->description,
            'structure_config' => $r->structure_config,
            'status' => $r->status,
            'admin_notes' => $r->admin_notes,
            'created_at_formatted' => $r->created_at->format('M d, Y'),
            'reviewed_at' => $r->reviewed_at?->format('M d, Y'),
            'show_url' => route('chairperson.structureTemplates.show', $r),
            'destroy_url' => route('chairperson.structureTemplates.destroy', $r),
        ];
    })->values();

    $countsData = [
        'pending' => $requests->where('status', 'pending')->count(),
        'approved' => $requests->where('status', 'approved')->count(),
        'rejected' => $requests->where('status', 'rejected')->count(),
    ];
@endphp

<script>
    window.chairpersonTemplateRequestsConfig = {
        requests: @json($requestsData),
        counts: @json($countsData),
        pollUrl: @json(route('chairperson.structureTemplates.poll')),
    };
</script>

<div class="container-fluid px-4 py-4" x-data="templateRequestsChairperson()" x-init="init()">
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="text-2xl font-bold mb-2 d-flex align-items-center">
                <i class="bi bi-diagram-3 text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
                <span>Structure Formula Requests</span>
            </h1>
            <p class="text-muted mb-0">Create and manage your custom grading structure formula requests</p>
        </div>
        <a href="{{ route('chairperson.structureTemplates.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>New Formula Request
        </a>
    </div>

    {{-- Toast Notifications --}}
    @include('chairperson.partials.toast-notifications')

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Statistics Summary (reactive) --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex gap-4 align-items-center justify-content-center flex-wrap">
                <div class="text-center">
                    <div class="fw-bold text-warning" style="font-size: 1.75rem;" x-text="counts.pending"></div>
                    <small class="text-muted">Pending</small>
                </div>
                <div class="text-center">
                    <div class="fw-bold text-success" style="font-size: 1.75rem;" x-text="counts.approved"></div>
                    <small class="text-muted">Approved</small>
                </div>
                <div class="text-center">
                    <div class="fw-bold text-danger" style="font-size: 1.75rem;" x-text="counts.rejected"></div>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty State --}}
    <template x-if="requests.length === 0">
        <x-empty-state
            icon="bi-diagram-3"
            title="No Formula Requests Yet"
            message="Create your first custom grading structure formula request."
        >
            <x-slot:actions>
                <a href="{{ route('chairperson.structureTemplates.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>Create New Request
                </a>
            </x-slot:actions>
        </x-empty-state>
    </template>

    {{-- Request Cards (rendered from Alpine data) --}}
    <template x-if="requests.length > 0">
        <div class="row g-4">
            <template x-for="req in requests" :key="req.id">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 request-card" :data-status="req.status">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge px-3 py-2" :class="getStatusBadge(req.status).class">
                                    <i class="me-1" :class="getStatusBadge(req.status).icon"></i><span x-text="getStatusBadge(req.status).label"></span>
                                </span>
                                <template x-if="req.status === 'pending'">
                                    <form method="POST" :action="req.destroy_url"
                                          onsubmit="return confirm('Are you sure you want to delete this pending request?');"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </template>
                            </div>

                            <h5 class="card-title fw-bold mb-2" x-text="req.label"></h5>

                            <template x-if="req.description">
                                <p class="text-muted small mb-3" x-text="truncate(req.description, 100)"></p>
                            </template>

                            <div class="mb-3">
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-diagram-2 me-1"></i><span x-text="getStructureLabel(req.structure_config)"></span>
                                </span>
                            </div>

                            <div class="text-muted small mb-3">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-calendar"></i>
                                    <span x-text="'Submitted: ' + req.created_at_formatted"></span>
                                </div>
                                <template x-if="req.reviewed_at">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-check"></i>
                                        <span x-text="'Reviewed: ' + req.reviewed_at"></span>
                                    </div>
                                </template>
                            </div>

                            <template x-if="req.admin_notes && (req.status === 'approved' || req.status === 'rejected')">
                                <div class="alert alert-sm mb-3" :class="req.status === 'approved' ? 'alert-success' : 'alert-danger'">
                                    <strong>Admin Notes:</strong>
                                    <p class="mb-0 mt-1 small" x-text="truncate(req.admin_notes, 80)"></p>
                                </div>
                            </template>

                            <a :href="req.show_url" class="btn btn-outline-success btn-sm w-100">
                                <i class="bi bi-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection
{{-- Styles: resources/css/chairperson/common.css --}}
{{-- Styles: resources/css/chairperson/structure-templates.css --}}
