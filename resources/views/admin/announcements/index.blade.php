@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">System Announcements</h2>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
            <i class="fas fa-plus"></i> Create Announcement
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" style="min-width: 1400px;">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Status</th>
                            <th style="min-width: 300px;">Title</th>
                            <th style="min-width: 100px;">Type</th>
                            <th style="min-width: 100px;">Priority</th>
                            <th style="min-width: 200px;">Target</th>
                            <th style="min-width: 180px;">Date Range</th>
                            <th style="min-width: 100px;">Views</th>
                            <th style="min-width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                            <tr>
                                <td>
                                    <button 
                                        class="btn btn-sm btn-{{ $announcement->is_active ? 'success' : 'secondary' }}"
                                        onclick="toggleActive({{ $announcement->id }})"
                                        id="status-{{ $announcement->id }}">
                                        {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <strong>{{ $announcement->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($announcement->message, 60) }}</small>
                                </td>
                                <td style="white-space: nowrap;">
                                    <span class="badge bg-{{ match($announcement->type) {
                                        'info' => 'info',
                                        'success' => 'success',
                                        'warning' => 'warning',
                                        'danger' => 'danger',
                                    } }}">{{ ucfirst($announcement->type) }}</span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <span class="badge bg-{{ match($announcement->priority) {
                                        'low' => 'secondary',
                                        'normal' => 'primary',
                                        'high' => 'warning',
                                        'urgent' => 'danger',
                                    } }}">{{ ucfirst($announcement->priority) }}</span>
                                </td>
                                <td>
                                    @if($announcement->target_roles === null)
                                        <span class="badge bg-dark">All Users</span>
                                    @else
                                        @foreach($announcement->target_roles as $role)
                                            <span class="badge bg-secondary">
                                                {{ match((int)$role) {
                                                    0 => 'Instructor',
                                                    1 => 'Chairperson',
                                                    2 => 'Dean',
                                                    3 => 'Admin',
                                                    4 => 'GE Coordinator',
                                                    5 => 'VPAA',
                                                    default => 'Unknown',
                                                } }}
                                            </span>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if($announcement->start_date || $announcement->end_date)
                                        <small>
                                            {{ $announcement->start_date?->format('M d, Y') ?? 'Start' }} - 
                                            {{ $announcement->end_date?->format('M d, Y') ?? 'No end' }}
                                        </small>
                                    @else
                                        <small class="text-muted">Permanent</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $announcement->viewedBy()->count() }} views
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" 
                                                class="btn btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editAnnouncementModal"
                                                onclick="loadAnnouncementForEdit({{ $announcement->id }}, {{ json_encode($announcement) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-danger" 
                                                onclick="deleteAnnouncement({{ $announcement->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No announcements yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Create Announcement Modal -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-labelledby="createAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAnnouncementModalLabel">Create New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createAnnouncementForm" action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_modal" value="create">
                <input type="hidden" name="is_dismissible" value="0">
                <input type="hidden" name="show_once" value="0">
                <input type="hidden" name="is_active" value="0">
                <div class="modal-body">
                    @include('admin.announcements.partials.form', ['announcement' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAnnouncementForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="is_dismissible" value="0">
                <input type="hidden" name="show_once" value="0">
                <input type="hidden" name="is_active" value="0">
                <div class="modal-body">
                    @include('admin.announcements.partials.form', ['announcement' => new \App\Models\Announcement(), 'isEdit' => true])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Show success/error messages and reopen modal if validation errors exist
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
            Alpine.store('notifications').success("{{ session('success') }}");
        }
    @endif

    @if(session('error'))
        if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
            Alpine.store('notifications').error("{{ session('error') }}");
        }
    @endif

    @if ($errors->any())
        @if (old('_modal') === 'create')
            new bootstrap.Modal(document.getElementById('createAnnouncementModal')).show();
        @elseif (old('_modal') === 'edit')
            new bootstrap.Modal(document.getElementById('editAnnouncementModal')).show();
        @endif
    @endif
});

function toggleActive(id) {
    bootbox.confirm({
        message: 'Toggle announcement status?',
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-primary'
            },
            cancel: {
                label: 'Cancel',
                className: 'btn-secondary'
            }
        },
        callback: function(result) {
            if (!result) return;
            
            fetch(`/admin/announcements/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const btn = document.getElementById(`status-${id}`);
                    btn.className = `btn btn-sm btn-${data.is_active ? 'success' : 'secondary'}`;
                    btn.textContent = data.is_active ? 'Active' : 'Inactive';
                    if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                        Alpine.store('notifications').success('Status updated successfully!');
                    }
                }
            })
            .catch(err => {
                if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                    Alpine.store('notifications').error('Error toggling status');
                }
            });
        }
    });
}

function deleteAnnouncement(id) {
    bootbox.confirm({
        message: '<div class="text-center"><i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i><h5>Delete this announcement?</h5><p class="text-muted">This action cannot be undone.</p></div>',
        buttons: {
            confirm: {
                label: '<i class="fas fa-trash"></i> Delete',
                className: 'btn-danger'
            },
            cancel: {
                label: '<i class="fas fa-times"></i> Cancel',
                className: 'btn-secondary'
            }
        },
        centerVertical: true,
        callback: function(result) {
            if (!result) return;
            
            const form = document.getElementById('deleteForm');
            form.action = `/admin/announcements/${id}`;
            form.submit();
        }
    });
}

function loadAnnouncementForEdit(id, announcement) {
    const form = document.getElementById('editAnnouncementForm');
    form.action = `/admin/announcements/${id}`;
    
    // Populate form fields
    document.getElementById('edit_title').value = announcement.title;
    document.getElementById('edit_message').value = announcement.message;
    document.getElementById('edit_type').value = announcement.type;
    document.getElementById('edit_priority').value = announcement.priority;
    
    // Format dates for datetime-local inputs (YYYY-MM-DDTHH:mm)
    document.getElementById('edit_start_date').value = announcement.start_date 
        ? formatDateTimeLocal(announcement.start_date) 
        : '';
    document.getElementById('edit_end_date').value = announcement.end_date 
        ? formatDateTimeLocal(announcement.end_date) 
        : '';
    
    document.getElementById('edit_is_dismissible').checked = announcement.is_dismissible;
    document.getElementById('edit_show_once').checked = announcement.show_once;
    document.getElementById('edit_is_active').checked = announcement.is_active;
    
    // Handle target roles
    if (announcement.target_roles === null) {
        document.getElementById('edit_audience_all').checked = true;
        document.getElementById('edit_audience_specific').checked = false;
        document.getElementById('edit_role-selection').style.display = 'none';
    } else {
        document.getElementById('edit_audience_all').checked = false;
        document.getElementById('edit_audience_specific').checked = true;
        document.getElementById('edit_role-selection').style.display = 'block';
        
        // Uncheck all first
        document.querySelectorAll('#edit_role-selection input[type="checkbox"]').forEach(cb => cb.checked = false);
        
        // Check selected roles
        announcement.target_roles.forEach(role => {
            const checkbox = document.getElementById(`edit_role_${getRoleName(role)}`);
            if (checkbox) checkbox.checked = true;
        });
    }
}

function getRoleName(roleId) {
    const roles = {
        0: 'instructor',
        1: 'chairperson', 
        2: 'dean',
        3: 'admin',
        4: 'ge',
        5: 'vpaa'
    };
    return roles[roleId] || '';
}

function formatDateTimeLocal(dateString) {
    if (!dateString) return '';
    
    // Parse the date string and convert to local datetime-local format (YYYY-MM-DDTHH:mm)
    const date = new Date(dateString);
    
    // Get local date components
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

function toggleRoleSelection(prefix = '') {
    const allUsersRadio = document.getElementById(prefix + 'audience_all');
    const roleSelectionId = prefix + 'role-selection';
    const roleSelection = document.getElementById(roleSelectionId);
    
    if (roleSelection) {
        roleSelection.style.display = allUsersRadio.checked ? 'none' : 'block';
        
        // If switching to All Users, uncheck all role checkboxes
        if (allUsersRadio.checked) {
            roleSelection.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        }
    }
}
</script>
@endpush
@endsection
