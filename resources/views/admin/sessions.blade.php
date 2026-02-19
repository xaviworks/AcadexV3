@extends('layouts.app')

@section('content')
{{-- Styles: resources/css/admin/sessions.css --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

<div class="container py-4" x-data="sessionsLive()" x-init="init()">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-dark fw-bold mb-0">
            <i class="fas fa-shield-alt text-success"></i> Session & Activity Monitor
        </h1>
        <button class="btn btn-danger btn-sm" onclick="confirmRevokeAll()">
            <i class="fas fa-users-slash me-2"></i>Revoke All Sessions
        </button>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4" id="sessionTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions-pane" 
                    type="button" role="tab" aria-controls="sessions-pane" aria-selected="true">
                <i class="fas fa-server me-2"></i>Active Sessions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" 
                    type="button" role="tab" aria-controls="logs-pane" aria-selected="false">
                <i class="fas fa-history me-2"></i>User Logs
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="sessionTabContent">
        {{-- Active Sessions Tab --}}
        <div class="tab-pane fade show active" id="sessions-pane" role="tabpanel" aria-labelledby="sessions-tab">
            {{-- Info Alert --}}
            <div class="alert alert-info mb-3 d-flex align-items-start">
                <i class="fas fa-info-circle me-2 mt-1"></i>
                <div>
                    <small>You can revoke individual sessions or reset 2FA for a specific user. <strong>Your current session is protected</strong> from revocation.</small>
                </div>
            </div>

            {{-- Sessions Table --}}
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Device</th>
                                    <th>Browser</th>
                                    <th>Platform</th>
                                    <th>IP Address</th>
                                    <th>Device Fingerprint</th>
                                    <th style="min-width: 140px;">Last Activity</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="session in sessions" :key="session.id">
                                    <tr :class="session.is_current ? 'current-session-row' : ''">
                                        <td>
                                            <div class="user-info">
                                                <span class="user-name" x-text="session.user_name"></span>
                                                <small class="user-email" x-text="session.email"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge bg-secondary text-white" x-text="session.role"></span>
                                        </td>
                                        <td>
                                            <template x-if="session.is_current">
                                                <span class="session-status-badge session-status-current">
                                                    <i class="fas fa-star"></i> Current
                                                </span>
                                            </template>
                                            <template x-if="!session.is_current && session.status === 'active'">
                                                <span class="session-status-badge session-status-active">
                                                    <i class="fas fa-circle"></i> Active
                                                </span>
                                            </template>
                                            <template x-if="!session.is_current && session.status === 'expired'">
                                                <span class="session-status-badge session-status-expired">
                                                    <i class="fas fa-times-circle"></i> Expired
                                                </span>
                                            </template>
                                        </td>
                                        <td>
                                            <div class="device-icon-wrapper">
                                                <span class="device-icon">
                                                    <i :class="deviceIcon(session.device_type)"></i>
                                                </span>
                                                <span x-text="session.device_type"></span>
                                            </div>
                                        </td>
                                        <td class="browser-text" x-text="session.browser"></td>
                                        <td class="platform-text" x-text="session.platform"></td>
                                        <td>
                                            <span class="ip-address" x-text="session.ip_address"></span>
                                        </td>
                                        <td>
                                            <template x-if="session.device_fingerprint">
                                                <code class="text-muted text-xs" :title="session.device_fingerprint_full" x-text="session.device_fingerprint"></code>
                                            </template>
                                            <template x-if="!session.device_fingerprint">
                                                <span class="text-muted text-xs">N/A</span>
                                            </template>
                                        </td>
                                        <td>
                                            <div class="activity-time" x-text="session.last_activity_formatted"></div>
                                            <small class="activity-date" x-text="session.last_activity_date"></small>
                                        </td>
                                        <td class="text-center">
                                            <template x-if="!session.is_current && session.user_id && session.user_name">
                                                <button class="action-btn btn-revoke" 
                                                        @click="confirmRevoke(session.id, session.user_name)"
                                                        title="Revoke this session">
                                                    <i class="fas fa-user-slash"></i>
                                                </button>
                                            </template>
                                            <template x-if="session.is_current">
                                                <span class="your-session-badge">
                                                    <i class="fas fa-circle-check"></i> Current Session
                                                </span>
                                            </template>
                                            <template x-if="!session.is_current && (!session.user_id || !session.user_name)">
                                                <span class="text-muted text-xs">
                                                    <i class="fas fa-user-slash"></i> No user
                                                </span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="sessions.length === 0">
                                    <tr>
                                        <td colspan="10" class="text-center text-muted fst-italic py-4">
                                            <i class="fas fa-info-circle me-2"></i>No active sessions found.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- End Active Sessions Tab --}}

        {{-- User Logs Tab --}}
        <div class="tab-pane fade" id="logs-pane" role="tabpanel" aria-labelledby="logs-tab">
            {{-- Date Filter --}}
            <div class="d-flex justify-content-end mb-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="date" class="mb-0 small fw-semibold text-nowrap">Filter by Date:</label>
                    <input type="date" id="date" x-model="selectedDate" @change="onDateChange()"
                           class="form-control form-control-sm max-w-180" />
                </div>
            </div>

            {{-- Logs Table --}}
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>User</th>
                                    <th>Event Type</th>
                                    <th>IP Address</th>
                                    <th>Browser</th>
                                    <th>Device</th>
                                    <th>Platform</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="log in userLogs" :key="log.id">
                                    <tr>
                                        <td>
                                            <template x-if="log.user_name">
                                                <div class="user-info">
                                                    <span class="user-name" x-text="log.user_name"></span>
                                                    <small class="user-email" x-text="log.user_email"></small>
                                                </div>
                                            </template>
                                            <template x-if="!log.user_name">
                                                <em class="text-muted">Unknown</em>
                                            </template>
                                        </td>
                                        <td>
                                            <span class="event-badge text-white" :class="'bg-' + log.event_color" x-text="log.event_type"></span>
                                        </td>
                                        <td>
                                            <span class="ip-address" x-text="log.ip_address"></span>
                                        </td>
                                        <td class="browser-text" x-text="log.browser"></td>
                                        <td class="platform-text" x-text="log.device"></td>
                                        <td class="platform-text" x-text="log.platform"></td>
                                        <td x-text="log.date"></td>
                                        <td x-text="log.time"></td>
                                    </tr>
                                </template>
                                <template x-if="userLogs.length === 0">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted fst-italic py-4">
                                            <i class="fas fa-info-circle me-2"></i>No logs found for the selected date.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- End User Logs Tab --}}
    </div>
</div>

{{-- Revoke Single Session Modal --}}
<div class="modal fade" id="revokeModal" tabindex="-1" aria-labelledby="revokeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="revokeModalLabel">Revoke Session</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="revoke-form" action="{{ route('admin.sessions.revoke') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" id="revoke-session-id">
                <div class="modal-body">
                    <p>You are about to revoke the session for <strong id="revoke-user-name"></strong>.</p>
                    <p class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This will immediately log out the user from their current session.
                    </p>
                    <div class="mt-3">
                        <label class="form-label fw-bold">Confirm Your Password</label>
                        <input type="password" name="password" id="revoke-password" class="form-control" required 
                               placeholder="Enter your admin password" autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Revoke Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset 2FA Modal --}}
<div class="modal fade" id="reset2FAModal" tabindex="-1" aria-labelledby="reset2FAModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="reset2FAModalLabel">Reset Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reset-2fa-form" action="{{ route('admin.sessions.reset2fa') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" id="reset-2fa-user-id">
                <div class="modal-body">
                    <p>You are about to <strong>disable two-factor authentication</strong> for <strong id="reset-2fa-user-name"></strong>.</p>
                    <p class="text-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This will remove their 2FA protection. They will need to re-enable and configure 2FA again if needed.
                    </p>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Use this when a user has lost access to their authenticator app or device.
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-bold">Confirm Your Password</label>
                        <input type="password" name="password" id="reset-2fa-password" class="form-control" required 
                               placeholder="Enter your admin password" autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset 2FA</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Revoke All Sessions Modal --}}
<div class="modal fade" id="revokeAllModal" tabindex="-1" aria-labelledby="revokeAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="revokeAllModalLabel">Revoke All Sessions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="revoke-all-form" action="{{ route('admin.sessions.revokeAll') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="fw-bold text-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        WARNING: This is a critical action!
                    </p>
                    <p>You are about to revoke <strong>ALL active user sessions</strong> in the system, except your current session.</p>
                    <p class="text-muted">
                        This will immediately log out all users from all their devices. Only use this in emergency situations.
                    </p>
                    <div class="mt-3">
                        <label class="form-label fw-bold">Confirm Your Password</label>
                        <input type="password" name="password" class="form-control" required 
                               placeholder="Enter your admin password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Revoke All Sessions</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    {{-- Live polling config for Alpine.js --}}
    <script>
        window.sessionsPageConfig = {
            sessions: @json($sessionsData),
            userLogs: @json($userLogsData),
            pollSessionsUrl: '{{ route("admin.sessions.poll") }}',
            pollLogsUrl: '{{ route("admin.sessions.pollLogs") }}',
            selectedDate: '{{ request("date", now()->format("Y-m-d")) }}'
        };
    </script>

    {{-- JavaScript: resources/js/pages/admin/sessions.js + sessions-poll.js --}}
    
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: true,
                confirmButtonColor: '#198754'
            });
        </script>
    @endif

    @if(session('info'))
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Information',
                text: '{{ session('info') }}',
                timer: 3000,
                showConfirmButton: true,
                confirmButtonColor: '#0dcaf0'
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                html: `
                    <ul class="text-start mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                confirmButtonColor: '#dc3545'
            });
        </script>
    @endif
@endpush
@endsection
