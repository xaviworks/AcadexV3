@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create New Announcement</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.announcements.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      rows="5" 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Info (Blue)</option>
                                    <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Success (Green)</option>
                                    <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                                    <option value="danger" {{ old('type') === 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target Users</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="all_users" checked onchange="toggleRoleSelection()">
                                <label class="form-check-label" for="all_users">
                                    All Users
                                </label>
                            </div>
                            <div id="role-selection" class="mt-2" style="display: none;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="target_roles[]" value="0" id="role_instructor">
                                    <label class="form-check-label" for="role_instructor">Instructors</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="target_roles[]" value="1" id="role_chairperson">
                                    <label class="form-check-label" for="role_chairperson">Chairpersons</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="target_roles[]" value="2" id="role_dean">
                                    <label class="form-check-label" for="role_dean">Deans</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="target_roles[]" value="3" id="role_admin">
                                    <label class="form-check-label" for="role_admin">Admins</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="target_roles[]" value="4" id="role_ge">
                                    <label class="form-check-label" for="role_ge">GE Coordinators</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="target_roles[]" value="5" id="role_vpaa">
                                    <label class="form-check-label" for="role_vpaa">VPAA</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date (Optional)</label>
                                <input type="datetime-local" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date') }}">
                                <small class="text-muted">Leave empty to start immediately</small>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date (Optional)</label>
                                <input type="datetime-local" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="{{ old('end_date') }}">
                                <small class="text-muted">Leave empty for no expiration</small>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_dismissible" name="is_dismissible" value="1" checked>
                                <label class="form-check-label" for="is_dismissible">
                                    Allow users to dismiss this announcement
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="show_once" name="show_once" value="1">
                                <label class="form-check-label" for="show_once">
                                    Show only once per user (won't appear again after dismissal)
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Active (publish immediately)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Announcement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleRoleSelection() {
    const allUsers = document.getElementById('all_users');
    const roleSelection = document.getElementById('role-selection');
    const checkboxes = roleSelection.querySelectorAll('input[type="checkbox"]');
    
    if (allUsers.checked) {
        roleSelection.style.display = 'none';
        checkboxes.forEach(cb => cb.checked = false);
    } else {
        roleSelection.style.display = 'block';
    }
}
</script>
@endpush
@endsection
