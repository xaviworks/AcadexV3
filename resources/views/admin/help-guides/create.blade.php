@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-plus-circle-fill text-success me-2"></i>Create Help Guide</h1>
            <p class="text-muted mb-0">Add a new help guide for users</p>
        </div>
        <a href="{{ route('admin.help-guides.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-triangle me-2"></i>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.help-guides.store') }}" method="POST" enctype="multipart/form-data" id="helpGuideForm">
        @csrf
        
        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-pencil-square me-2 text-primary"></i>Guide Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   placeholder="Enter a descriptive title..."
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="12" 
                                      placeholder="Write the help guide content here. You can use plain text or basic formatting..."
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Tip: Use clear, step-by-step instructions to help users understand the feature.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attachment Section --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-paperclip me-2 text-primary"></i>Attachment (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="attachment" class="form-label fw-semibold">Upload PDF</label>
                            <input type="file" 
                                   class="form-control @error('attachment') is-invalid @enderror" 
                                   id="attachment" 
                                   name="attachment"
                                   accept=".pdf,application/pdf">
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                                Only PDF files are accepted. Max size: 10MB.
                            </div>
                        </div>
                        
                        {{-- File Preview --}}
                        <div id="filePreview" class="d-none">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-file-earmark me-2"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="clearFile()">
                                    <i class="bi bi-x"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Visibility Settings --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-eye me-2 text-primary"></i>Visibility</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Who can see this guide? <span class="text-danger">*</span></label>
                        <div class="mb-3">
                            @foreach($availableRoles as $roleId => $roleName)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="visible_roles[]" 
                                           value="{{ $roleId }}" 
                                           id="role_{{ $roleId }}"
                                           {{ in_array($roleId, old('visible_roles', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $roleId }}">
                                        {{ $roleName }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('visible_roles')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllRoles()">
                                <i class="bi bi-check-all"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllRoles()">
                                <i class="bi bi-x-lg"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Settings --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-gear me-2 text-primary"></i>Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="sort_order" class="form-label fw-semibold">Sort Order</label>
                            <input type="number" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', 0) }}" 
                                   min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Lower numbers appear first.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">Active</label>
                            <div class="form-text">Inactive guides won't be visible to users.</div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-lg me-2"></i>Create Help Guide
                            </button>
                            <a href="{{ route('admin.help-guides.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    .form-switch .form-check-input:checked {
        background-color: #198754;
    }
    #content {
        font-family: inherit;
        resize: vertical;
        min-height: 300px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File preview
    const fileInput = document.getElementById('attachment');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'The file must not exceed 10MB.'
                });
                this.value = '';
                filePreview.classList.add('d-none');
                return;
            }
            
            fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            filePreview.classList.remove('d-none');
        } else {
            filePreview.classList.add('d-none');
        }
    });
});

function clearFile() {
    const fileInput = document.getElementById('attachment');
    const filePreview = document.getElementById('filePreview');
    
    fileInput.value = '';
    filePreview.classList.add('d-none');
}

function selectAllRoles() {
    document.querySelectorAll('input[name="visible_roles[]"]').forEach(cb => cb.checked = true);
}

function clearAllRoles() {
    document.querySelectorAll('input[name="visible_roles[]"]').forEach(cb => cb.checked = false);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush
