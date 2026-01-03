@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 text-dark fw-bold mb-0"><i class="bi bi-shield-fill-check text-success me-2"></i>Disaster Recovery</h1>
            <p class="text-muted small mb-0">Manage system backups and restore points</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.disaster-recovery.activity') }}" class="btn btn-outline-secondary">
                <i class="fas fa-history me-1"></i> Activity Log
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#backupModal">
                <i class="fas fa-plus me-1"></i> Create Backup
            </button>
        </div>
    </div>



    {{-- Stats --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <!-- Background decoration -->
                <div class="position-absolute top-0 end-0 p-3 opacity-10" style="opacity: 0.05; transform: rotate(15deg); margin-right: -10px;">
                    <i class="fas fa-server fa-6x text-primary"></i>
                </div>
                
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="fas fa-database fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-0">Storage Usage</h6>
                            <div class="d-flex align-items-baseline">
                                <h4 class="mb-0 fw-bold text-dark me-2">{{ $stats['storage_used'] }}</h4>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.7rem;">
                                    <i class="fas fa-check-circle me-1"></i>Healthy
                                </span>
                            </div>
                        </div>
                    </div>

                    @php
                        $bytes = $stats['storage_bytes'] ?? 0;
                        // Visual scale: assume 1GB is the "visual" limit for the bar
                        $percent = $bytes > 0 ? min(($bytes / 1073741824) * 100, 100) : 0; 
                        $color = $percent > 80 ? 'danger' : ($percent > 50 ? 'warning' : 'primary');
                    @endphp

                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Capacity</span>
                            <span class="fw-bold text-{{ $color }}">{{ number_format($percent, 1) }}%</span>
                        </div>
                        <div class="progress shadow-sm" style="height: 12px; background-color: #f0f2f5; border-radius: 6px;">
                            <div class="progress-bar bg-{{ $color }} progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: {{ $percent }}%" 
                                 aria-valuenow="{{ $percent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <div class="mt-2 d-flex align-items-center small text-muted">
                            <i class="fas fa-hdd me-1"></i> Local Server Storage
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Backups</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['total_backups'] }}</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded p-3">
                            <i class="fas fa-database fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Last Backup</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['last_backup'] }}</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info rounded p-3">
                            <i class="fas fa-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Backups List --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Your Backups</h5>
                </div>
                <div class="table-responsive" style="height: 700px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Type</th>
                                <th>Date & Time</th>
                                <th>Size</th>
                                <th>Tables</th>
                                <th>Creator</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td>
                                        @if($backup->type === 'full')
                                            <span class="badge bg-success bg-opacity-10 text-success">Full Backup</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary">Config Only</span>
                                        @endif
                                    </td>
                                    <td>{{ $backup->created_at->format('M d, Y h:i A') }}</td>
                                    <td>{{ $backup->size_formatted }}</td>
                                    <td>{{ count($backup->tables ?? []) }}</td>
                                    <td>{{ $backup->creator->name ?? 'System' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.disaster-recovery.backup.download', $backup) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="showRestoreModal({{ $backup->id }}, '{{ $backup->created_at->format('M d, Y h:i A') }}')" 
                                                    title="Restore">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="showDeleteModal({{ $backup->id }})" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted align-middle" style="height: 600px;">
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                            <i class="fas fa-database fs-1 mb-3 opacity-25"></i>
                                            <p class="mb-0">No backups found. Create one to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Auto Backup Schedule --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-clock text-info me-2"></i>Automatic Backup Schedule</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.disaster-recovery.schedule') }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-5">
                            <label class="form-label small text-muted fw-bold">Frequency</label>
                            <select name="frequency" class="form-select" onchange="toggleTimeInput(this.value)">
                                <option value="never" {{ ($schedule['frequency'] ?? 'never') === 'never' ? 'selected' : '' }}>Disabled</option>
                                <option value="daily" {{ ($schedule['frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($schedule['frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly (Sunday)</option>
                                <option value="monthly" {{ ($schedule['frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly (1st)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold">Time</label>
                            <input type="time" name="time" class="form-control" value="{{ $schedule['time'] ?? '00:00' }}" {{ ($schedule['frequency'] ?? 'never') === 'never' ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Save</button>
                        </div>
                    </form>
                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i> Keeps last 10 backups automatically.
                        </small>
                        <form id="runManualBackupForm" action="{{ route('admin.disaster-recovery.run-now') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="button" onclick="confirmRunNow()" class="btn btn-sm btn-link text-decoration-none">
                                <i class="fas fa-play me-1"></i> Run Manual Backup Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Activity</h5>
                    <a href="{{ route('admin.disaster-recovery.activity') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>
                <div class="list-group list-group-flush" style="height: 440px; overflow-y: auto;">
                    @forelse($recentActivity as $log)
                        @php
                            $colors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'restored' => 'info'];
                            $icons = ['created' => 'plus', 'updated' => 'edit', 'deleted' => 'trash', 'restored' => 'undo'];
                            $color = $colors[$log->event] ?? 'secondary';
                            $icon = $icons[$log->event] ?? 'circle';
                        @endphp
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-{{ $icon }} small"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 small">
                                        <strong>{{ $log->user?->name ?? 'System' }}</strong> 
                                        {{ $log->event }} 
                                        <span class="text-primary">{{ class_basename($log->auditable_type) }}</span>
                                    </p>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                            <i class="fas fa-history fs-1 mb-3 opacity-25"></i>
                            <p class="mb-0 small">No recent activity</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
{{-- Create Backup Modal --}}
<div class="modal fade" id="backupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.disaster-recovery.backup.create') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle text-success me-2"></i>Create Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info mb-4">
                        <div class="d-flex">
                            <i class="fas fa-info-circle fs-5 me-3 mt-1"></i>
                            <div>
                                <strong>Safe & Secure</strong>
                                <div class="small">Backups are stored locally on the server. The system remains accessible during the backup process.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">Choose Backup Type</label>
                        <style>
                            .backup-option-card {
                                transition: all 0.2s ease;
                                border: 2px solid #e9ecef;
                                cursor: pointer;
                            }
                            .backup-option-card:hover {
                                border-color: #dee2e6;
                                background-color: #f8f9fa;
                            }
                            .backup-check:checked + .backup-option-card.full {
                                border-color: #198754;
                                background-color: #f0fdf4;
                                box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
                            }
                            .backup-check:checked + .backup-option-card.config {
                                border-color: #0d6efd;
                                background-color: #f0f7ff;
                                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
                            }
                        </style>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="d-none backup-check" name="type" value="full" id="typeFull" checked>
                                <label class="card h-100 p-3 backup-option-card full" for="typeFull" style="cursor: pointer;">
                                    <div class="d-flex">
                                        <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; flex-shrink: 0;">
                                            <i class="fas fa-database fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="mb-1">
                                                <h6 class="fw-bold mb-0 text-dark">Full Backup</h6>
                                            </div>
                                            <div class="small text-muted lh-sm">
                                                Complete snapshot including all tables, student records, and settings.
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="d-none backup-check" name="type" value="config" id="typeConfig">
                                <label class="card h-100 p-3 backup-option-card config" for="typeConfig" style="cursor: pointer;">
                                    <div class="d-flex">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; flex-shrink: 0;">
                                            <i class="fas fa-cogs fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="mb-1">
                                                <h6 class="fw-bold mb-0 text-dark">Config Only</h6>
                                            </div>
                                            <div class="small text-muted lh-sm">
                                                Settings, formulas, and curriculum only. <span class="text-danger fw-bold">No student data.</span>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes <span class="text-muted fw-normal small">(Optional)</span></label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g., Pre-semester backup">
                        <div class="form-text">Add a short description to help identify this backup later.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Security Verification</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Enter your admin password" required>
                        </div>
                        <div class="form-text text-muted">Please confirm your identity to proceed.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-2"></i>Start Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Flash Messages
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: "{{ session('error') }}",
    });
@endif

async function showRestoreModal(id, date) {
    const { value: formValues } = await Swal.fire({
        title: '<span class="text-dark fw-bold">Restore System Backup</span>',
        html: `
            <div class="text-start mt-2">
                <!-- Backup Details -->
                <div class="bg-light rounded p-3 mb-4 border d-flex align-items-center">
                    <div class="bg-white p-2 rounded border me-3 text-primary shadow-sm">
                        <i class="fas fa-database fa-lg"></i>
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Restoring Point</div>
                        <div class="fw-bold text-dark fs-5">${date}</div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4">
                    <div class="d-flex">
                        <i class="fas fa-exclamation-triangle fs-4 me-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Warning: Data Overwrite</h6>
                            <div class="small">This action will replace your current database with the selected backup. Any changes made after this backup date will be lost.</div>
                        </div>
                    </div>
                </div>

                <!-- Safety Option -->
                <div class="mb-4">
                    <div class="bg-white border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-check-label" for="swal-safety" style="cursor: pointer;">
                                <span class="fw-bold text-dark">Create Safety Backup</span>
                                <span class="d-block small text-muted">Recommended. Saves current state before restoring.</span>
                            </label>
                            <div class="form-check form-switch mb-0 ps-0">
                                <input class="form-check-input ms-0" type="checkbox" id="swal-safety" style="cursor: pointer; transform: scale(1.3);">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation & Auth -->
                <div class="mb-2">
                    <label class="form-label fw-bold small text-uppercase text-muted">Verification</label>
                    
                    <div class="form-check mb-3 user-select-none">
                        <input class="form-check-input" type="checkbox" id="swal-confirm" style="cursor: pointer;">
                        <label class="form-check-label fw-bold text-danger" for="swal-confirm" style="cursor: pointer;">
                            I understand the risks and want to proceed
                        </label>
                    </div>
                    
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white text-muted"><i class="fas fa-lock"></i></span>
                        <input type="password" id="swal-password" class="form-control" placeholder="Enter admin password">
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-undo me-2"></i>Restore Backup',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6c757d',
        focusConfirm: false,
        width: '550px',
        customClass: {
            confirmButton: 'btn btn-danger btn-lg px-4 shadow-sm',
            cancelButton: 'btn btn-secondary btn-lg px-4 shadow-sm'
        },
        didOpen: () => {
            const confirmBtn = Swal.getConfirmButton();
            const confirmCheck = document.getElementById('swal-confirm');
            const passwordInput = document.getElementById('swal-password');
            
            // Initial state
            confirmBtn.disabled = true;
            
            function validate() {
                confirmBtn.disabled = !(confirmCheck.checked && passwordInput.value.length > 0);
            }

            confirmCheck.addEventListener('change', validate);
            passwordInput.addEventListener('input', validate);
        },
        preConfirm: () => {
            const safety = document.getElementById('swal-safety').checked;
            const confirm = document.getElementById('swal-confirm').checked;
            const password = document.getElementById('swal-password').value;

            if (!confirm || !password) {
                return false;
            }

            return { safety, confirm, password };
        }
    });

    if (formValues) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/disaster-recovery/backup/${id}/restore`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);

        const safetyInput = document.createElement('input');
        safetyInput.type = 'hidden';
        safetyInput.name = 'create_safety_backup';
        safetyInput.value = formValues.safety ? '1' : '0';
        form.appendChild(safetyInput);

        const confirmInput = document.createElement('input');
        confirmInput.type = 'hidden';
        confirmInput.name = 'confirm_restore';
        confirmInput.value = '1';
        form.appendChild(confirmInput);

        const passInput = document.createElement('input');
        passInput.type = 'hidden';
        passInput.name = 'password';
        passInput.value = formValues.password;
        form.appendChild(passInput);

        document.body.appendChild(form);
        form.submit();
    }
}

function confirmRunNow() {
    Swal.fire({
        title: 'Run Manual Backup?',
        text: "This will create a new backup immediately. The system will remain accessible.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Run Backup'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('runManualBackupForm').submit();
        }
    });
}

function showDeleteModal(id) {
    Swal.fire({
        title: 'Delete Backup?',
        text: "This action cannot be undone.",
        icon: 'error',
        input: 'password',
        inputPlaceholder: 'Enter password',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete',
        preConfirm: (password) => {
            if (!password) {
                Swal.showValidationMessage('Password is required');
            }
            return password;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/disaster-recovery/backup/${id}`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);

            const passInput = document.createElement('input');
            passInput.type = 'hidden';
            passInput.name = 'password';
            passInput.value = result.value;
            form.appendChild(passInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function toggleTimeInput(value) {
    const timeInput = document.querySelector('input[name="time"]');
    if (value === 'never') {
        timeInput.disabled = true;
    } else {
        timeInput.disabled = false;
    }
}
</script>
@endpush
@endsection
