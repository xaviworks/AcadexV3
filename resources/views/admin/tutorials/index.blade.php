@extends('layouts.app')

@push('styles')
<style>
    /* Tutorial Builder Custom Styles */
    .tutorial-info-card {
        transition: all 0.3s ease;
    }
    
    .tutorial-info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
    }
    
    #createTutorialModal .modal-body,
    #editMetadataModal .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    #createTutorialModal .form-label,
    #editMetadataModal .form-label {
        font-size: 0.9rem;
    }
    
    .btn-group .btn {
        transition: all 0.2s ease;
    }
    
    .btn-group .btn:hover {
        transform: scale(1.05);
        z-index: 1;
    }
    
    /* Distinguish between quick edit and full edit */
    .btn-outline-primary {
        border-width: 2px;
    }
    
    .btn-outline-primary:hover {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    /* Role Badge Colors */
    .badge.bg-admin { background-color: #dc3545 !important; }
    .badge.bg-dean { background-color: #0d6efd !important; }
    .badge.bg-vpaa { background-color: #0dcaf0 !important; }
    .badge.bg-chairperson { background-color: #ffc107 !important; color: #000 !important; }
    .badge.bg-instructor { background-color: #198754 !important; }
    
    /* Action buttons tooltips */
    .btn-group .btn {
        position: relative;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">
                <i class="bi bi-book-half"></i> Tutorial Builder
            </h2>
            <p class="text-muted mb-0">Create and manage interactive step-by-step tutorials for users</p>
        </div>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createTutorialModal">
            <i class="bi bi-plus-lg"></i> Create Tutorial
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Info Card --}}
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
        <div class="card-body py-3">
            <h6 class="fw-bold text-primary mb-2">
                <i class="bi bi-lightbulb me-2"></i>Tutorial Builder Workflow
            </h6>
            <ul class="mb-0 small text-muted ps-3">
                <li><strong>Create:</strong> Add new tutorial with basic metadata (role, page, title)</li>
                <li><strong>Quick Edit:</strong> Use <i class="bi bi-pencil-square"></i> button to modify metadata instantly</li>
                <li><strong>Full Edit:</strong> Use <i class="bi bi-list-ol"></i> button to manage tutorial steps</li>
                <li><strong>Test:</strong> Preview tutorial on target page before activating</li>
                <li><strong>Activate:</strong> Toggle status to make visible to users</li>
            </ul>
        </div>
    </div>

    {{-- Action Buttons Legend --}}
    <div class="alert alert-light border mb-4 d-flex align-items-center">
        <i class="bi bi-info-circle text-info me-3 fs-4"></i>
        <div class="flex-grow-1">
            <strong>Action Buttons:</strong>
            <span class="badge bg-light text-dark border ms-2"><i class="bi bi-pencil-square"></i> Quick Edit</span> 
            <span class="text-muted small">- Edit metadata only</span>
            <span class="ms-3 badge bg-primary"><i class="bi bi-list-ol"></i> Full Edit</span>
            <span class="text-muted small">- Manage all steps</span>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tutorialsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Role</th>
                            <th>Page Identifier</th>
                            <th>Steps</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tutorials as $tutorial)
                            <tr>
                                <td>{{ $tutorial->id }}</td>
                                <td>
                                    <strong>{{ $tutorial->title }}</strong>
                                    @if($tutorial->description)
                                        <br><small class="text-muted">{{ Str::limit($tutorial->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $tutorial->role }}">
                                        {{ ucfirst($tutorial->role) }}
                                    </span>
                                </td>
                                <td><code class="small">{{ $tutorial->page_identifier }}</code></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $tutorial->steps->count() }} steps</span>
                                </td>
                                <td>
                                    @if($tutorial->is_active)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>
                                    @else
                                        <span class="badge bg-warning"><i class="bi bi-pause-circle"></i> Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-dark">{{ $tutorial->priority }}</span>
                                </td>
                                <td>
                                    <small>{{ $tutorial->creator->first_name }} {{ $tutorial->creator->last_name }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary edit-metadata-btn"
                                                data-id="{{ $tutorial->id }}"
                                                data-role="{{ $tutorial->role }}"
                                                data-page-identifier="{{ $tutorial->page_identifier }}"
                                                data-title="{{ $tutorial->title }}"
                                                data-description="{{ $tutorial->description }}"
                                                data-priority="{{ $tutorial->priority }}"
                                                data-is-active="{{ $tutorial->is_active ? '1' : '0' }}"
                                                title="Quick Edit Metadata"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editMetadataModal">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <a href="{{ route('admin.tutorials.edit', $tutorial) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit Tutorial & Steps">
                                            <i class="bi bi-list-ol"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.tutorials.toggle-active', $tutorial) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-{{ $tutorial->is_active ? 'warning' : 'success' }}" 
                                                    title="{{ $tutorial->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi bi-{{ $tutorial->is_active ? 'pause' : 'play' }}-circle"></i>
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('admin.tutorials.duplicate', $tutorial) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-info" 
                                                    title="Duplicate">
                                                <i class="bi bi-files"></i>
                                            </button>
                                        </form>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete({{ $tutorial->id }})"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <form id="delete-form-{{ $tutorial->id }}" 
                                          action="{{ route('admin.tutorials.destroy', $tutorial) }}" 
                                          method="POST" 
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                        <h5 class="mb-2">No Tutorials Yet</h5>
                                        <p class="mb-3">Get started by creating your first interactive tutorial for users.</p>
                                        <button type="button" 
                                                class="btn btn-success btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#createTutorialModal">
                                            <i class="bi bi-plus-lg me-1"></i> Create Your First Tutorial
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create Tutorial Modal --}}
<div class="modal fade" id="createTutorialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Tutorial</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tutorials.store') }}" method="POST" id="createTutorialForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Start by creating the tutorial metadata. You'll add steps after saving.
                    </p>

                    {{-- Role Selection --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="role" class="form-label fw-semibold">
                                Target Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="vpaa">VPAA</option>
                                <option value="dean">Dean</option>
                                <option value="chairperson">Chairperson</option>
                                <option value="instructor">Instructor</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="page_identifier" class="form-label fw-semibold">
                                Page Identifier <span class="text-danger">*</span>
                                <i class="bi bi-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   title="Unique identifier for the page (e.g., admin-dashboard, dean-grades)"></i>
                            </label>
                            <input type="text" 
                                   name="page_identifier" 
                                   id="page_identifier" 
                                   class="form-control @error('page_identifier') is-invalid @enderror" 
                                   placeholder="e.g., admin-dashboard"
                                   required>
                            @error('page_identifier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Tutorial Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               class="form-control @error('title') is-invalid @enderror" 
                               placeholder="e.g., Admin Dashboard Overview"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea name="description" 
                                  id="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="2"
                                  placeholder="Brief description of what this tutorial covers"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Settings Row --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label fw-semibold">
                                Priority
                                <i class="bi bi-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   title="Higher number = higher priority. Used when multiple tutorials exist for same page."></i>
                            </label>
                            <input type="number" 
                                   name="priority" 
                                   id="priority" 
                                   class="form-control" 
                                   value="10"
                                   min="0"
                                   max="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <div class="border rounded p-3 bg-light">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           checked>
                                    <label class="form-check-label" for="is_active">
                                        <span class="text-success fw-semibold">
                                            <i class="bi bi-eye-fill me-1"></i>Active (Visible to Users)
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Create Tutorial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Metadata Modal --}}
<div class="modal fade" id="editMetadataModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Tutorial Metadata</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="editMetadataForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p class="text-muted small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Edit basic tutorial information. To modify steps, use the full "Edit Tutorial & Steps" button.
                    </p>

                    {{-- Role Selection --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_role" class="form-label fw-semibold">
                                Target Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="vpaa">VPAA</option>
                                <option value="dean">Dean</option>
                                <option value="chairperson">Chairperson</option>
                                <option value="instructor">Instructor</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_page_identifier" class="form-label fw-semibold">
                                Page Identifier <span class="text-danger">*</span>
                                <i class="bi bi-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   title="Unique identifier for the page (e.g., admin-dashboard, dean-grades)"></i>
                            </label>
                            <input type="text" 
                                   name="page_identifier" 
                                   id="edit_page_identifier" 
                                   class="form-control" 
                                   placeholder="e.g., admin-dashboard"
                                   required>
                        </div>
                    </div>

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="edit_title" class="form-label fw-semibold">
                            Tutorial Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="edit_title" 
                               class="form-control" 
                               placeholder="e.g., Admin Dashboard Overview"
                               required>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="edit_description" class="form-label fw-semibold">Description</label>
                        <textarea name="description" 
                                  id="edit_description" 
                                  class="form-control" 
                                  rows="2"
                                  placeholder="Brief description of what this tutorial covers"></textarea>
                    </div>

                    {{-- Settings Row --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_priority" class="form-label fw-semibold">
                                Priority
                                <i class="bi bi-question-circle text-muted" 
                                   data-bs-toggle="tooltip" 
                                   title="Higher number = higher priority. Used when multiple tutorials exist for same page."></i>
                            </label>
                            <input type="number" 
                                   name="priority" 
                                   id="edit_priority" 
                                   class="form-control" 
                                   value="10"
                                   min="0"
                                   max="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <div class="border rounded p-3 bg-light">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="edit_is_active" 
                                           name="is_active" 
                                           value="1">
                                    <label class="form-check-label" for="edit_is_active">
                                        <span class="text-success fw-semibold">
                                            <i class="bi bi-eye-fill me-1"></i>Active (Visible to Users)
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Metadata
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(tutorialId) {
    if (confirm('Are you sure you want to delete this tutorial? This action cannot be undone.')) {
        document.getElementById('delete-form-' + tutorialId).submit();
    }
}

// Initialize DataTables and Tooltips
$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize DataTables
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#tutorialsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [8] } // Disable sorting on Actions column
            ],
            language: {
                search: "Search tutorials:",
                lengthMenu: "Show _MENU_ tutorials per page",
                emptyTable: "No tutorials found. Create your first tutorial!"
            }
        });
    }

    // Handle form validation errors - reopen modal if errors exist
    @if($errors->any() && !session('success'))
        const createModal = new bootstrap.Modal(document.getElementById('createTutorialModal'));
        createModal.show();
        
        // Repopulate form fields with old values
        @if(old('role'))
            document.getElementById('role').value = '{{ old('role') }}';
        @endif
        @if(old('page_identifier'))
            document.getElementById('page_identifier').value = '{{ old('page_identifier') }}';
        @endif
        @if(old('title'))
            document.getElementById('title').value = '{{ old('title') }}';
        @endif
        @if(old('description'))
            document.getElementById('description').value = '{{ old('description') }}';
        @endif
        @if(old('priority'))
            document.getElementById('priority').value = '{{ old('priority') }}';
        @endif
        @if(old('is_active'))
            document.getElementById('is_active').checked = true;
        @endif
    @endif

    // Handle Edit Metadata Modal
    $('.edit-metadata-btn').on('click', function() {
        const btn = $(this);
        const tutorialId = btn.data('id');
        const role = btn.data('role');
        const pageIdentifier = btn.data('page-identifier');
        const title = btn.data('title');
        const description = btn.data('description');
        const priority = btn.data('priority');
        const isActive = btn.data('is-active');

        // Set form action
        $('#editMetadataForm').attr('action', `/admin/tutorials/${tutorialId}`);

        // Populate form fields
        $('#edit_role').val(role);
        $('#edit_page_identifier').val(pageIdentifier);
        $('#edit_title').val(title);
        $('#edit_description').val(description);
        $('#edit_priority').val(priority);
        $('#edit_is_active').prop('checked', isActive == '1');

        // Reinitialize tooltips in modal
        const tooltips = document.querySelectorAll('#editMetadataModal [data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    });
});
</script>
@endpush
@endsection
