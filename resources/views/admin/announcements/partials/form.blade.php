@php
    $isEdit = isset($isEdit) && $isEdit;
    $prefix = $isEdit ? 'edit_' : '';
@endphp

{{-- Basic Information Section --}}
<div class="card border-0 bg-light mb-3">
    <div class="card-body py-3">
        <h6 class="card-title mb-3 text-success"><i class="fas fa-bullhorn me-2"></i>Announcement Details</h6>
        
        <div class="mb-3">
            <label for="{{ $prefix }}title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('title') is-invalid @enderror" 
                   id="{{ $prefix }}title" 
                   name="title" 
                   placeholder="Enter a clear, concise title"
                   value="{{ old('title', $announcement?->title ?? '') }}" 
                   required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="{{ $prefix }}message" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
            <textarea class="form-control @error('message') is-invalid @enderror" 
                      id="{{ $prefix }}message" 
                      name="message" 
                      rows="3" 
                      placeholder="Enter your announcement message..."
                      required>{{ old('message', $announcement?->message ?? '') }}</textarea>
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Keep it brief and to the point</small>
            @error('message')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select class="form-select @error('type') is-invalid @enderror" id="{{ $prefix }}type" name="type" required>
                    <option value="info" {{ old('type', $announcement?->type ?? 'info') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="success" {{ old('type', $announcement?->type ?? '') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="warning" {{ old('type', $announcement?->type ?? '') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="danger" {{ old('type', $announcement?->type ?? '') === 'danger' ? 'selected' : '' }}>Danger</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="{{ $prefix }}priority" class="form-label fw-semibold">Priority</label>
                <select class="form-select @error('priority') is-invalid @enderror" id="{{ $prefix }}priority" name="priority" required>
                    <option value="low" {{ old('priority', $announcement?->priority ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ old('priority', $announcement?->priority ?? 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ old('priority', $announcement?->priority ?? '') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ old('priority', $announcement?->priority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
                @error('priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Target Audience Section --}}
<div class="card border-0 bg-light mb-3">
    <div class="card-body py-3">
        <h6 class="card-title mb-3 text-success"><i class="fas fa-users me-2"></i>Target Audience</h6>
        
        {{-- Radio: All Users --}}
        <div class="form-check mb-2">
            <input class="form-check-input" 
                   type="radio" 
                   name="{{ $prefix }}audience_type"
                   id="{{ $prefix }}audience_all" 
                   value="all"
                   {{ ($announcement?->target_roles ?? null) === null ? 'checked' : '' }} 
                   onchange="toggleRoleSelection('{{ $prefix }}')">
            <label class="form-check-label fw-semibold" for="{{ $prefix }}audience_all">
                All Users
            </label>
            <small class="text-muted d-block ps-4">Show to everyone in the system</small>
        </div>
        
        {{-- Radio: Specific Roles --}}
        <div class="form-check mb-2">
            <input class="form-check-input" 
                   type="radio" 
                   name="{{ $prefix }}audience_type"
                   id="{{ $prefix }}audience_specific" 
                   value="specific"
                   {{ ($announcement?->target_roles ?? null) !== null ? 'checked' : '' }} 
                   onchange="toggleRoleSelection('{{ $prefix }}')">
            <label class="form-check-label fw-semibold" for="{{ $prefix }}audience_specific">
                Specific Roles
            </label>
            <small class="text-muted d-block ps-4">Choose which user roles can see this</small>
        </div>
        
        {{-- Role Selection (shown when Specific Roles is selected) --}}
        <div id="{{ $prefix }}role-selection" class="ps-4 pt-2 ms-2 border-start border-2 border-success" style="display: {{ ($announcement?->target_roles ?? null) !== null ? 'block' : 'none' }};">
            <div class="row">
                <div class="col-6 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="target_roles[]" value="0" 
                               id="{{ $prefix }}role_instructor"
                               {{ in_array(0, $announcement?->target_roles ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}role_instructor">Instructors</label>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="target_roles[]" value="1" 
                               id="{{ $prefix }}role_chairperson"
                               {{ in_array(1, $announcement?->target_roles ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}role_chairperson">Chairpersons</label>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="target_roles[]" value="2" 
                               id="{{ $prefix }}role_dean"
                               {{ in_array(2, $announcement?->target_roles ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}role_dean">Deans</label>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="target_roles[]" value="3" 
                               id="{{ $prefix }}role_admin"
                               {{ in_array(3, $announcement?->target_roles ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}role_admin">Admins</label>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="target_roles[]" value="4" 
                               id="{{ $prefix }}role_ge"
                               {{ in_array(4, $announcement?->target_roles ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}role_ge">GE Coordinators</label>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="target_roles[]" value="5" 
                               id="{{ $prefix }}role_vpaa"
                               {{ in_array(5, $announcement?->target_roles ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $prefix }}role_vpaa">VPAA</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Schedule Section (Optional) --}}
<div class="card border-0 bg-light mb-3">
    <div class="card-body py-3">
        <h6 class="card-title mb-3 text-success">
            <i class="fas fa-calendar-alt me-2"></i>Schedule 
            <span class="badge bg-secondary fw-normal ms-1">Optional</span>
        </h6>
        
        <div class="row">
            <div class="col-md-6 mb-2">
                <label for="{{ $prefix }}start_date" class="form-label">Start Date</label>
                <input type="datetime-local" 
                       class="form-control form-control-sm @error('start_date') is-invalid @enderror" 
                       id="{{ $prefix }}start_date" 
                       name="start_date" 
                       value="{{ old('start_date', optional($announcement)->start_date?->format('Y-m-d\TH:i') ?? '') }}">
                <small class="text-muted">Leave empty = starts now</small>
                @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-2">
                <label for="{{ $prefix }}end_date" class="form-label">End Date</label>
                <input type="datetime-local" 
                       class="form-control form-control-sm @error('end_date') is-invalid @enderror" 
                       id="{{ $prefix }}end_date" 
                       name="end_date" 
                       value="{{ old('end_date', optional($announcement)->end_date?->format('Y-m-d\TH:i') ?? '') }}">
                <small class="text-muted">Leave empty = no expiration</small>
                @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Options Section --}}
<div class="card border-0 bg-light mb-0">
    <div class="card-body py-3">
        <h6 class="card-title mb-3 text-success"><i class="fas fa-cog me-2"></i>Options</h6>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           role="switch"
                           id="{{ $prefix }}is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $announcement?->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $prefix }}is_active">
                        <span class="fw-semibold d-block">Active</span>
                        <small class="text-muted">Visible to users when enabled.</small>
                    </label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           role="switch"
                           id="{{ $prefix }}is_dismissible" 
                           name="is_dismissible" 
                           value="1"
                           {{ old('is_dismissible', $announcement?->is_dismissible ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $prefix }}is_dismissible">
                        <span class="fw-semibold d-block">Allow dismissal</span>
                        <small class="text-muted">When off, logout required to dismiss.</small>
                    </label>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           role="switch"
                           id="{{ $prefix }}show_once" 
                           name="show_once" 
                           value="1"
                           {{ old('show_once', $announcement?->show_once ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $prefix }}show_once">
                        <span class="fw-semibold d-block">Show once only</span>
                        <small class="text-muted">Displays once per user, then hidden.</small>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
