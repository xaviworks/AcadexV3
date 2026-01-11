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
    
    /* Compact step form styling */
    #editStepsList .step-item .card-header {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    #editStepsList .step-item .card-body {
        padding: 0.75rem;
    }
    
    #editStepsList .step-item .form-label.small {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    
    #editStepsList .step-item .form-control-sm,
    #editStepsList .step-item .form-select-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }
    
    #editStepsList .step-item .input-group-sm .btn {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }
    
    #editStepsList .step-item small.text-muted {
        font-size: 0.75rem;
    }
    
    /* Compact Tutorial Builder Workflow card */
    .tutorial-info-card,
    .card[style*="linear-gradient"] {
        margin-bottom: 0.5rem !important;
    }
    
    .card[style*="linear-gradient"] .card-body {
        padding: 0.5rem 0.75rem !important;
    }
    
    .card[style*="linear-gradient"] h6 {
        font-size: 0.85rem;
        margin-bottom: 0.25rem !important;
    }
    
    .card[style*="linear-gradient"] ul {
        font-size: 0.8rem;
        padding-left: 1rem !important;
    }
    
    .card[style*="linear-gradient"] ul li {
        margin-bottom: 0.1rem !important;
        padding-bottom: 0 !important;
    }
    
    /* Table styling - less compact, more readable */
    #tutorialsTable {
        width: 100% !important;
    }
    
    #tutorialsTable thead th {
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        white-space: nowrap;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    #tutorialsTable tbody td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }
    
    #tutorialsTable tbody tr {
        border-bottom: 1px solid #e9ecef;
    }
    
    #tutorialsTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Ensure table is scrollable horizontally */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* DataTables wrapper styling */
    .dataTables_wrapper {
        width: 100%;
    }
    
    .dataTables_wrapper .dataTables_filter {
        display: none; /* Hide default search box */
    }
    
    /* Role filter styling */
    #roleFilter {
        max-width: 200px;
    }
    
    /* Ensure action buttons don't wrap */
    #tutorialsTable tbody td:last-child {
        white-space: nowrap;
    }
    
    #tutorialsTable .btn-group {
        flex-wrap: nowrap;
    }
    
    /* Better spacing for table content */
    #tutorialsTable td code {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
    }
    
    #tutorialsTable td .badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.65rem;
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

    {{-- Info Card --}}
    <div class="card mb-2 border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
        <div class="card-body py-2 px-3">
            <h6 class="fw-bold text-primary mb-1 small">
                <i class="bi bi-lightbulb me-1"></i>Tutorial Builder Workflow
            </h6>
            <ul class="mb-0 small text-muted ps-3" style="line-height: 1.4; margin-bottom: 0 !important;">
                <li class="mb-0"><strong>Create:</strong> Add new tutorial with basic metadata (role, page, title) using the modal form</li>
                <li class="mb-0"><strong>Edit:</strong> Edit tutorial metadata using the modal form</li>
                <li class="mb-0"><strong>Manage Steps:</strong> Use the full edit page to manage all tutorial steps</li>
                <li class="mb-0"><strong>Test:</strong> Preview tutorial on target page before activating</li>
            </ul>
        </div>
    </div>

    {{-- Action Buttons Legend removed as per request --}}

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Role Filter --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="roleFilter" class="form-label small fw-semibold">Filter by Role:</label>
                    <select id="roleFilter" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="vpaa">VPAA</option>
                        <option value="dean">Dean</option>
                        <option value="chairperson">Chairperson</option>
                        <option value="instructor">Instructor</option>
                    </select>
                </div>
            </div>
            
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-hover table-striped" id="tutorialsTable" style="min-width: 1200px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Role</th>
                            <th>Page Identifier</th>
                            <th>Steps</th>
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
                                <!-- Status column removed as per request -->
                                <td>
                                    <span class="badge bg-dark">{{ $tutorial->priority }}</span>
                                </td>
                                <td>
                                    <span class="text-nowrap">{{ $tutorial->creator->first_name }} {{ $tutorial->creator->last_name }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group" style="white-space: nowrap;">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary edit-tutorial-btn"
                                                data-id="{{ $tutorial->id }}"
                                                data-role="{{ $tutorial->role }}"
                                                data-page-identifier="{{ $tutorial->page_identifier }}"
                                                data-title="{{ $tutorial->title }}"
                                                data-description="{{ $tutorial->description }}"
                                                data-priority="{{ $tutorial->priority }}"
                                                
                                                title="Edit Tutorial Details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTutorialModal">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        

                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-info" 
                                                onclick="confirmDuplicate({{ $tutorial->id }}, {{ json_encode($tutorial->title) }})"
                                                title="Duplicate">
                                            <i class="bi bi-files"></i>
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete({{ $tutorial->id }}, {{ json_encode($tutorial->title) }})"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    

                                        @csrf
                                    </form>
                                    
                                    <form id="duplicate-form-{{ $tutorial->id }}" 
                                          action="{{ route('admin.tutorials.duplicate', $tutorial) }}" 
                                          method="POST" 
                                          class="d-none">
                                        @csrf
                                    </form>
                                    
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
                                <td colspan="8" class="text-center py-5">
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
                <input type="hidden" name="_modal" value="create">
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

{{-- Edit Tutorial Details Modal --}}
<div class="modal fade" id="editTutorialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Tutorial Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="editTutorialForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <div class="modal-body">
                    <p class="text-muted small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Edit all tutorial details, including steps, in one place.
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

                    {{-- Tutorial Steps Section --}}
                    <hr class="my-4">
                    <h5 class="mb-3"><i class="bi bi-list-ol me-2"></i>Tutorial Steps</h5>
                    <div id="editStepsList">
                        <button type="button" class="btn btn-sm btn-success my-2" id="addEditStepBtn" onclick="addStep()"><i class="bi bi-plus"></i> Add Step</button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Tutorial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Tutorial Step Templates (outside modal to prevent deletion) --}}
@php $positions = ['top','bottom','left','right']; @endphp
@if(isset($tutorials))
    @foreach($tutorials as $tutorial)
        <template id="steps-template-{{ $tutorial->id }}" style="display: none;">
            <div class="d-flex flex-column gap-2">
            @foreach($tutorial->steps as $index => $step)
                <div class="card step-item" x-data="{ open: false }" data-step-index="{{ $index }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Step {{ $index + 1 }}: {{ $step->title ?? 'Untitled' }}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="open = !open">
                            <span x-show="!open"><i class="bi bi-chevron-down"></i></span>
                            <span x-show="open"><i class="bi bi-chevron-up"></i></span>
                        </button>
                    </div>
                    <div class="card-body" x-show="open" x-transition>
                        @include('admin.tutorials._step-form-fields', ['index' => $index, 'step' => $step, 'positions' => $positions])
                    </div>
                </div>
            @endforeach
            </div>
        </template>
    @endforeach
@endif

@push('scripts')
<script>


// Duplicate confirmation
function confirmDuplicate(tutorialId, tutorialTitle) {
    bootbox.confirm({
        message: `<div class="text-center">
                    <i class="bi bi-files text-info" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Duplicate Tutorial</h5>
                    <p class="text-muted">Are you sure you want to duplicate "<strong>${tutorialTitle}</strong>"?</p>
                    <p class="text-muted small">This will create a copy of the tutorial with all its steps.</p>
                  </div>`,
        buttons: {
            confirm: {
                label: '<i class="bi bi-files me-1"></i> Duplicate',
                className: 'btn-info'
            },
            cancel: {
                label: '<i class="bi bi-x-lg me-1"></i> Cancel',
                className: 'btn-secondary'
            }
        },
        centerVertical: true,
        callback: function(result) {
            if (result) {
                document.getElementById('duplicate-form-' + tutorialId).submit();
            }
        }
    });
}

// Delete confirmation
function confirmDelete(tutorialId, tutorialTitle) {
    bootbox.confirm({
        message: `<div class="text-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Delete Tutorial</h5>
                    <p class="text-muted">Are you sure you want to delete "<strong>${tutorialTitle}</strong>"?</p>
                    <p class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>This action cannot be undone. All tutorial steps will be permanently deleted.</p>
                  </div>`,
        buttons: {
            confirm: {
                label: '<i class="bi bi-trash me-1"></i> Delete',
                className: 'btn-danger'
            },
            cancel: {
                label: '<i class="bi bi-x-lg me-1"></i> Cancel',
                className: 'btn-secondary'
            }
        },
        centerVertical: true,
        callback: function(result) {
            if (result) {
                document.getElementById('delete-form-' + tutorialId).submit();
            }
        }
    });
}

function addStep() {
    const stepsList = document.getElementById('editStepsList');
    const newStepIndex = stepsList.querySelectorAll('.step-item').length;

    const newStepTemplate = `
        <div class="card step-item" x-data="{ open: true }" data-step-index="${newStepIndex}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Step ${newStepIndex + 1}: Untitled</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="open = !open">
                    <span x-show="!open"><i class="bi bi-chevron-down"></i></span>
                    <span x-show="open"><i class="bi bi-chevron-up"></i></span>
                </button>
            </div>
            <div class="card-body py-2" x-show="open" x-transition>
                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <label for="step-title-${newStepIndex}" class="form-label small fw-semibold mb-1">Step Title *</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               id="step-title-${newStepIndex}" 
                               name="steps[${newStepIndex}][title]" 
                               placeholder="e.g., Welcome to the Dashboard"
                               required>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <label for="step-content-${newStepIndex}" class="form-label small fw-semibold mb-1">Step Content *</label>
                        <textarea class="form-control form-control-sm" 
                                  id="step-content-${newStepIndex}" 
                                  name="steps[${newStepIndex}][content]" 
                                  rows="2" 
                                  placeholder="Describe what the user should learn in this step..."
                                  required></textarea>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <label for="step-target-${newStepIndex}" class="form-label small fw-semibold mb-1">
                            Target Selector * 
                            <i class="bi bi-question-circle" 
                               data-bs-toggle="tooltip" 
                               title="CSS selector for the element to highlight (e.g., #myButton, .card-header)"></i>
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" 
                                   class="form-control target-selector-input" 
                                   id="step-target-${newStepIndex}" 
                                   name="steps[${newStepIndex}][target_selector]" 
                                   placeholder=".element-class, #element-id"
                                   required>
                            <button type="button" class="btn btn-outline-secondary btn-sm pick-element-btn" data-step-index="${newStepIndex}">
                                <i class="bi bi-cursor"></i> Pick
                            </button>
                        </div>
                        <small class="text-muted small">Use comma-separated selectors for fallbacks</small>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label for="step-position-${newStepIndex}" class="form-label small fw-semibold mb-1">Tooltip Position</label>
                        <select name="steps[${newStepIndex}][position]" class="form-select form-select-sm" id="step-position-${newStepIndex}">
                            <option value="top">Top</option>
                            <option value="bottom" selected>Bottom</option>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">&nbsp;</label>
                        <div class="form-check mt-2">
                            <input type="hidden" name="steps[${newStepIndex}][is_optional]" value="0">
                            <input type="checkbox" 
                                   name="steps[${newStepIndex}][is_optional]" 
                                   class="form-check-input" 
                                   id="step-optional-${newStepIndex}"
                                   value="1">
                            <label class="form-check-label small" for="step-optional-${newStepIndex}">Optional</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold mb-1">&nbsp;</label>
                        <div class="form-check mt-2">
                            <input type="hidden" name="steps[${newStepIndex}][requires_data]" value="0">
                            <input type="checkbox" 
                                   name="steps[${newStepIndex}][requires_data]" 
                                   class="form-check-input" 
                                   id="step-requires-data-${newStepIndex}"
                                   value="1">
                            <label class="form-check-label small" for="step-requires-data-${newStepIndex}">Requires Data</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Find the wrapper div or create it
    let wrapper = stepsList.querySelector('.d-flex.flex-column.gap-2');
    if (!wrapper) {
        // Create wrapper if it doesn't exist
        wrapper = document.createElement('div');
        wrapper.className = 'd-flex flex-column gap-2';
        // Insert wrapper before the "Add Step" button
        const addButton = document.getElementById('addEditStepBtn');
        if (addButton) {
            addButton.insertAdjacentElement('beforebegin', wrapper);
        } else {
            stepsList.appendChild(wrapper);
        }
    }
    
    // Add new step to wrapper
    wrapper.insertAdjacentHTML('beforeend', newStepTemplate);

    // Reinitialize Alpine.js for the new step
    if (typeof Alpine !== 'undefined') {
        // Get the last step item we just added
        const newStepElement = wrapper.querySelector(`.step-item[data-step-index="${newStepIndex}"]`);
        if (newStepElement) {
            Alpine.initTree(newStepElement);
        }
    }

    // Reinitialize tooltips for the new step
    const tooltips = stepsList.querySelectorAll(`[data-step-index="${newStepIndex}"] [data-bs-toggle="tooltip"]`);
    tooltips.forEach(el => new bootstrap.Tooltip(el));

    // Update step title when input changes
    const titleInput = document.getElementById(`step-title-${newStepIndex}`);
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            const header = this.closest('.step-item').querySelector('.card-header .fw-semibold');
            if (header) {
                const stepNum = newStepIndex + 1;
                header.textContent = `Step ${stepNum}: ${this.value || 'Untitled'}`;
            }
        });
    }
}

// Initialize DataTables and Tooltips
$(document).ready(function() {
    // Show success/error messages via Alpine notifications
    @if(session('success'))
        if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
            Alpine.store('notifications').success("{{ session('success') }}");
        } else if (typeof window.notify !== 'undefined') {
            window.notify.success("{{ session('success') }}");
        }
    @endif

    @if(session('error'))
        if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
            Alpine.store('notifications').error("{{ session('error') }}");
        } else if (typeof window.notify !== 'undefined') {
            window.notify.error("{{ session('error') }}");
        }
    @endif

    // Handle form validation errors - reopen modal if errors exist
    @if($errors->any() && !session('success'))
        @if(old('_modal') === 'create')
            const createModal = new bootstrap.Modal(document.getElementById('createTutorialModal'));
            createModal.show();
        @elseif(old('_modal') === 'edit')
            const editModal = new bootstrap.Modal(document.getElementById('editTutorialModal'));
            editModal.show();
        @endif
    @endif

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize DataTables
    if (typeof $.fn.DataTable !== 'undefined') {
        const tutorialsTable = $('#tutorialsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            scrollX: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, targets: [7] }, // Disable sorting on Actions column
                { width: '80px', targets: [0] }, // ID
                { width: '200px', targets: [1] }, // Title
                { width: '100px', targets: [2] }, // Role
                { width: '150px', targets: [3] }, // Page Identifier
                { width: '100px', targets: [4] }, // Steps
                { width: '100px', targets: [5] }, // Priority
                { width: '150px', targets: [6] }, // Created By
                { width: '250px', targets: [7] }  // Actions
            ],
            language: {
                search: "",
                searchPlaceholder: "",
                lengthMenu: "Show _MENU_ tutorials per page",
                emptyTable: "No tutorials found. Create your first tutorial!",
                info: "Showing _START_ to _END_ of _TOTAL_ tutorials",
                infoEmpty: "No tutorials available",
                infoFiltered: "(filtered from _MAX_ total tutorials)"
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });

        // Role filter functionality
        $('#roleFilter').on('change', function() {
            const roleValue = this.value;
            tutorialsTable.column(2).search(roleValue).draw(); // Filter by Role column (index 2)
        });
    }

    // Handle form validation errors - reopen modal if errors exist
    @if($errors->any() && !session('success'))
        @if(old('_modal') === 'create')
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
        @elseif(old('_modal') === 'edit')
            const editModal = new bootstrap.Modal(document.getElementById('editTutorialModal'));
            editModal.show();
            
            // Repopulate edit form fields with old values
            @if(old('role'))
                document.getElementById('edit_role').value = '{{ old('role') }}';
            @endif
            @if(old('page_identifier'))
                document.getElementById('edit_page_identifier').value = '{{ old('page_identifier') }}';
            @endif
            @if(old('title'))
                document.getElementById('edit_title').value = '{{ old('title') }}';
            @endif
            @if(old('description'))
                document.getElementById('edit_description').value = '{{ old('description') }}';
            @endif
            @if(old('priority'))
                document.getElementById('edit_priority').value = '{{ old('priority') }}';
            @endif
            @if(old('is_active'))
                document.getElementById('edit_is_active').checked = true;
            @endif
        @endif
    @endif


    // Store current tutorial ID for modal events
    let currentTutorialId = null;

    // Handle Edit Tutorial Details Modal
    $('.edit-tutorial-btn').off('click').on('click', function() {
        console.log('Edit tutorial modal opened');
        const btn = $(this);
        const tutorialId = btn.data('id');
        currentTutorialId = tutorialId;
        const role = btn.data('role');
        const pageIdentifier = btn.data('page-identifier');
        const title = btn.data('title');
        const description = btn.data('description');
        const priority = btn.data('priority');
        const isActive = btn.data('is-active');

        // Set form action
        $('#editTutorialForm').attr('action', `/admin/tutorials/${tutorialId}`);

        // Populate form fields
        $('#edit_role').val(role);
        $('#edit_page_identifier').val(pageIdentifier);
        $('#edit_title').val(title);
        $('#edit_description').val(description);
        $('#edit_priority').val(priority);
        // No is_active field anymore

        // Load steps will be handled in shown.bs.modal event
    });

    // Handle modal show event to ensure content is properly initialized
    $('#editTutorialModal').on('shown.bs.modal', function() {
        if (!currentTutorialId) return;

        const stepsTemplate = document.getElementById(`steps-template-${currentTutorialId}`);
        const stepsListEl = document.getElementById('editStepsList');
        
        if (!stepsListEl) {
            console.error('editStepsList element not found');
            return;
        }
        
        // Always clear and reload content when modal is shown
        stepsListEl.innerHTML = '';
        
        if (stepsTemplate && stepsTemplate.innerHTML && stepsTemplate.innerHTML.trim()) {
            console.log('Loading template content for tutorial:', currentTutorialId);
            
            // Get the template HTML
            const templateHTML = stepsTemplate.innerHTML;
            
            // Find the "Add Step" button and insert content before it
            const addButton = document.getElementById('addEditStepBtn');
            if (addButton) {
                // Insert template content before the button
                addButton.insertAdjacentHTML('beforebegin', templateHTML);
            } else {
                // If button doesn't exist, append to container
                stepsListEl.insertAdjacentHTML('beforeend', templateHTML);
            }
            
            // Verify content was inserted
            const insertedSteps = stepsListEl.querySelectorAll('.step-item');
            console.log('Steps inserted into DOM:', insertedSteps.length);
            
            // Ensure "Add Step" button exists
            if (!document.getElementById('addEditStepBtn')) {
                const addButton = document.createElement('button');
                addButton.type = 'button';
                addButton.className = 'btn btn-sm btn-success my-2';
                addButton.id = 'addEditStepBtn';
                addButton.setAttribute('onclick', 'addStep()');
                addButton.innerHTML = '<i class="bi bi-plus"></i> Add Step';
                stepsListEl.appendChild(addButton);
            }
            
            // Reinitialize Alpine.js for the modal content - use setTimeout to ensure DOM is ready
            setTimeout(() => {
                if (typeof Alpine !== 'undefined' && Alpine.start) {
                    // Get fresh reference to steps after insertion
                    const freshSteps = stepsListEl.querySelectorAll('.step-item[x-data]');
                    console.log('Found steps with x-data:', freshSteps.length);
                    
                    // Also reinitialize the entire modal tree first
                    const modalElement = document.getElementById('editTutorialModal');
                    if (modalElement) {
                        Alpine.initTree(modalElement);
                    }
                    
                    // Manually initialize Alpine.js on each step
                    freshSteps.forEach((step, index) => {
                        // Use Alpine.initTree to process this element
                        try {
                            // Clear any existing bindings first
                            if (step._x_dataStack) {
                                step._x_dataStack = [];
                            }
                            
                            // Initialize Alpine on this step
                            Alpine.initTree(step);
                            
                            // Wait a tick for Alpine to process
                            if (Alpine.nextTick) {
                                Alpine.nextTick(() => {
                                    // Verify Alpine initialized
                                    const hasAlpine = step._x_dataStack && step._x_dataStack.length > 0;
                                    console.log(`Step ${index + 1} Alpine initialized:`, hasAlpine);
                                    
                                    // Open the first step programmatically
                                    if (index === 0 && hasAlpine) {
                                        const alpineData = step._x_dataStack[0];
                                        if (alpineData && typeof alpineData.open !== 'undefined') {
                                            alpineData.open = true;
                                            console.log('First step opened programmatically');
                                        }
                                    }
                                });
                            } else {
                                // Fallback if nextTick is not available
                                setTimeout(() => {
                                    const hasAlpine = step._x_dataStack && step._x_dataStack.length > 0;
                                    console.log(`Step ${index + 1} Alpine initialized:`, hasAlpine);
                                    
                                    if (index === 0 && hasAlpine) {
                                        const alpineData = step._x_dataStack[0];
                                        if (alpineData && typeof alpineData.open !== 'undefined') {
                                            alpineData.open = true;
                                            console.log('First step opened programmatically');
                                        }
                                    }
                                }, 10);
                            }
                        } catch (error) {
                            console.error(`Error initializing Alpine for step ${index + 1}:`, error);
                        }
                    });
                } else {
                    console.warn('Alpine.js is not available or not ready');
                }

                // Reinitialize tooltips in modal
                const tooltips = document.querySelectorAll('#editTutorialModal [data-bs-toggle="tooltip"]');
                tooltips.forEach(el => {
                    // Destroy existing tooltip if any
                    const existingTooltip = bootstrap.Tooltip.getInstance(el);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                    // Create new tooltip
                    new bootstrap.Tooltip(el);
                });
                
                // Fallback: Try clicking the first step's toggle button if it's still closed
                const firstStep = stepsListEl.querySelector('.step-item');
                if (firstStep) {
                    setTimeout(() => {
                        const cardBody = firstStep.querySelector('.card-body');
                        if (cardBody && window.getComputedStyle(cardBody).display === 'none') {
                            const toggleBtn = firstStep.querySelector('.btn-outline-secondary');
                            if (toggleBtn) {
                                toggleBtn.click();
                            }
                        }
                    }, 100);
                }
            }, 100);
        } else {
            console.log('No template found or template is empty for tutorial:', currentTutorialId);
            // Ensure "Add Step" button exists
            if (!document.getElementById('addEditStepBtn')) {
                const addButton = document.createElement('button');
                addButton.type = 'button';
                addButton.className = 'btn btn-sm btn-success my-2';
                addButton.id = 'addEditStepBtn';
                addButton.setAttribute('onclick', 'addStep()');
                addButton.innerHTML = '<i class="bi bi-plus"></i> Add Step';
                stepsListEl.appendChild(addButton);
            }
        }
    });

    // Clear tutorial ID when modal is hidden
    $('#editTutorialModal').on('hidden.bs.modal', function() {
        currentTutorialId = null;
        // Clean up Alpine.js bindings if needed
        const stepsList = document.getElementById('editStepsList');
        if (stepsList) {
            // Clear content to prevent stale Alpine.js bindings
            stepsList.innerHTML = '';
        }
    });
});
</script>
@endpush
@endsection
