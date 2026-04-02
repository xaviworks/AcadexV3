@extends('layouts.app')

@section('content')
{{-- Styles and JavaScript are loaded from resources/css/admin/users.css and resources/js/pages/admin/users.js --}}

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-dark fw-bold mb-0"><i class="fas fa-users text-success me-2"></i>Users</h1>
        <button class="btn btn-success" onclick="openModal()">+ Add User</button>
    </div>

    {{-- Error/Info Messages --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof notify !== 'undefined') {
                    notify.success(@json(session('success')));
                }
            });
        </script>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Warning Message --}}
    @if (isset($hasDisabledUntilColumn) && ! $hasDisabledUntilColumn)
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            The <code>disabled_until</code> column is missing from the <code>users</code> table. Please run the latest migrations to restore disable-account behavior.
        </div>
    @endif

    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        These users have higher access. Add one at your own discretion.
    </div>

    {{-- Users Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="usersTable" class="table table-bordered table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">2FA</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="user-info">
                                        @php
                                            $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                                            $displayName = $fullName ?: $user->name;
                                        @endphp
                                        <span class="user-name">{{ $displayName }}</span>
                                        @if($fullName && $fullName !== $user->name)
                                            <small class="user-email">{{ $user->name }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="user-email">{{ $user->email }}</small>
                                </td>
                                <td class="text-center">
                                    @switch($user->role)
                                        @case(0)
                                            <span class="badge bg-secondary">Instructor</span>
                                            @break
                                        @case(1)
                                            <span class="badge bg-primary">Chairperson</span>
                                            @break
                                        @case(2)
                                            <span class="badge bg-info text-dark">Dean</span>
                                            @break
                                        @case(3)
                                            <span class="badge bg-danger">Admin</span>
                                            @break
                                        @case(4)
                                            <span class="badge bg-warning text-dark">GE Coordinator</span>
                                            @break
                                        @case(5)
                                            <span class="badge bg-dark">VPAA</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark border">Unknown</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    @if ($user->is_active)
                                        <span class="session-status-badge session-status-active">
                                            <i class="fas fa-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="session-status-badge session-status-expired">
                                            <i class="fas fa-times-circle"></i> Disabled
                                        </span>
                                        @if (isset($hasDisabledUntilColumn) && $hasDisabledUntilColumn && $user->disabled_until)
                                            @php $until = new \Carbon\Carbon($user->disabled_until); @endphp
                                            <div class="mt-1">
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                    @if ($until->year >= 9999)
                                                        Indefinitely
                                                    @else
                                                        Until: {{ $until->format('M d, Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($user->two_factor_secret)
                                        @if($user->two_factor_confirmed_at)
                                            <span class="badge bg-success" title="2FA is enabled and confirmed">
                                                <i class="fas fa-shield-alt"></i> Enabled
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark" title="2FA is enabled but not confirmed">
                                                <i class="fas fa-shield-alt"></i> Pending
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary" title="2FA is not enabled">
                                            <i class="fas fa-shield-alt"></i> Disabled
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(auth()->id() !== $user->id)
                                        <div class="action-btn-group">
                                            @if($user->is_active)
                                                <button class="action-btn btn-revoke" 
                                                        @click="modal.open('chooseDisableModal', { userId: {{ $user->id }}, userName: {{ json_encode($user->name) }} })"
                                                        title="Disable this account">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @else
                                                <button class="action-btn btn-enable" 
                                                        onclick="enableUser({{ $user->id }}, {{ json_encode($user->name) }})"
                                                        title="Enable this account">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            @if($user->two_factor_secret)
                                                <button class="action-btn btn-reset-2fa" 
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}"
                                                        data-action="reset-2fa-user"
                                                        title="Reset 2FA for this user">
                                                    <i class="fas fa-shield-halved"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="your-session-badge">
                                            <i class="fas fa-circle-check"></i> You
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            {{-- DataTables will handle empty state --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    {{-- Disable Choose Modal (one instance) --}}
    <div x-data x-show="$store.modals.active === 'chooseDisableModal'" x-cloak x-transition.opacity class="modal fade" :class="{ 'show d-block': $store.modals.active === 'chooseDisableModal' }" tabindex="-1" style="z-index: 1050;" @click.self="modal.close()">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header disable-modal-header text-white">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="bi bi-person-slash me-2"></i>Disable User Account
                        </h5>
                        <small class="opacity-75">Temporarily restrict account access</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" @click="modal.close()" aria-label="Close"></button>
                </div>
                <form id="chooseDisableForm" method="POST" :action="`/admin/users/${$store.modals.data.userId}/disable`">
                    @csrf
                    <div class="modal-body disable-modal-body">
                        <div class="disable-modal-intro">
                            <div class="d-flex align-items-start gap-3">
                                <div class="text-danger icon-xl">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Disabling: <span x-text="$store.modals.data.userName" class="text-primary"></span></h6>
                                    <p class="mb-0 small text-muted">
                                        This will prevent the user from logging in or accessing the system for the selected duration. 
                                        All active sessions will be terminated immediately.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold text-dark mb-3">
                                <i class="bi bi-clock-history me-2"></i>Choose Duration
                            </label>
                        </div>

                        <div class="d-flex gap-3 disable-options-row flex-wrap">
                            <div class="flex-fill">
                                <div class="disable-option-card active" data-value="1_week" role="button" tabindex="0">
                                    <span class="check-mark"><i class="bi bi-check-lg"></i></span>
                                    <div class="icon bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-calendar-week-fill"></i>
                                    </div>
                                    <div class="meta">
                                        <div class="fw-semibold">1 Week</div>
                                        <small>Disable for 7 days</small>
                                    </div>
                                    <input type="radio" class="d-none" name="duration_option" value="1_week" checked>
                                </div>
                            </div>

                            <div class="flex-fill">
                                <div class="disable-option-card" data-value="1_month" role="button" tabindex="0">
                                    <span class="check-mark"><i class="bi bi-check-lg"></i></span>
                                    <div class="icon bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-calendar-month-fill"></i>
                                    </div>
                                    <div class="meta">
                                        <div class="fw-semibold">1 Month</div>
                                        <small>Disable for ~30 days</small>
                                    </div>
                                    <input type="radio" class="d-none" name="duration_option" value="1_month">
                                </div>
                            </div>

                            <div class="flex-fill">
                                <div class="disable-option-card" data-value="indefinite" role="button" tabindex="0">
                                    <span class="check-mark"><i class="bi bi-check-lg"></i></span>
                                    <div class="icon bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-slash-circle-fill"></i>
                                    </div>
                                    <div class="meta">
                                        <div class="fw-semibold">Indefinite</div>
                                        <small>Until manually re-enabled</small>
                                    </div>
                                    <input type="radio" class="d-none" name="duration_option" value="indefinite">
                                </div>
                            </div>

                            <div class="flex-fill">
                                <div class="disable-option-card" data-value="custom" role="button" tabindex="0">
                                    <span class="check-mark"><i class="bi bi-check-lg"></i></span>
                                    <div class="icon bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-clock-fill"></i>
                                    </div>
                                    <div class="meta">
                                        <div class="fw-semibold">Custom</div>
                                        <small>Pick exact date &amp; time</small>
                                    </div>
                                    <input type="radio" class="d-none" name="duration_option" value="custom">
                                </div>
                            </div>
                        </div>

                        <div id="customDatetimeWrapper" class="custom-datetime-wrapper">
                            <div class="bg-white p-3 rounded-3 border">
                                <label for="customDisableDatetime" class="form-label fw-semibold small mb-2">
                                    <i class="bi bi-calendar-event me-1"></i>Select Re-enable Date & Time
                                </label>
                                <input 
                                    type="datetime-local" 
                                    id="customDisableDatetime" 
                                    name="custom_disable_datetime" 
                                    class="form-control" 
                                    min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                                >
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>Account will be automatically re-enabled at this time
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer disable-modal-footer">
                        <input type="hidden" name="duration" id="chooseDisableDuration" value="1_week">
                        <button type="submit" class="btn btn-danger px-4" id="disableAccountSubmitBtn">
                            <i class="bi bi-person-slash me-2"></i>Disable Account
                        </button>
                        <button type="button" class="btn btn-secondary px-4" @click="modal.close()">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add User Modal --}}
<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="courseModalLabel">Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="user-form" action="{{ route('admin.storeVerifiedUser') }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- Name Section --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Juan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="(optional)">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" placeholder="Dela Cruz" required>
                        </div>
                    </div>

                    {{-- Email Username --}}
                    <div class="mt-3">
                        <label class="form-label">Email Username</label>
                        <div class="input-group">
                            <input type="text" name="email" class="form-control" placeholder="jdelacruz" required
                                pattern="^[^@]+$" title="Do not include '@' or domain — just the username.">
                            <span class="input-group-text">@brokenshire.edu.ph</span>
                        </div>
                        <div id="email-warning" class="text-danger small mt-1 d-none">
                            Please enter only your username — do not include '@' or email domain.
                        </div>
                    </div>

                    {{-- User Role --}}
                    <div class="mt-3">
                        <label class="form-label">User Role</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Choose Role --</option>
                            <option value="1">Chairperson</option>
                            <option value="2">Dean</option>
                            <option value="3">Admin</option>
                            <option value="5">VPAA</option>
                        </select>
                    </div>

                    {{-- Department --}}
                    <div class="mt-3" id="department-wrapper">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select" required>
                            <option value="">-- Choose Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->department_description }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Course --}}
                    <div class="mt-3" id="course-wrapper">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select">
                            <option value="">-- Choose Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->course_description }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Password --}}
                    <div class="mt-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="Min. 8 characters" autocomplete="new-password"
                                   oninput="checkPassword(this.value)" id="password">
                            <button type="button" id="togglePassword" 
                                    class="btn btn-outline-secondary border-start-0 text-dark card-header-light"
                                    aria-label="Toggle password visibility"
                                    aria-pressed="false"
                                    title="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        
                        {{-- Password Requirements --}}
                        <div id="password-requirements" class="mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div id="circle-length" class="password-indicator bg-secondary"></div>
                                        <small>Minimum 8 characters</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div id="circle-case" class="password-indicator bg-secondary"></div>
                                        <small>Upper & lowercase</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div id="circle-number" class="password-indicator bg-secondary"></div>
                                        <small>At least 1 number</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div id="circle-special" class="password-indicator bg-secondary"></div>
                                        <small>Special character</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mt-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" required id="password_confirmation">
                            <button type="button" id="togglePasswordConfirmation" 
                                    class="btn btn-outline-secondary border-start-0 text-dark card-header-light"
                                    aria-label="Toggle confirm password visibility"
                                    aria-pressed="false"
                                    title="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="openConfirmModal()">Add User</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Your Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="confirm-form" action="{{ route('admin.confirmUserCreationWithPassword') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>To make sure this is you, you will need to re-enter your password for safety purposes.</p>
                    <div class="mt-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="confirm_password" class="form-control" required 
                               placeholder="Re-enter your password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirm</button>
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset 2FA Modal --}}
<div class="modal fade" id="reset2FAUserModal" tabindex="-1" aria-labelledby="reset2FAUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="reset2FAUserModalLabel">
                    <i class="fas fa-shield-halved me-2"></i>Reset Two-Factor Authentication
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reset-2fa-user-form" action="{{ route('admin.sessions.reset2fa') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" id="reset-2fa-user-user-id">
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                        <i class="fas fa-exclamation-triangle mt-1"></i>
                        <div>
                            <strong>Warning:</strong> You are about to reset 2FA for <strong id="reset-2fa-user-user-name"></strong>.
                        </div>
                    </div>
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        This will remove their two-factor authentication settings. The user will need to set up 2FA again if they want to re-enable it.
                    </p>
                    <div class="mt-3">
                        <label class="form-label fw-bold">Confirm Your Password</label>
                        <input type="password" name="password" id="reset-2fa-user-password" class="form-control" required 
                               placeholder="Enter your admin password" autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-shield-halved me-1"></i>Reset 2FA
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DataTables initialization is handled by resources/js/pages/admin/users.js --}}
@endsection
