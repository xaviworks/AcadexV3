@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/admin/users.css --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        #usersTable {
            font-size: 0.95rem;
        }
        
        #usersTable thead th {
            padding: 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
            position: relative;
        }
        
        #usersTable tbody td {
            padding: 1rem;
            vertical-align: middle;
            line-height: 1.5;
            position: relative;
        }
        
        #usersTable tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }
        
        #usersTable tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
            position: relative;
            z-index: 1;
        }
        
        #usersTable .badge {
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        #usersTable th:nth-child(3),
        #usersTable td:nth-child(3) {
            min-width: 140px;
        }
        
        #usersTable th:nth-child(4),
        #usersTable td:nth-child(4) {
            min-width: 120px;
        }
        
        #usersTable th:nth-child(5),
        #usersTable td:nth-child(5) {
            min-width: 110px;
        }
        
        #usersTable th:nth-child(6),
        #usersTable td:nth-child(6) {
            min-width: 120px;
            width: 120px;
            overflow: visible;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
    </style>
@endpush

@push('head')
    <script>
        // Make togglePasswordVisibility globally available
        window.togglePasswordVisibility = function(inputId) {
            const input = document.getElementById(inputId);
            const button = inputId === 'password' ? document.getElementById('togglePassword') : document.getElementById('togglePasswordConfirmation');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endpush

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-dark fw-bold mb-0"><i class="fas fa-users text-success me-2"></i>Users</h1>
        <button class="btn btn-success" onclick="openModal()">+ Add User</button>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
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
                                                        onclick="confirmReset2FAUser({{ $user->id }}, {{ json_encode($user->name) }})"
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

                        <div class="row disable-options-row row-cols-1 row-cols-md-2 row-cols-lg-4">
                            <div class="col mb-3">
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

                            <div class="col mb-3">
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

                            <div class="col mb-3">
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

                            <div class="col mb-3">
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
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                        <input type="hidden" name="duration" id="chooseDisableDuration" value="1_week">
                        <button type="submit" class="btn btn-danger px-4" x-data>
                            <span x-show="!$store.loading.isLoading('disableUser')">
                                <i class="bi bi-person-slash me-2"></i>Disable Account
                            </span>
                            <span x-show="$store.loading.isLoading('disableUser')" x-cloak>
                                <span class="spinner-border spinner-border-sm me-2"></span>Disabling...
                            </span>
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
                                    class="btn btn-outline-secondary border-start-0 text-dark card-header-light">
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
                                    class="btn btn-outline-secondary border-start-0 text-dark card-header-light">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="openConfirmModal()">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/admin/users.js --}}
<script>
    // Functions loaded from external JS file
</script>
@endpush

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Your Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="confirm-form" action="#" method="POST">
                @csrf
                <div class="modal-body">
                    <p>To make sure this is you, you will need to re-enter your password for safety purposes.</p>
                    <div class="mt-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="confirm_password" class="form-control" required 
                               placeholder="Re-enter your password">
                    </div>
                </div>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-shield-halved me-1"></i>Reset 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js" defer></script>
    <script defer>
        // Add this at the start of your scripts
        const swalCustomClass = {
            popup: 'swal-small',
            icon: 'text-danger',
            title: 'fs-5',
            htmlContainer: 'text-start'
        };

        function validateForm() {
            const form = document.getElementById('user-form');
            const password = form.querySelector('input[name="password"]').value;
            const confirmPassword = form.querySelector('input[name="password_confirmation"]').value;
            const firstName = form.querySelector('input[name="first_name"]').value;
            const lastName = form.querySelector('input[name="last_name"]').value;
            const email = form.querySelector('input[name="email"]').value;
            const role = form.querySelector('select[name="role"]').value;
            const departmentId = form.querySelector('select[name="department_id"]').value;
            const courseId = form.querySelector('select[name="course_id"]').value;

            // Check if required fields are filled
            const missingFields = [];
            if (!firstName) missingFields.push('First Name');
            if (!lastName) missingFields.push('Last Name');
            if (!email) missingFields.push('Email Username');
            if (!role) missingFields.push('User Role');
            
            // Only validate department and course if not Admin or VPAA
            if (role !== "3" && role !== "5") {
                if (!departmentId) missingFields.push('Department');
                // Only require course for Chairperson role
                if (role === "1" && !courseId) missingFields.push('Course');
            }
            
            if (!password) missingFields.push('Password');
            if (!confirmPassword) missingFields.push('Confirm Password');

            if (missingFields.length > 0) {
                notify.warning(`Please fill in the following fields: ${missingFields.join(', ')}`);
                return false;
            }

            // Validate email format (no @ or domain)
            if (email.includes('@')) {
                notify.error('Please enter only your username without @ or domain.');
                return false;
            }

            // Check password requirements
            const hasMinLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            if (!(hasMinLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecial)) {
                let missingRequirements = [];
                if (!hasMinLength) missingRequirements.push('Minimum 8 characters');
                if (!hasUpperCase || !hasLowerCase) missingRequirements.push('Both uppercase and lowercase letters');
                if (!hasNumber) missingRequirements.push('At least one number');
                if (!hasSpecial) missingRequirements.push('At least one special character');

                notify.error(`Password requirements not met: ${missingRequirements.join(', ')}`);
                return false;
            }

            // Check if passwords match
            if (password !== confirmPassword) {
                notify.error('Passwords do not match. Please try again.');
                return false;
            }

            return true;
        }

        function openModal() {
            modal.open('courseModal');
        }

        function closeModal() {
            modal.close('courseModal');
        }

        function openConfirmModal() {
            if (validateForm()) {
                // Check for duplicate user
                const firstName = document.querySelector('input[name="first_name"]').value;
                const lastName = document.querySelector('input[name="last_name"]').value;
                const email = document.querySelector('input[name="email"]').value;
                
                fetch(`/api/check-duplicate-name?first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&email=${encodeURIComponent(email)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            notify.error('A user with this name or email already exists in the system.');
                        } else {
                            // Proceed with confirmation modal if no duplicate
                            modal.open('confirmModal');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Proceed with confirmation modal if check fails
                        modal.open('confirmModal');
                    });
            }
        }

        function closeConfirmModal() {
            modal.close('confirmModal');
        }

        // Password validation
        function checkPassword(password) {
            const checks = {
                length: password.length >= 8,
                number: /[0-9]/.test(password),
                case: /[a-z]/.test(password) && /[A-Z]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            const update = (id, valid) => {
                const el = document.getElementById(`circle-${id}`);
                el.classList.remove('bg-danger', 'bg-success', 'bg-secondary');
                el.classList.add(valid ? 'bg-success' : 'bg-danger');
            };

            update('length', checks.length);
            update('number', checks.number);
            update('case', checks.case);
            update('special', checks.special);

            const requirementsBox = document.getElementById('password-requirements');
            const allValid = Object.values(checks).every(Boolean);
            requirementsBox.classList.toggle('d-none', allValid);
        }

        // Form submission
        document.getElementById('confirm-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
        
            fetch("{{ route('admin.confirmUserCreationWithPassword') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeConfirmModal();
                    notify.success('Password verified. Creating user...');
                    setTimeout(() => submitUserForm(), 500);
                } else {
                    notify.error(data.message || 'Invalid password. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notify.error('There was an error processing your request. Please try again.');
            });
        });

        function submitUserForm() {
            document.getElementById('user-form').submit();
        }

        // Role change handler
        document.addEventListener('DOMContentLoaded', function () {
            const roleInput = document.querySelector('select[name="role"]');
            const departmentInput = document.querySelector('select[name="department_id"]');
            const courseInput = document.querySelector('select[name="course_id"]');
            const courseWrapper = document.getElementById('course-wrapper');
            const departmentWrapper = document.getElementById('department-wrapper');

            // Initially hide course wrapper
            courseWrapper.classList.add('d-none');

            // Role change handler
            roleInput.addEventListener('change', function () {
                if (roleInput.value == "3" || roleInput.value == "5") {  // Admin or VPAA role
                    // Clear and hide department and course selections
                    departmentInput.value = "";
                    courseInput.value = "";
                    courseWrapper.classList.add('d-none');
                    departmentWrapper.classList.add('d-none');
                    
                    // Make course optional
                    courseInput.removeAttribute('required');
                } else if (roleInput.value == "2") {  // Dean role
                    // Show only department, hide course
                    departmentInput.value = "";
                    courseInput.value = "";
                    courseWrapper.classList.add('d-none');
                    departmentWrapper.classList.remove('d-none');
                    
                    // Make course optional for Dean
                    courseInput.removeAttribute('required');
                } else if (roleInput.value == "1") {  // Chairperson role
                    // Show both department and course
                    departmentInput.value = "";
                    courseInput.value = "";
                    courseWrapper.classList.remove('d-none');
                    departmentWrapper.classList.remove('d-none');
                    
                    // Make course required for chairperson
                    courseInput.setAttribute('required', 'required');
                }
                
                // Trigger department change to reset course selection
                departmentInput.dispatchEvent(new Event('change'));
            });

            // Department change handler
            departmentInput.addEventListener('change', function() {
                const deptId = this.value;
                const courseSelect = courseInput;
                
                // If role is Admin, VPAA, or Dean, keep course wrapper hidden
                if (roleInput.value == "3" || roleInput.value == "5" || roleInput.value == "2") {
                    courseWrapper.classList.add('d-none');
                    if (roleInput.value == "3" || roleInput.value == "5") {
                        departmentWrapper.classList.add('d-none');
                    }
                    return;
                }
                
                // Reset and hide course selection if no department selected
                if (!deptId) {
                    courseWrapper.classList.add('d-none');
                    courseSelect.innerHTML = '<option value="">-- Choose Course --</option>';
                    return;
                }

                // Show loading state
                courseWrapper.classList.remove('d-none');
                courseSelect.innerHTML = '<option value="">Loading...</option>';

                // Fetch courses for selected department
                fetch(`/api/department/${deptId}/courses`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            courseSelect.innerHTML = '<option value="">No courses available</option>';
                            return;
                        }

                        if (data.length === 1) {
                            // If department has only one course, auto-select it but keep the input visible
                            courseSelect.innerHTML = `<option value="${data[0].id}" selected>${data[0].name}</option>`;
                            courseWrapper.classList.remove('d-none');
                        } else {
                            // If department has multiple courses, show the dropdown
                            courseSelect.innerHTML = '<option value="">-- Choose Course --</option>';
                            data.forEach(course => {
                                courseSelect.innerHTML += `<option value="${course.id}">${course.name}</option>`;
                            });
                            courseWrapper.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        courseSelect.innerHTML = '<option value="">Error loading courses</option>';
                    });
            });

            // Add input validation for email
            const emailInput = document.querySelector('input[name="email"]');
            const emailWarning = document.getElementById('email-warning');
            
            emailInput.addEventListener('input', function() {
                if (this.value.includes('@')) {
                    emailWarning.classList.remove('d-none');
                    this.classList.add('is-invalid');
                } else {
                    emailWarning.classList.add('d-none');
                    this.classList.remove('is-invalid');
                }
            });

            // Initialize course wrapper visibility if department is pre-selected
            if (departmentInput.value) {
                departmentInput.dispatchEvent(new Event('change'));
            }

            // Password visibility toggle functionality
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('password_confirmation');
            const togglePassword = document.getElementById('togglePassword');
            const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');

            // Add click event listeners for password toggles
            togglePassword.addEventListener('click', function() {
                const input = passwordField;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });

            togglePasswordConfirmation.addEventListener('click', function() {
                const input = confirmPasswordField;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });

        // Load session counts for all users on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sessionBadges = document.querySelectorAll('.session-count');
            
            sessionBadges.forEach(badge => {
                const userId = badge.dataset.userId;
                
                fetch(`/admin/users/${userId}/session-count`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const count = data.count;
                            badge.innerHTML = `<i class="bi bi-circle-fill"></i> ${count} active`;
                            
                            // Change badge color based on session count
                            badge.classList.remove('bg-info', 'bg-success', 'bg-warning');
                            if (count === 0) {
                                badge.classList.add('bg-secondary');
                            } else if (count === 1) {
                                badge.classList.add('bg-success');
                            } else {
                                badge.classList.add('bg-warning');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching session count:', error);
                        badge.innerHTML = '<i class="bi bi-x-circle"></i> Error';
                        badge.classList.remove('bg-info');
                        badge.classList.add('bg-danger');
                    });
            });

            // Force logout functionality
            const forceLogoutButtons = document.querySelectorAll('.force-logout-btn');
            
            forceLogoutButtons.forEach(button => {
                button.addEventListener('click', async function() {
                    const userId = this.dataset.userId;
                    const userName = this.dataset.userName;
                    
                    const confirmed = await window.confirm.ask({
                        title: 'Force Logout User?',
                        message: `Are you sure you want to log out ${userName} from all devices? This will end all their active sessions immediately.`,
                        confirmText: 'Yes, Force Logout',
                        type: 'danger'
                    });
                    
                    if (!confirmed) return;
                    
                    // Show loading state
                    loading.start('forceLogout');
                    button.disabled = true;
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Logging out...';
                            
                            fetch(`/admin/users/${userId}/force-logout`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    notify.success(data.message);
                                    
                                    // Update session count badge
                                    const sessionBadge = document.querySelector(`.session-count[data-user-id="${userId}"]`);
                                    if (sessionBadge) {
                                        sessionBadge.innerHTML = '<i class="bi bi-circle-fill"></i> 0 active';
                                        sessionBadge.classList.remove('bg-info', 'bg-success', 'bg-warning');
                                        sessionBadge.classList.add('bg-secondary');
                                    }
                                } else {
                                    notify.error(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                notify.error('Failed to force logout user. Please try again.');
                            })
                            .finally(() => {
                                loading.stop('forceLogout');
                                button.disabled = false;
                                button.innerHTML = originalHTML;
                            });
                });
            });
        });
        
        // Reset 2FA functionality
        window.confirmReset2FAUser = function(userId, userName) {
            const modalEl = document.getElementById('reset2FAUserModal');
            if (!modalEl) return;
            
            document.getElementById('reset-2fa-user-user-id').value = userId;
            document.getElementById('reset-2fa-user-user-name').textContent = userName;
            
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
            
            // Focus on password input after modal opens
            setTimeout(() => {
                const passwordInput = document.getElementById('reset-2fa-user-password');
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            }, 100);
        };
    </script>
@endpush
{{-- DataTables initialization is handled by resources/js/pages/admin/users.js --}}
@endsection
