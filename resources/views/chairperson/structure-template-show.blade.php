@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css --}}

@section('content')
@php
    $statusBadge = match ($request->status) {
        'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'clock-history', 'label' => 'Pending Review'],
        'approved' => ['class' => 'bg-success', 'icon' => 'check-circle', 'label' => 'Approved'],
        'rejected' => ['class' => 'bg-danger', 'icon' => 'x-circle', 'label' => 'Rejected'],
        default => ['class' => 'bg-secondary', 'icon' => 'question-circle', 'label' => 'Unknown'],
    };
    
    $structureConfig = $request->structure_config;
    $structureType = $structureConfig['type'] ?? 'custom';
    $structureData = is_array($structureConfig['structure'] ?? null) ? $structureConfig['structure'] : [];
    $structureTypeLabel = data_get($structureCatalog, "$structureType.label", 'Custom');

    $groupedComponents = [];
    $componentLookup = [];
    $lastGroupIndex = null;

    foreach ($structureData as $entry) {
        $isMain = (bool) data_get($entry, 'is_main', false);

        if ($isMain) {
            $groupedComponents[] = [
                'component' => $entry,
                'sub_components' => [],
            ];

            $lastGroupIndex = array_key_last($groupedComponents);
            $componentKey = data_get($entry, 'component_id') ?? data_get($entry, 'id');

            if ($componentKey !== null) {
                $componentLookup[$componentKey] = $lastGroupIndex;
            }

            continue;
        }

        if ($lastGroupIndex === null) {
            continue;
        }

        $targetIndex = $lastGroupIndex;
        $parentKey = data_get($entry, 'parent_id');

        if ($parentKey !== null && array_key_exists($parentKey, $componentLookup)) {
            $targetIndex = $componentLookup[$parentKey];
        }

        $groupedComponents[$targetIndex]['sub_components'][] = $entry;
    }
@endphp

<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <div class="text-end mb-3">
        <a href="{{ route('chairperson.structureTemplates.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Requests
        </a>
    </div>

    <div class="text-center mb-3">
        <h1 class="text-2xl font-bold mb-2">
            <i class="bi bi-diagram-3 text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
            <span>{{ $request->label }}</span>
        </h1>
    </div>

    {{-- Breadcrumb Navigation --}}
    @php
        $breadcrumbItems = [
            ['label' => 'Formula Requests', 'url' => route('chairperson.structureTemplates.index')],
            ['label' => $request->label]
        ];
    @endphp
    <div class="d-flex justify-content-center">
        <x-breadcrumbs :items="$breadcrumbItems" />
    </div>

    {{-- Status Badge Centered --}}
    <div class="d-flex justify-content-center mb-4">
        <div class="card border-0 shadow-sm" style="max-width: 400px; width: 100%;">
            <div class="card-body text-center py-4">
                <div class="mb-2">
                    <i class="bi bi-{{ $statusBadge['icon'] }} {{ $request->status === 'approved' ? 'text-success' : ($request->status === 'rejected' ? 'text-danger' : 'text-warning') }}" style="font-size: 3rem;"></i>
                </div>
                <span class="badge {{ $statusBadge['class'] }} px-4 py-2" style="font-size: 1.1rem;">
                    {{ $statusBadge['label'] }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold">Request Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-semibold text-muted small">Template Name</label>
                        <p class="mb-0">{{ $request->label }}</p>
                    </div>
                    
                    @if ($request->description)
                        <div class="mb-3">
                            <label class="fw-semibold text-muted small">Description</label>
                            <p class="mb-0">{{ $request->description }}</p>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="fw-semibold text-muted small">Structure Type</label>
                        <p class="mb-0">
                            <span class="badge bg-info text-dark">{{ $structureTypeLabel }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold">Grading Structure</h5>
                </div>
                <div class="card-body">
                    @if (empty($groupedComponents))
                        <x-inline-alert type="muted" icon="bi-puzzle" message="No components defined for this grading structure." />
                    @else
                        <div class="structure-preview">
                            @foreach ($groupedComponents as $group)
                                @php
                                    $main = $group['component'];
                                    $subComponents = $group['sub_components'];
                                @endphp
                                <div class="card mb-3 border-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $main['label'] ?? 'Unnamed' }}</h6>
                                                <span class="badge bg-success-subtle text-success">{{ $main['activity_type'] ?? 'other' }}</span>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-success" style="font-size: 1.25rem;">{{ number_format((float) ($main['weight'] ?? 0), 2) }}%</div>
                                            </div>
                                        </div>

                                        @if (! empty($subComponents))
                                            <div class="mt-3 ps-3 border-start border-success border-2">
                                                @foreach ($subComponents as $sub)
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="small">
                                                            <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                                            <strong>{{ $sub['label'] ?? 'Unnamed' }}</strong>
                                                            <span class="badge bg-light text-dark ms-1">{{ $sub['activity_type'] ?? 'other' }}</span>
                                                        </div>
                                                        <span class="small fw-semibold text-success">{{ number_format((float) ($sub['weight'] ?? 0), 2) }}%</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold">Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="d-flex align-items-start gap-2">
                                <div class="p-2 rounded-circle bg-success bg-opacity-10">
                                    <i class="bi bi-send text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold small">Submitted</div>
                                    <div class="text-muted small">{{ $request->created_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        @if ($request->reviewed_at)
                            <div class="timeline-item">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="p-2 rounded-circle bg-{{ $request->status === 'approved' ? 'success' : 'danger' }} bg-opacity-10">
                                        <i class="bi bi-{{ $request->status === 'approved' ? 'check-circle' : 'x-circle' }} text-{{ $request->status === 'approved' ? 'success' : 'danger' }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ ucfirst($request->status) }}</div>
                                        <div class="text-muted small">{{ $request->reviewed_at->format('M d, Y h:i A') }}</div>
                                        @if ($request->reviewer)
                                            <div class="text-muted small">By: {{ $request->reviewer->first_name }} {{ $request->reviewer->last_name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($request->admin_notes && in_array($request->status, ['approved', 'rejected']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold">Admin Feedback</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-{{ $request->status === 'approved' ? 'success' : 'danger' }} mb-0">
                            <p class="mb-0">{{ $request->admin_notes }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

{{-- Styles: resources/css/chairperson/common.css --}}
