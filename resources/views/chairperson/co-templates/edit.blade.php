@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('chairperson.co-templates.index') }}">CO Templates</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('chairperson.co-templates.show', $coTemplate) }}">{{ $coTemplate->template_name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-success mb-0">
                <i class="bi bi-pencil me-2"></i>Edit CO Template
            </h2>
        </div>
    </div>

    @if($coTemplate->batchDrafts()->count() > 0)
        <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2 mt-1 fs-5"></i>
            <div>
                <strong>Warning:</strong> This template is currently being used by {{ $coTemplate->batchDrafts()->count() }} batch draft(s).
                Any changes you make will NOT affect existing batch drafts that have already applied this template.
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>Template Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('chairperson.co-templates.update', $coTemplate) }}" method="POST" id="templateForm">
                        @csrf
                        @method('PUT')

                        <!-- Template Name -->
                        <div class="mb-4">
                            <label for="template_name" class="form-label fw-semibold">
                                Template Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('template_name') is-invalid @enderror" 
                                   id="template_name" 
                                   name="template_name" 
                                   value="{{ old('template_name', $coTemplate->template_name) }}"
                                   required>
                            @error('template_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">
                                Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $coTemplate->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

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
                                @foreach($coTemplate->items as $index => $item)
                                    <div class="card mb-3 co-item-card" id="coItem{{ $index + 1 }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="mb-0">Course Outcome #{{ $index + 1 }}</h6>
                                                @if($loop->first)
                                                    <!-- Cannot remove first item -->
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCOItem({{ $index + 1 }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">CO Code <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="items[{{ $index }}][co_code]" 
                                                           value="{{ old('items.'.$index.'.co_code', $item->co_code) }}"
                                                           required>
                                                </div>
                                                <div class="col-md-9">
                                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" 
                                                              name="items[{{ $index }}][description]" 
                                                              rows="2"
                                                              required>{{ old('items.'.$index.'.description', $item->description) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle me-2"></i>Update Template
                            </button>
                            <a href="{{ route('chairperson.co-templates.show', $coTemplate) }}" class="btn btn-secondary">
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
let coItemCount = {{ $coTemplate->items->count() }};

function addCOItem() {
    coItemCount++;
    const container = document.getElementById('coItemsContainer');
    
    const itemHtml = `
        <div class="card mb-3 co-item-card" id="coItem${coItemCount}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">Course Outcome #${coItemCount}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCOItem(${coItemCount})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">CO Code <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="items[${coItemCount - 1}][co_code]" 
                               value="CO${coItemCount}"
                               required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  name="items[${coItemCount - 1}][description]" 
                                  rows="2"
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
    border-color: #ffc107;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush
@endsection
