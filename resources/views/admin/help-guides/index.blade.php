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

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
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
                <div class="table-responsive">
                    <table id="guidesTable" class="table table-hover mb-0">
                        <thead class="table-success">
                            <tr>
                                <th style="width: 50px;">Order</th>
                                <th>Title</th>
                                <th>Visible To</th>
                                <th class="text-center">Attachment</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Created By</th>
                                <th class="text-center">Updated</th>
                                <th class="text-center" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-guides">
                            @foreach($guides as $guide)
                                <tr data-id="{{ $guide->id }}">
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $guide->sort_order }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $guide->title }}</div>
                                        <small class="text-muted">{{ Str::limit(strip_tags($guide->content), 60) }}</small>
                                    </td>
                                    <td>
                                        @foreach($guide->visible_role_labels as $role)
                                            <span class="badge bg-info bg-opacity-10 text-info me-1">{{ $role }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @if($guide->hasAttachment())
                                            <a href="{{ route('admin.help-guides.download', $guide) }}" class="btn btn-sm btn-outline-primary" title="Download {{ $guide->attachment_name }}">
                                                <i class="bi bi-paperclip"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm {{ $guide->is_active ? 'btn-success' : 'btn-secondary' }} toggle-status"
                                                data-id="{{ $guide->id }}"
                                                title="{{ $guide->is_active ? 'Active - Click to deactivate' : 'Inactive - Click to activate' }}">
                                            <i class="bi {{ $guide->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                            {{ $guide->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <small>{{ $guide->creator->full_name ?? 'Unknown' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">{{ $guide->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.help-guides.edit', $guide) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-guide" data-id="{{ $guide->id }}" data-title="{{ $guide->title }}" title="Delete">
                                                <i class="bi bi-trash"></i>
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
    #guidesTable tbody tr {
        transition: background-color 0.2s ease;
    }
    #guidesTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    .toggle-status {
        min-width: 90px;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable only if table exists and has data
    if ($.fn.DataTable && $('#guidesTable').length) {
        $('#guidesTable').DataTable({
            paging: true,
            pageLength: 15,
            lengthChange: false,
            searching: true,
            ordering: false, // Disable ordering since we use custom sort
            info: true,
            autoWidth: false,
            columnDefs: [
                { targets: [0, 3, 4, 5, 6, 7], orderable: false }
            ],
            language: {
                emptyTable: "No help guides found",
                search: "_INPUT_",
                searchPlaceholder: "Search guides..."
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"<"search-box"f>>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    }

    // Toggle status handler
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const button = this;
            
            try {
                const response = await fetch(`{{ url('admin/help-guides') }}/${id}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.is_active) {
                        button.classList.remove('btn-secondary');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="bi bi-check-circle"></i> Active';
                        button.title = 'Active - Click to deactivate';
                    } else {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-secondary');
                        button.innerHTML = '<i class="bi bi-x-circle"></i> Inactive';
                        button.title = 'Inactive - Click to activate';
                    }
                }
            } catch (error) {
                console.error('Error toggling status:', error);
                Swal.fire('Error', 'Failed to update status. Please try again.', 'error');
            }
        });
    });

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
