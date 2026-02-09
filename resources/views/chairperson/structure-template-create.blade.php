@extends('layouts.app')

{{-- Styles: resources/css/chairperson/common.css --}}
{{-- Styles: resources/css/chairperson/structure-templates.css --}}

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Page Header --}}
    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-plus-circle text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Create Structure Template Request</span>
    </h1>
    <p class="text-muted mb-4">Design a custom grading structure and submit it for admin approval</p>

    {{-- Breadcrumb Navigation --}}
    @php
        $breadcrumbItems = [
            ['label' => 'Formula Requests', 'url' => route('chairperson.structureTemplates.index')],
            ['label' => 'Create New Request']
        ];
    @endphp
    <x-breadcrumbs :items="$breadcrumbItems" />

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('chairperson.structureTemplates.store') }}" id="templateRequestForm">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Template Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="template_name" class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="template_name" name="template_name" value="{{ old('template_name') }}" required maxlength="255" placeholder="e.g., CS Theory Courses, Lab-Heavy Structure">
                        <small class="text-muted">Choose a descriptive name that identifies the purpose of this template.</small>
                    </div>
                    <div class="col-12">
                        <label for="label" class="form-label fw-semibold">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="label" name="label" value="{{ old('label') }}" required maxlength="255" placeholder="Auto-generated from template name" readonly>
                        <small class="text-muted">This is automatically generated based on the template name.</small>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="1000" placeholder="Explain when and why this template should be used...">{{ old('description') }}</textarea>
                        <small class="text-muted">Optional: Provide context to help admins understand your template's purpose.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Grading Components</h5>
                    <div id="weight-indicator" class="badge bg-secondary">
                        Total: <span id="weight-total">0</span>%
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="weight-alert" class="alert alert-warning d-none mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="weight-message">Component weights must total exactly 100%</span>
                </div>
                
                <div id="components-container">
                    <!-- Components will be added dynamically -->
                </div>
                <button type="button" class="btn btn-outline-success btn-sm mt-2" id="add-component-btn">
                    <i class="bi bi-plus-circle me-1"></i>Add Main Component
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <a href="{{ route('chairperson.structureTemplates.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-success px-4" id="submit-btn" disabled>
                    <i class="bi bi-send me-1"></i>Submit Request
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Component Template -->
<template id="component-template">
    <div class="component-item card mb-3" data-component-id="">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0 fw-bold">Main Component</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-component-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small">Name</label>
                    <input type="text" class="form-control form-control-sm activity-name-input" placeholder="e.g., Quiz" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Label</label>
                    <input type="text" class="form-control form-control-sm component-label" required data-auto="true">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Weight (%)</label>
                    <input type="number" class="form-control form-control-sm component-weight" min="0" max="100" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Max Components</label>
                    <input type="number" class="form-control form-control-sm component-max-items" min="1" max="5" step="1" placeholder="1-5">
                    <small class="text-muted component-max-helper">Limit: 1-5</small>
                </div>
            </div>
            <div class="mt-2 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-success add-subcomponent-btn">
                    <i class="bi bi-plus me-1"></i>Add Sub-component
                </button>
                <small class="text-muted sub-weight-indicator d-none">
                    Sub-components: <span class="sub-weight-total fw-semibold">0</span>%
                </small>
            </div>
            <div class="subcomponents-container mt-2"></div>
        </div>
    </div>
</template>

<!-- Subcomponent Template -->
<template id="subcomponent-template">
    <div class="subcomponent-item card bg-light ms-4 mb-2" data-subcomponent-id="">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="fw-semibold">Sub-component</small>
                <button type="button" class="btn btn-sm btn-outline-danger remove-subcomponent-btn py-0 px-1">
                    <i class="bi bi-x" style="font-size: 0.875rem;"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm activity-name-input" placeholder="e.g., Quiz" required>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm component-label" placeholder="Label" required data-auto="true">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control form-control-sm component-weight" min="0" max="100" step="0.01" placeholder="Weight %" required>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control form-control-sm component-max-items" min="1" max="5" step="1" placeholder="Max 1-5">
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/chairperson/structure-template-create.js --}}
@endpush
