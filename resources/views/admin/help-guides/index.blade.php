@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-question-circle-fill text-success me-2"></i>Help Guides</h1>
            <p class="text-muted mb-0">Manage help guides for different user roles</p>
        </div>
        <a href="{{ route('admin.help-guides.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Create Guide
        </a>
    </div>

    {{-- Success/Error Messages via Bootbox --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify.success('{{ session('success') }}');
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.notify.error('{{ session('error') }}');
            });
        </script>
    @endif

    {{-- Guides Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($guides->isEmpty())
                {{-- Empty State --}}
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        <p class="mb-2">No help guides created yet.</p>
                        <a href="{{ route('admin.help-guides.create') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Create Your First Guide
                        </a>
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
                                <th class="text-center" style="min-width: 150px;">Created By</th>
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
                                        <span class="creator-name">{{ $guide->creator->full_name ?? 'Unknown' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="updated-info">
                                            <span class="updated-time">{{ $guide->updated_at->diffForHumans() }}</span>
                                            <small class="updated-date">{{ $guide->updated_at->format('M d, Y') }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btn-group">
                                            <a href="{{ route('admin.help-guides.edit', $guide) }}" class="action-btn btn-edit" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
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
    <div class="card mt-4 border-info">
        <div class="card-body">
            <h6 class="card-title text-info"><i class="bi bi-info-circle me-2"></i>About Help Guides</h6>
            <ul class="mb-0 small text-muted">
                <li>Help guides are displayed to users based on their assigned roles.</li>
                <li>You can attach documents (PDF, Word, Excel, images) to provide additional resources.</li>
                <li>Inactive guides will not be visible to users but remain in the system.</li>
                <li>Sort order determines the display sequence for users.</li>
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
@endsection

@push('styles')
<style>
    /* Help Guides Table Styles - Matching Sessions Table Design */
    
    /* Table wrapper with max height and scroll */
    .guides-table-wrapper {
        max-height: 580px; /* Approximately 10 rows */
        overflow-y: auto;
        overflow-x: auto;
    }

    /* Sticky header when scrolling */
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

    /* Guide info styling */
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

    /* Priority badges */
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

    /* Status badges */
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

    /* Role badges container */
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

    /* Creator name */
    .creator-name {
        font-size: 0.875rem;
        color: #495057;
        font-weight: 500;
    }

    /* Updated info styling */
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

    /* Action buttons */
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

    /* DataTable search box styling */
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

    /* Responsive adjustments */
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    if ($.fn.DataTable && $('#guidesTable').length) {
        $('#guidesTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthChange: false,
            searching: true,
            ordering: false,
            info: true,
            autoWidth: false,
            scrollX: true,
            language: {
                emptyTable: "No help guides found",
                search: "_INPUT_",
                searchPlaceholder: "Search guides..."
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3 px-3 pt-3"<"search-box"f>>rt<"d-flex justify-content-between align-items-center mt-3 px-3 pb-3"ip>'
        });
    }

    // Delete handler
    document.querySelectorAll('.delete-guide').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const title = this.dataset.title;
            
            document.getElementById('deleteGuideTitle').textContent = title;
            document.getElementById('deleteForm').action = `{{ url('admin/help-guides') }}/${id}`;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });
});
</script>
@endpush
