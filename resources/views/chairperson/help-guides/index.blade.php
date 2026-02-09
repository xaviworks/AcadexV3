@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-question-circle-fill text-success me-2"></i>Manage Help Guides</h1>
            <p class="text-muted mb-0">Create and manage help guides for different user roles</p>
        </div>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createGuideModal">
            <i class="bi bi-plus-lg me-1"></i> Create Guide
        </button>
    </div>

    {{-- Success/Error Messages via Bootbox --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify?.success(@json(session('success')));
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify?.error(@json(session('error')));
            });
        </script>
    @endif

    {{-- Search Box --}}
    <div class="mb-3">
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="guidesSearch" class="form-control border-start-0 ps-0" placeholder="Search guides...">
        </div>
    </div>

    {{-- Guides Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($guides->isEmpty())
                {{-- Empty State --}}
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        <p class="mb-2">No help guides created yet.</p>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createGuideModal">
                            <i class="bi bi-plus-lg me-1"></i> Create Your First Guide
                        </button>
                    </div>
                </div>
            @else
                <div class="table-responsive guides-table-wrapper">
                    <table id="guidesTable" class="table table-bordered table-hover mb-0">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center" style="min-width: 100px;">Priority</th>
                                <th style="min-width: 250px;">Title</th>
                                <th class="text-center" style="min-width: 180px;">Visible To</th>
                                <th class="text-center" style="min-width: 120px;">Status</th>
                                <th class="text-center" style="min-width: 140px;">Updated</th>
                                <th class="text-center" style="min-width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($guides as $guide)
                                <tr>
                                    <td class="text-center">
                                        @if($guide->sort_order <= 25)
                                            <span class="priority-badge priority-high">
                                                <i class="bi bi-arrow-up-circle-fill me-1"></i>High
                                            </span>
                                        @elseif($guide->sort_order <= 75)
                                            <span class="priority-badge priority-normal">
                                                <i class="bi bi-dash-circle-fill me-1"></i>Normal
                                            </span>
                                        @else
                                            <span class="priority-badge priority-low">
                                                <i class="bi bi-arrow-down-circle-fill me-1"></i>Low
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="guide-info">
                                            <span class="guide-title">{{ $guide->title }}</span>
                                            <small class="guide-excerpt">{{ Str::limit(strip_tags($guide->content), 60) }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="role-badges">
                                            @foreach($guide->visible_role_labels as $role)
                                                <span class="role-badge bg-info text-white">{{ $role }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($guide->is_active)
                                            <span class="status-badge status-visible">
                                                <i class="bi bi-eye-fill me-1"></i>Visible
                                            </span>
                                        @else
                                            <span class="status-badge status-hidden">
                                                <i class="bi bi-eye-slash-fill me-1"></i>Hidden
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="updated-info">
                                            <span class="updated-time">{{ $guide->updated_at->diffForHumans() }}</span>
                                            <small class="updated-date">{{ $guide->updated_at->format('M d, Y') }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btn-group">
                                            <button type="button" class="action-btn btn-edit edit-guide" 
                                                    data-id="{{ $guide->id }}"
                                                    data-title="{{ $guide->title }}"
                                                    data-content="{{ $guide->content }}"
                                                    data-visible-roles="{{ json_encode($guide->visible_roles) }}"
                                                    data-sort-order="{{ $guide->sort_order }}"
                                                    data-is-active="{{ $guide->is_active ? '1' : '0' }}"
                                                    data-attachments="{{ json_encode($guide->attachments->map(fn($a) => ['id' => $a->id, 'file_name' => $a->file_name, 'file_size' => $a->human_file_size])) }}"
                                                    data-legacy-attachment="{{ $guide->attachment_name ?? '' }}"
                                                    title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button type="button" class="action-btn btn-delete delete-guide" data-id="{{ $guide->id }}" data-title="{{ $guide->title }}" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Info Card --}}
    <div class="card mt-4 shadow-sm border-0" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
        <div class="card-body py-3">
            <h6 class="fw-bold text-success mb-2"><i class="bi bi-lightbulb me-2"></i>Quick Tips</h6>
            <ul class="mb-0 small text-muted ps-3">
                <li>Guides are displayed to users based on their assigned roles.</li>
                <li>Attach PDF documents for additional resources.</li>
                <li>Use priority to control the display order for users.</li>
                <li>Hidden guides remain in the system but are not visible to users.</li>
            </ul>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Delete Help Guide</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the help guide "<strong id="deleteGuideTitle"></strong>"?</p>
                <p class="text-muted small mb-0">This action cannot be undone. Any attached files will also be deleted.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Delete Guide
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Create Guide Modal --}}
<div class="modal fade" id="createGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create Help Guide</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createGuideForm" action="{{ route('chairperson.help-guides.store') }}" method="POST" enctype="multipart/form-data" data-no-page-loader>
                @csrf
                <div class="modal-body">
                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="create_title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_title" name="title" placeholder="Enter a descriptive title...">
                    </div>
                    
                    {{-- Content --}}
                    <div class="mb-3">
                        <label for="create_content" class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control summernote-editor" id="create_content" name="content"></textarea>
                    </div>

                    <div class="row">
                        {{-- Visibility --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Visible To <span class="text-danger">*</span></label>
                            <div id="create_roles_container" class="border rounded p-3 bg-light" style="height: 220px; overflow-y: auto;">
                                @foreach($availableRoles as $roleId => $roleName)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="visible_roles[]" value="{{ $roleId }}" id="create_role_{{ $roleId }}">
                                        <label class="form-check-label" for="create_role_{{ $roleId }}">{{ $roleName }}</label>
                                    </div>
                                @endforeach
                                <div class="mt-2 pt-2 border-top">
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="toggleAllRoles('create', true)">Select All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllRoles('create', false)">Clear</button>
                                </div>
                            </div>
                        </div>

                        {{-- Settings --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Settings</label>
                            <div class="border rounded p-3 bg-light" style="height: 220px;">
                                <div class="mb-4">
                                    <label for="create_sort_order" class="form-label fw-semibold d-flex align-items-center">
                                        <i class="bi bi-sort-down me-2 text-success"></i>Display Priority
                                    </label>
                                    <select class="form-select" id="create_sort_order" name="sort_order">
                                        <option value="0">High Priority (Shows at top)</option>
                                        <option value="50" selected>Normal Priority</option>
                                        <option value="100">Low Priority (Shows at bottom)</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i>Controls where this guide appears in the list
                                    </small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="create_is_active" name="is_active" value="1" checked style="width: 3em; height: 1.5em;">
                                    <label class="form-check-label ms-2" for="create_is_active">
                                        <span class="fw-semibold d-flex align-items-center">
                                            <i class="bi bi-eye-fill me-2 text-success"></i>Published
                                        </span>
                                        <small class="text-muted d-block mt-1">Make this guide visible to selected users</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Attachments --}}
                    <div class="mb-3">
                        <label for="create_attachments" class="form-label fw-semibold">PDF Attachments (Optional)</label>
                        <input type="file" class="form-control" id="create_attachments" name="attachments[]" accept=".pdf,application/pdf" multiple>
                        <div class="form-text">
                            <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                            Only PDF files. Max 10MB per file. Maximum 10 files.
                        </div>
                        <div id="createFilesPreview" class="d-none mt-2">
                            <div class="border rounded p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold small">Selected Files:</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFiles('create')"><i class="bi bi-x"></i> Clear</button>
                                </div>
                                <div id="createFilesList"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Create Guide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Guide Modal --}}
<div class="modal fade" id="editGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Help Guide</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGuideForm" method="POST" enctype="multipart/form-data" data-no-page-loader>
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="edit_title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" placeholder="Enter a descriptive title...">
                    </div>
                    
                    {{-- Content --}}
                    <div class="mb-3">
                        <label for="edit_content" class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control summernote-editor" id="edit_content" name="content"></textarea>
                    </div>

                    <div class="row">
                        {{-- Visibility --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Visible To <span class="text-danger">*</span></label>
                            <div id="edit_roles_container" class="border rounded p-3 bg-light" style="height: 220px; overflow-y: auto;">
                                @foreach($availableRoles as $roleId => $roleName)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="visible_roles[]" value="{{ $roleId }}" id="edit_role_{{ $roleId }}">
                                        <label class="form-check-label" for="edit_role_{{ $roleId }}">{{ $roleName }}</label>
                                    </div>
                                @endforeach
                                <div class="mt-2 pt-2 border-top">
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="toggleAllRoles('edit', true)">Select All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllRoles('edit', false)">Clear</button>
                                </div>
                            </div>
                        </div>

                        {{-- Settings --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Settings</label>
                            <div class="border rounded p-3 bg-light" style="height: 220px;">
                                <div class="mb-4">
                                    <label for="edit_sort_order" class="form-label fw-semibold d-flex align-items-center">
                                        <i class="bi bi-sort-down me-2 text-success"></i>Display Priority
                                    </label>
                                    <select class="form-select" id="edit_sort_order" name="sort_order">
                                        <option value="0">High Priority (Shows at top)</option>
                                        <option value="50">Normal Priority</option>
                                        <option value="100">Low Priority (Shows at bottom)</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i>Controls where this guide appears in the list
                                    </small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1" style="width: 3em; height: 1.5em;">
                                    <label class="form-check-label ms-2" for="edit_is_active">
                                        <span class="fw-semibold d-flex align-items-center">
                                            <i class="bi bi-eye-fill me-2 text-success"></i>Published
                                        </span>
                                        <small class="text-muted d-block mt-1">Make this guide visible to selected users</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Existing Attachments --}}
                    <div class="mb-3" id="editExistingAttachments">
                        <label class="form-label fw-semibold">Current Attachments</label>
                        <div id="editAttachmentsList" class="border rounded p-2 bg-light">
                            <p class="text-muted small mb-0">No attachments</p>
                        </div>
                        <input type="hidden" name="remove_attachment" id="edit_remove_attachment" value="0">
                    </div>

                    {{-- Add More Attachments --}}
                    <div class="mb-3">
                        <label for="edit_attachments" class="form-label fw-semibold">Add More Attachments (Optional)</label>
                        <input type="file" class="form-control" id="edit_attachments" name="attachments[]" accept=".pdf,application/pdf" multiple>
                        <div class="form-text">
                            <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                            Only PDF files. Max 10MB per file. Maximum 10 files.
                        </div>
                        <div id="editFilesPreview" class="d-none mt-2">
                            <div class="border rounded p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold small">Selected Files:</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFiles('edit')"><i class="bi bi-x"></i> Clear</button>
                                </div>
                                <div id="editFilesList"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Guide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<style>
    .is-invalid:not(input):not(textarea):not(select) {
        border-color: #dc3545 !important;
    }
    
    .guides-table-wrapper {
        max-height: 580px;
        overflow-y: auto;
        overflow-x: auto;
    }

    #guidesTable thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #d1e7dd;
    }

    #guidesTable th,
    #guidesTable td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
        white-space: nowrap;
    }

    .guide-info {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }

    .guide-title {
        font-weight: 600;
        color: #212529;
        font-size: 0.875rem;
        white-space: normal;
        min-width: 200px;
    }

    .guide-excerpt {
        color: #6c757d;
        font-size: 0.75rem;
        white-space: normal;
        max-width: 250px;
    }

    .priority-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .priority-high {
        background-color: #f8d7da;
        color: #721c24;
    }

    .priority-normal {
        background-color: #e2e3e5;
        color: #41464b;
    }

    .priority-low {
        background-color: #d3d3d4;
        color: #1a1a1a;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .status-visible {
        background-color: #d1f4e0;
        color: #0f4b36;
    }

    .status-hidden {
        background-color: #e2e3e5;
        color: #41464b;
    }

    .role-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        justify-content: center;
    }

    .role-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .creator-name {
        font-size: 0.875rem;
        color: #495057;
        font-weight: 500;
    }

    .updated-info {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }

    .updated-time {
        font-weight: 600;
        color: #212529;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .updated-date {
        color: #6c757d;
        font-size: 0.75rem;
        white-space: nowrap;
    }

    .action-btn-group {
        display: inline-flex;
        gap: 0.5rem;
        align-items: center;
        justify-content: center;
    }

    .action-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        cursor: pointer;
    }

    .action-btn i {
        font-size: 0.875rem;
        margin: 0;
        line-height: 1;
    }

    .btn-edit {
        background-color: #0d6efd;
        color: white;
        text-decoration: none;
    }

    .btn-edit:hover {
        background-color: #0b5ed7;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
    }

    .btn-delete {
        background-color: #dc3545;
        color: white;
    }

    .btn-delete:hover {
        background-color: #bb2d3b;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }

    .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #dee2e6;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_filter input:focus {
        border-color: #0f4b36;
        box-shadow: 0 0 0 0.2rem rgba(15, 75, 54, 0.15);
        outline: none;
    }

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

    .note-editor.note-frame .note-editing-area .note-editable {
        min-height: 150px;
    }

    .attachment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .attachment-item:last-child {
        border-bottom: none;
    }

    .attachment-item .attachment-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        overflow: hidden;
    }

    .attachment-item .attachment-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 300px;
    }

    @media (max-width: 768px) {
        .guide-title,
        .guide-excerpt {
            font-size: 0.8125rem;
        }

        .action-btn {
            padding: 0.375rem 0.5rem;
            min-width: 32px;
            height: 32px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const summernoteConfig = {
        height: 200,
        placeholder: 'Write the help guide content here...',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ]
    };

    // Initialize DataTable
    if ($.fn.DataTable && $('#guidesTable').length) {
        const guidesTable = $('#guidesTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthChange: false,
            searching: true,
            ordering: false,
            info: true,
            autoWidth: false,
            scrollX: true,
            language: {
                emptyTable: "No help guides found"
            },
            dom: 'rt<"d-flex justify-content-between align-items-center mt-3 px-3 pb-3"ip>'
        });

        $('#guidesSearch').on('keyup', function() {
            guidesTable.search(this.value).draw();
        });
    }

    // Initialize Summernote for Create modal
    const createModal = document.getElementById('createGuideModal');
    createModal.addEventListener('shown.bs.modal', function() {
        if (!$('#create_content').hasClass('note-editor')) {
            $('#create_content').summernote({
                ...summernoteConfig,
                callbacks: {
                    onChange: function(contents) {
                        $('#create_content').val(contents);
                    }
                }
            });
        }
    });

    createModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('createGuideForm').reset();
        $('#create_content').summernote('code', '');
        clearFiles('create');
        clearValidationErrors('create');
    });

    // Initialize Summernote for Edit modal
    const editModal = document.getElementById('editGuideModal');
    let pendingEditContent = null;
    
    editModal.addEventListener('shown.bs.modal', function() {
        if (!$('#edit_content').hasClass('note-editor') && !$('#edit_content').next('.note-editor').length) {
            $('#edit_content').summernote({
                ...summernoteConfig,
                callbacks: {
                    onChange: function(contents) {
                        $('#edit_content').val(contents);
                    }
                }
            });
        }
        
        if (pendingEditContent !== null) {
            $('#edit_content').summernote('code', pendingEditContent);
            pendingEditContent = null;
        }
    });

    editModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('editGuideForm').reset();
        $('#edit_content').summernote('code', '');
        clearFiles('edit');
        clearValidationErrors('edit');
    });

    function validateGuideForm(prefix) {
        let isValid = true;
        clearValidationErrors(prefix);
        
        const titleInput = document.getElementById(`${prefix}_title`);
        if (!titleInput.value.trim()) {
            isValid = false;
            titleInput.classList.add('is-invalid');
        }
        
        const content = $(`#${prefix}_content`).summernote('code').replace(/<[^>]*>/g, '').trim();
        if (!content) {
            isValid = false;
            const noteEditor = document.getElementById(`${prefix}_content`).closest('.mb-3').querySelector('.note-editor');
            if (noteEditor) noteEditor.classList.add('is-invalid');
        }
        
        const visibleRoles = document.querySelectorAll(`#${prefix}GuideModal input[name="visible_roles[]"]:checked`);
        if (visibleRoles.length === 0) {
            isValid = false;
            const rolesContainer = document.getElementById(`${prefix}_roles_container`);
            if (rolesContainer) rolesContainer.classList.add('is-invalid');
        }
        
        if (!isValid) {
            window.notify?.warning('Please fill in all required fields');
        }
        
        return isValid;
    }
    
    function clearValidationErrors(prefix) {
        const modal = document.getElementById(`${prefix}GuideModal`);
        if (!modal) return;
        modal.querySelectorAll('.validation-error').forEach(el => el.remove());
        modal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
    
    document.getElementById('createGuideForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalContent = submitBtn.innerHTML;
        
        if (!validateGuideForm('create')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating...';
        
        // Reset button state if form submission fails (e.g., server validation errors)
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            }
        }, 5000);
    });
    
    document.getElementById('editGuideForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalContent = submitBtn.innerHTML;
        
        if (!validateGuideForm('edit')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
        
        // Reset button state if form submission fails (e.g., server validation errors)
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            }
        }, 5000);
    });

    // Delete handler
    document.querySelectorAll('.delete-guide').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const title = this.dataset.title;
            
            document.getElementById('deleteGuideTitle').textContent = title;
            document.getElementById('deleteForm').action = `{{ url('chairperson/help-guides') }}/${id}`;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });

    // Edit handler
    document.querySelectorAll('.edit-guide').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const title = this.dataset.title;
            const content = this.dataset.content;
            const visibleRoles = JSON.parse(this.dataset.visibleRoles || '[]');
            const sortOrder = parseInt(this.dataset.sortOrder) || 50;
            const isActive = this.dataset.isActive === '1';
            const attachments = JSON.parse(this.dataset.attachments || '[]');
            const legacyAttachment = this.dataset.legacyAttachment || '';

            document.getElementById('editGuideForm').action = `{{ url('chairperson/help-guides') }}/${id}`;
            document.getElementById('edit_title').value = title;
            pendingEditContent = content;

            document.querySelectorAll('#editGuideModal input[name="visible_roles[]"]').forEach(cb => {
                cb.checked = visibleRoles.includes(parseInt(cb.value));
            });

            let priority = sortOrder <= 25 ? '0' : (sortOrder <= 75 ? '50' : '100');
            document.getElementById('edit_sort_order').value = priority;
            document.getElementById('edit_is_active').checked = isActive;
            document.getElementById('edit_remove_attachment').value = '0';

            const attachmentsList = document.getElementById('editAttachmentsList');
            const existingSection = document.getElementById('editExistingAttachments');
            
            if (attachments.length > 0 || legacyAttachment) {
                let html = '';
                
                if (legacyAttachment) {
                    html += `
                        <div class="attachment-item" id="legacy-attachment-item">
                            <div class="attachment-info">
                                <i class="bi bi-file-pdf text-danger"></i>
                                <span class="attachment-name">${legacyAttachment}</span>
                                <span class="badge bg-secondary">Legacy</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLegacyAttachment()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    `;
                }
                
                attachments.forEach(att => {
                    html += `
                        <div class="attachment-item" id="attachment-item-${att.id}">
                            <div class="attachment-info">
                                <i class="bi bi-file-pdf text-danger"></i>
                                <span class="attachment-name">${att.file_name}</span>
                                <span class="badge bg-secondary">${att.file_size}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAttachmentForDeletion(${att.id})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    `;
                });
                
                attachmentsList.innerHTML = html;
                existingSection.style.display = 'block';
            } else {
                attachmentsList.innerHTML = '<p class="text-muted small mb-0">No attachments</p>';
                existingSection.style.display = 'block';
            }

            new bootstrap.Modal(document.getElementById('editGuideModal')).show();
        });
    });

    setupFileInput('create_attachments', 'createFilesPreview', 'createFilesList');
    setupFileInput('edit_attachments', 'editFilesPreview', 'editFilesList');
});

function setupFileInput(inputId, previewId, listId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const list = document.getElementById(listId);
    
    if (!input) return;
    
    input.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            const maxSize = 10 * 1024 * 1024;
            let hasError = false;
            
            for (const file of this.files) {
                if (file.size > maxSize) {
                    window.notify?.error(`File Too Large: "${file.name}" exceeds 10MB limit.`);
                    hasError = true;
                    break;
                }
            }
            
            if (hasError) {
                this.value = '';
                preview.classList.add('d-none');
                return;
            }
            
            if (this.files.length > 10) {
                window.notify?.error('Too Many Files: Maximum 10 files allowed.');
                this.value = '';
                preview.classList.add('d-none');
                return;
            }
            
            list.innerHTML = '';
            for (const file of this.files) {
                const div = document.createElement('div');
                div.className = 'd-flex align-items-center py-1 border-bottom';
                div.innerHTML = `
                    <i class="bi bi-file-pdf text-danger me-2"></i>
                    <span class="small flex-grow-1 text-truncate">${file.name}</span>
                    <span class="badge bg-secondary ms-2">${formatFileSize(file.size)}</span>
                `;
                list.appendChild(div);
            }
            
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }
    });
}

function toggleAllRoles(prefix, checked) {
    document.querySelectorAll(`#${prefix}GuideModal input[name="visible_roles[]"]`).forEach(cb => {
        cb.checked = checked;
    });
}

function clearFiles(prefix) {
    const input = document.getElementById(`${prefix}_attachments`);
    const preview = document.getElementById(`${prefix}FilesPreview`);
    
    if (input) input.value = '';
    if (preview) preview.classList.add('d-none');
}

function removeLegacyAttachment() {
    document.getElementById('edit_remove_attachment').value = '1';
    const item = document.getElementById('legacy-attachment-item');
    if (item) {
        item.style.opacity = '0.5';
        item.style.textDecoration = 'line-through';
        item.querySelector('button').disabled = true;
    }
}

let attachmentsToDelete = [];
function markAttachmentForDeletion(attachmentId) {
    attachmentsToDelete.push(attachmentId);
    
    const form = document.getElementById('editGuideForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'delete_attachments[]';
    input.value = attachmentId;
    form.appendChild(input);
    
    const item = document.getElementById(`attachment-item-${attachmentId}`);
    if (item) {
        item.style.opacity = '0.5';
        item.style.textDecoration = 'line-through';
        item.querySelector('button').disabled = true;
    }
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
