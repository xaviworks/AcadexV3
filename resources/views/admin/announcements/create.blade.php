@extends('layouts.app')

@section('content')
@push('styles')
{{-- Quill Editor CSS (external CDN) --}}
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
{{-- Announcement styles loaded via app.css import --}}
@endpush

{{-- Configuration for Alpine.js component - must be before x-data evaluation --}}
<script>
    window.announcementConfig = {
        csrfToken: '{{ csrf_token() }}',
        previewUrl: '{{ route("admin.announcements.preview") }}',
        oldTitle: @json(old('title', '')),
        oldMessage: @json(old('message', '')),
        oldTargetType: @json(old('target_type', '')),
        oldTargetId: @json(old('target_id', '')),
        users: @json($users)
    };
</script>

<div class="container py-4">
    <div class="announcement-form-card" x-data="announcementForm(window.announcementConfig)">
        <div class="announcement-header">
            <h1>
                <i class="bi bi-megaphone"></i>
                Send Announcement
            </h1>
        </div>

        <div class="announcement-body">
            @if(session('success'))
                <div class="alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.announcements.store') }}" method="POST" @submit="handleSubmit">
                @csrf

                {{-- Message Content --}}
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-chat-text me-2"></i>Message Content
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="title" 
                            name="title" 
                            placeholder="Enter announcement title..."
                            maxlength="255"
                            x-model="title"
                            value="{{ old('title') }}"
                            required
                        >
                        <div class="character-count" :class="{ 'warning': title.length > 200, 'danger': title.length > 240 }">
                            <span x-text="title.length"></span>/255 characters
                        </div>
                    </div>

                    <div class="mb-3">
                        <label id="message-label" class="form-label">Message <span class="text-danger">*</span></label>
                        <div class="editor-container">
                            <div id="message-editor" role="textbox" aria-labelledby="message-label" aria-multiline="true"></div>
                        </div>
                        <input type="hidden" name="message" id="message-input">
                        <div class="character-count" :class="{ 'warning': messageLength > 1500, 'danger': messageLength > 1800 }">
                            <span x-text="messageLength"></span>/2000 characters
                        </div>
                    </div>
                </div>

                {{-- Priority Selection --}}
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-flag me-2"></i>Priority Level
                    </div>

                    <div class="priority-options">
                        <div class="priority-option priority-low">
                            <input type="radio" name="priority" id="priority-low" value="low" {{ old('priority') == 'low' ? 'checked' : '' }}>
                            <label for="priority-low">
                                <span class="priority-icon">🔵</span>
                                <span class="priority-label">Low</span>
                            </label>
                        </div>
                        <div class="priority-option priority-normal">
                            <input type="radio" name="priority" id="priority-normal" value="normal" {{ old('priority', 'normal') == 'normal' ? 'checked' : '' }}>
                            <label for="priority-normal">
                                <span class="priority-icon">🟢</span>
                                <span class="priority-label">Normal</span>
                            </label>
                        </div>
                        <div class="priority-option priority-high">
                            <input type="radio" name="priority" id="priority-high" value="high" {{ old('priority') == 'high' ? 'checked' : '' }}>
                            <label for="priority-high">
                                <span class="priority-icon">🟡</span>
                                <span class="priority-label">High</span>
                            </label>
                        </div>
                        <div class="priority-option priority-urgent">
                            <input type="radio" name="priority" id="priority-urgent" value="urgent" {{ old('priority') == 'urgent' ? 'checked' : '' }}>
                            <label for="priority-urgent">
                                <span class="priority-icon">🔴</span>
                                <span class="priority-label">Urgent</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Target Audience --}}
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-people me-2"></i>Target Audience
                    </div>

                    <div class="target-type-options">
                        <div class="target-type-option">
                            <input type="radio" name="target_type" id="target-user" value="specific_user" 
                                {{ old('target_type') == 'specific_user' ? 'checked' : '' }}
                                @change="selectTargetType('specific_user')">
                            <label for="target-user">
                                <span class="target-icon"><i class="bi bi-person"></i></span>
                                <span class="target-label">Specific User</span>
                            </label>
                        </div>
                        <div class="target-type-option">
                            <input type="radio" name="target_type" id="target-department" value="department"
                                {{ old('target_type') == 'department' ? 'checked' : '' }}
                                @change="selectTargetType('department')">
                            <label for="target-department">
                                <span class="target-icon"><i class="bi bi-building"></i></span>
                                <span class="target-label">Department</span>
                            </label>
                        </div>
                        <div class="target-type-option">
                            <input type="radio" name="target_type" id="target-program" value="program"
                                {{ old('target_type') == 'program' ? 'checked' : '' }}
                                @change="selectTargetType('program')">
                            <label for="target-program">
                                <span class="target-icon"><i class="bi bi-mortarboard"></i></span>
                                <span class="target-label">Program</span>
                            </label>
                        </div>
                        <div class="target-type-option">
                            <input type="radio" name="target_type" id="target-role" value="role"
                                {{ old('target_type') == 'role' ? 'checked' : '' }}
                                @change="selectTargetType('role')">
                            <label for="target-role">
                                <span class="target-icon"><i class="bi bi-shield-check"></i></span>
                                <span class="target-label">Role</span>
                            </label>
                        </div>
                    </div>

                    {{-- Specific User Selector --}}
                    <div class="target-selector" :class="{ 'active': targetType === 'specific_user' }">
                        <label class="form-label">Select User <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control user-search-input" 
                            placeholder="Search by name or email..."
                            x-model="userSearch"
                            @input="filterUsers()"
                        >
                        <div class="user-select-list">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <div 
                                    class="user-select-item"
                                    :class="{ 'selected': selectedTargetId == user.id }"
                                    @click="selectUser(user.id)"
                                >
                                    <div class="user-name" x-text="user.name"></div>
                                    <div class="user-meta">
                                        <span x-text="user.email"></span> • <span x-text="user.role"></span>
                                    </div>
                                </div>
                            </template>
                            <div x-show="filteredUsers.length === 0" class="user-select-item text-muted">
                                No users found
                            </div>
                        </div>
                        <input type="hidden" name="target_id" x-model="selectedTargetId" x-show="targetType === 'specific_user'">
                    </div>

                    {{-- Department Selector --}}
                    <div class="target-selector" :class="{ 'active': targetType === 'department' }">
                        <label class="form-label">Select Department <span class="text-danger">*</span></label>
                        <select class="form-select" x-model="selectedTargetId" @change="fetchRecipientPreview()" name="target_id" x-show="targetType === 'department'">
                            <option value="">Choose a department...</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('target_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_description }} ({{ $department->department_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Program Selector --}}
                    <div class="target-selector" :class="{ 'active': targetType === 'program' }">
                        <label class="form-label">Select Program <span class="text-danger">*</span></label>
                        <select class="form-select" x-model="selectedTargetId" @change="fetchRecipientPreview()" name="target_id" x-show="targetType === 'program'">
                            <option value="">Choose a program...</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('target_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->course_description }} ({{ $program->course_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Role Selector --}}
                    <div class="target-selector" :class="{ 'active': targetType === 'role' }">
                        <label class="form-label">Select Role <span class="text-danger">*</span></label>
                        <select class="form-select" x-model="selectedTargetId" @change="fetchRecipientPreview()" name="target_id" x-show="targetType === 'role'">
                            <option value="">Choose a role...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role['value'] }}" {{ old('target_id') == $role['value'] ? 'selected' : '' }}>
                                    {{ $role['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Recipient Preview --}}
                    <div class="recipient-preview" :class="{ 'active': recipientCount > 0 }">
                        <div class="recipient-preview-header">
                            <i class="bi bi-people-fill"></i>
                            <span>Recipients:</span>
                            <span class="count" x-text="recipientCount"></span>
                        </div>
                        <div class="recipient-preview-list">
                            <template x-for="recipient in recipientPreview" :key="recipient.email">
                                <div class="recipient-preview-item">
                                    <i class="bi bi-person-circle"></i>
                                    <span x-text="recipient.name"></span>
                                    <span class="text-muted">(<span x-text="recipient.email"></span>)</span>
                                </div>
                            </template>
                        </div>
                        <div class="recipient-more" x-show="recipientCount > 5">
                            ... and <span x-text="recipientCount - 5"></span> more
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn-send" :disabled="!canSubmit || isSubmitting">
                        <template x-if="isSubmitting">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        </template>
                        <i class="bi bi-send" x-show="!isSubmitting"></i>
                        <span x-text="isSubmitting ? 'Sending...' : 'Send Announcement'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
{{-- Quill Editor JS (external CDN) --}}
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
{{-- Announcement JS loaded via app.js import --}}
<script>
    // Initialize editor when DOM is ready (config already defined above)
    document.addEventListener('DOMContentLoaded', function() {
        initAnnouncementEditor(window.announcementConfig);
    });
</script>
@endpush
@endsection
