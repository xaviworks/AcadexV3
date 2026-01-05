@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-pencil-fill text-success me-2"></i>Edit Help Guide</h1>
            <p class="text-muted mb-0">Update help guide details</p>
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

    <form action="{{ route('admin.help-guides.update', $helpGuide) }}" method="POST" enctype="multipart/form-data" id="helpGuideForm">
        @csrf
        @method('PUT')
        
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
                                   value="{{ old('title', $helpGuide->title) }}" 
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
                                      name="content">{{ old('content', $helpGuide->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Attachment Section --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-paperclip me-2 text-primary"></i>Attachments</h5>
                    </div>
                    <div class="card-body">
                        {{-- Existing Attachments --}}
                        @if($helpGuide->hasAttachment())
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Current Attachments</label>
                                <div class="row g-3" id="existingAttachments">
                                    {{-- Legacy single attachment --}}
                                    @if($helpGuide->attachment_path)
                                        <div class="col-12" id="legacy-attachment">
                                            <div class="alert alert-secondary d-flex align-items-center justify-content-between mb-0">
                                                <div class="d-flex align-items-center overflow-hidden me-3" style="min-width: 0;">
                                                    <i class="bi bi-file-pdf text-danger me-2 flex-shrink-0"></i>
                                                    <a href="{{ route('admin.help-guides.download', $helpGuide) }}" class="text-decoration-none text-truncate" style="max-width: 400px;" title="{{ $helpGuide->attachment_name }}">
                                                        {{ $helpGuide->attachment_name }}
                                                    </a>
                                                    <small class="text-muted ms-2 flex-shrink-0">(Legacy)</small>
                                                </div>
                                                <input type="hidden" name="remove_attachment" id="removeAttachmentInput" value="0">
                                                <button type="button" 
                                                        class="btn btn-link text-danger p-0 flex-shrink-0" 
                                                        onclick="removeLegacyAttachment()">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Multiple attachments --}}
                                    @foreach($helpGuide->attachments as $attachment)
                                        <div class="col-12" id="attachment-{{ $attachment->id }}">
                                            <div class="alert alert-secondary d-flex align-items-center justify-content-between mb-0">
                                                <div class="d-flex align-items-center overflow-hidden me-3" style="min-width: 0;">
                                                    <i class="bi bi-file-pdf text-danger me-2 flex-shrink-0"></i>
                                                    <a href="{{ route('help-guides.attachment.preview', $attachment) }}" target="_blank" class="text-decoration-none text-truncate" style="max-width: 400px;" title="{{ $attachment->file_name }}">
                                                        {{ $attachment->file_name }}
                                                    </a>
                                                    <small class="text-muted ms-2 flex-shrink-0">({{ $attachment->human_file_size }})</small>
                                                </div>
                                                <button type="button" 
                                                        class="btn btn-link text-danger p-0 flex-shrink-0" 
                                                        onclick="deleteAttachment({{ $attachment->id }})">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="attachments" class="form-label fw-semibold">
                                {{ $helpGuide->hasAttachment() ? 'Add More PDF Files' : 'Upload PDF Files' }}
                            </label>
                            <input type="file" 
                                   class="form-control @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror" 
                                   id="attachments" 
                                   name="attachments[]"
                                   accept=".pdf,application/pdf"
                                   multiple>
                            @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                                Only PDF files are accepted. Max size: 10MB per file. Maximum 10 files.
                            </div>
                        </div>
                        
                        {{-- Files Preview --}}
                        <div id="filesPreview" class="d-none">
                            <div class="border rounded p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold small">New Files to Upload:</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFiles()">
                                        <i class="bi bi-x"></i> Clear All
                                    </button>
                                </div>
                                <div id="filesList"></div>
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
                            @php
                                $oldRoles = old('visible_roles', $helpGuide->visible_roles ?? []);
                            @endphp
                            @foreach($availableRoles as $roleId => $roleName)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="visible_roles[]" 
                                           value="{{ $roleId }}" 
                                           id="role_{{ $roleId }}"
                                           {{ in_array($roleId, $oldRoles) ? 'checked' : '' }}>
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
                                   value="{{ old('sort_order', $helpGuide->sort_order) }}" 
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
                                   {{ old('is_active', $helpGuide->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">Active</label>
                            <div class="form-text">Inactive guides won't be visible to users.</div>
                        </div>
                    </div>
                </div>

                {{-- Meta Info --}}
                <div class="card shadow-sm mb-4 bg-light">
                    <div class="card-body small text-muted">
                        <div class="mb-2">
                            <strong>Created:</strong> {{ $helpGuide->created_at->format('M d, Y h:i A') }}
                            <br>by {{ $helpGuide->creator->full_name ?? 'Unknown' }}
                        </div>
                        @if($helpGuide->updater)
                            <div>
                                <strong>Last Updated:</strong> {{ $helpGuide->updated_at->format('M d, Y h:i A') }}
                                <br>by {{ $helpGuide->updater->full_name ?? 'Unknown' }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-lg me-2"></i>Update Help Guide
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
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<style>
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    .form-switch .form-check-input:checked {
        background-color: #198754;
    }
    .note-editor.note-frame {
        border-radius: 0.375rem;
    }
    .note-editor .note-toolbar {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Summernote
    $('#content').summernote({
        height: 300,
        placeholder: 'Write the help guide content here...',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ],
        callbacks: {
            onChange: function(contents) {
                $('#content').val(contents);
            }
        }
    });

    // Multiple files preview
    const filesInput = document.getElementById('attachments');
    const filesPreview = document.getElementById('filesPreview');
    const filesList = document.getElementById('filesList');
    
    filesInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            let hasError = false;
            
            // Check file sizes
            for (const file of this.files) {
                if (file.size > maxSize) {
                    window.notify.error(`File Too Large: "${file.name}" exceeds 10MB limit.`);
                    hasError = true;
                    break;
                }
            }
            
            if (hasError) {
                this.value = '';
                filesPreview.classList.add('d-none');
                return;
            }
            
            if (this.files.length > 10) {
                window.notify.error('Too Many Files: Maximum 10 files allowed.');
                this.value = '';
                filesPreview.classList.add('d-none');
                return;
            }
            
            // Build files list
            filesList.innerHTML = '';
            for (const file of this.files) {
                const div = document.createElement('div');
                div.className = 'd-flex align-items-center py-1 border-bottom';
                div.innerHTML = `
                    <i class="bi bi-file-pdf text-danger me-2"></i>
                    <span class="small flex-grow-1 text-truncate">${file.name}</span>
                    <span class="badge bg-secondary ms-2">${formatFileSize(file.size)}</span>
                `;
                filesList.appendChild(div);
            }
            
            filesPreview.classList.remove('d-none');
        } else {
            filesPreview.classList.add('d-none');
        }
    });
});

function clearFiles() {
    const filesInput = document.getElementById('attachments');
    const filesPreview = document.getElementById('filesPreview');
    
    filesInput.value = '';
    filesPreview.classList.add('d-none');
}

function deleteAttachment(attachmentId) {
    window.confirm.ask({
        title: 'Delete Attachment?',
        message: 'This action cannot be undone.',
        type: 'warning',
        confirmText: 'Yes, delete it',
        cancelText: 'Cancel'
    }).then((confirmed) => {
        if (confirmed) {
            fetch(`{{ url('admin/help-guides/attachment') }}/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById(`attachment-${attachmentId}`);
                    if (el) {
                        el.remove();
                    }
                    window.notify.success('Attachment deleted successfully.');
                } else {
                    window.notify.error(data.message || 'Failed to delete attachment.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.notify.error('Failed to delete attachment.');
            });
        }
    });
}

function removeLegacyAttachment() {
    window.confirm.ask({
        title: 'Remove Legacy Attachment?',
        message: 'This attachment will be removed when you save the form.',
        type: 'warning',
        confirmText: 'Yes, remove it',
        cancelText: 'Cancel'
    }).then((confirmed) => {
        if (confirmed) {
            document.getElementById('removeAttachmentInput').value = '1';
            const el = document.getElementById('legacy-attachment');
            if (el) {
                el.style.opacity = '0.5';
                el.querySelector('button').disabled = true;
                el.querySelector('button').textContent = 'Will be removed on save';
            }
            window.notify.info('Legacy attachment will be removed when you save.');
        }
    });
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
