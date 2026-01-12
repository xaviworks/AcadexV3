@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('chairperson.co-templates.index') }}">CO Templates</a></li>
                    <li class="breadcrumb-item active">Create Template</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-success mb-0">
                <i class="bi bi-plus-circle me-2"></i>Create CO Template
            </h2>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>Template Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('chairperson.co-templates.store') }}" method="POST" id="templateForm">
                        @csrf

                        <!-- Template Name -->
                        <div class="mb-4">
                            <label for="template_name" class="form-label fw-semibold">
                                Template Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('template_name') is-invalid @enderror" 
                                   id="template_name" 
                                   name="template_name" 
                                   value="{{ old('template_name') }}"
                                   placeholder="e.g., Standard 3 COs - BSIT First Year"
                                   required>
                            @error('template_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Choose a descriptive name for this template</small>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">
                                Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Optional: Describe when this template should be used">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror>
                        </div>

                        <!-- Universal Template Option (for GE Coordinator) -->
                        @if(Auth::user()->role === 4)
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_universal" 
                                           name="is_universal"
                                           value="1"
                                           {{ old('is_universal') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_universal">
                                        Universal Template (Available to all courses)
                                    </label>
                                </div>
                                <small class="text-muted">Enable this if the template should be available to all departments</small>
                            </div>
                        @endif

                        <hr class="my-4">

                        <!-- CO Items Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-semibold mb-0">
                                    Course Outcome Items <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addCOItem()">
                                    <i class="bi bi-plus-circle me-1"></i>Add CO Item
                                </button>
                            </div>
                            
                            <div id="coItemsContainer">
                                <!-- CO items will be added here -->
                            </div>

                            <div class="alert alert-info d-flex align-items-start mt-3" role="alert">
                                <i class="bi bi-info-circle me-2 mt-1"></i>
                                <div>
                                    <strong>Tip:</strong> Add at least one CO item. Common templates include 3-6 course outcomes.
                                    Each outcome should describe what students will achieve.
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-2"></i>Create Template
                            </button>
                            <a href="{{ route('chairperson.co-templates.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let coItemCount = 0;

// Add initial 3 CO items on page load
document.addEventListener('DOMContentLoaded', function() {
    for (let i = 1; i <= 3; i++) {
        addCOItem();
    }
});

function addCOItem() {
    coItemCount++;
    const container = document.getElementById('coItemsContainer');
    
    const itemHtml = `
        <div class="card mb-3 co-item-card" id="coItem${coItemCount}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">Course Outcome #${coItemCount}</h6>
                    ${coItemCount > 1 ? `
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCOItem(${coItemCount})">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : ''}
                </div>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">CO Code <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="items[${coItemCount - 1}][co_code]" 
                               value="CO${coItemCount}"
                               placeholder="CO${coItemCount}"
                               required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  name="items[${coItemCount - 1}][description]" 
                                  rows="2"
                                  placeholder="Describe what students will achieve with this outcome"
                                  required></textarea>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removeCOItem(id) {
    const item = document.getElementById(`coItem${id}`);
    if (item) {
        item.remove();
    }
    
    // Renumber remaining items
    const items = document.querySelectorAll('.co-item-card');
    items.forEach((item, index) => {
        const heading = item.querySelector('h6');
        if (heading) {
            heading.textContent = `Course Outcome #${index + 1}`;
        }
    });
}

// Form validation
document.getElementById('templateForm').addEventListener('submit', function(e) {
    const coItems = document.querySelectorAll('.co-item-card');
    if (coItems.length === 0) {
        e.preventDefault();
        alert('Please add at least one CO item.');
        return false;
    }
});
</script>

<style>
.co-item-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.co-item-card:hover {
    border-color: #198754;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush
@endsection
