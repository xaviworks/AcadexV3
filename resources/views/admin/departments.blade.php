@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-building-fill text-success me-2"></i>Departments</h1>
            <p class="text-muted mb-0">Manage academic departments</p>
        </div>
        <button class="btn btn-success" onclick="openAddDepartmentModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Department
        </button>
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

    {{-- Departments Table --}}
    <div class="card shadow-sm" style="overflow: visible;">
        <div class="card-body p-0" style="overflow: visible;">
            <div class="table-responsive">
                <table id="departmentsTable" class="table table-bordered table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr data-department-id="{{ $department->id }}">
                                <td>{{ $department->id }}</td>
                                <td class="fw-semibold department-code">{{ $department->department_code }}</td>
                                <td class="department-description">{{ $department->department_description }}</td>
                                <td class="text-center">{{ $department->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="openEditDepartmentModal({{ $department->id }}, '{{ addslashes($department->department_code) }}', '{{ addslashes($department->department_description) }}')"
                                                title="Edit Department">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="openDeleteDepartmentModal({{ $department->id }}, '{{ addslashes($department->department_code) }}')"
                                                title="Delete Department">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
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

{{-- Add Department Modal --}}
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addDepartmentModalLabel">
                    <i class="bi bi-building-add me-2"></i>Add New Department
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addDepartmentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                        <input type="text" name="department_code" id="addDepartmentCode" class="form-control" 
                               placeholder="e.g. CITE" required maxlength="50">
                        <div class="invalid-feedback" id="addDepartmentCodeError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Description <span class="text-danger">*</span></label>
                        <input type="text" name="department_description" id="addDepartmentDescription" class="form-control" 
                               placeholder="e.g. College of Information Technology Education" required maxlength="255">
                        <div class="invalid-feedback" id="addDepartmentDescriptionError"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="confirmAddDepartment()">
                        <i class="bi bi-plus-lg me-1"></i>Add Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Department Modal --}}
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editDepartmentModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Department
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDepartmentForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editDepartmentId" name="department_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                        <input type="text" name="department_code" id="editDepartmentCode" class="form-control" 
                               required maxlength="50">
                        <div class="invalid-feedback" id="editDepartmentCodeError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Description <span class="text-danger">*</span></label>
                        <input type="text" name="department_description" id="editDepartmentDescription" class="form-control" 
                               required maxlength="255">
                        <div class="invalid-feedback" id="editDepartmentDescriptionError"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmEditDepartment()">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Department Modal --}}
<div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteDepartmentModalLabel">
                    <i class="bi bi-trash me-2"></i>Delete Department
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteDepartmentId">
                <div class="text-center mb-3">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">
                        You are about to delete the department: <strong id="deleteDepartmentName" class="text-danger"></strong>
                    </p>
                    <p class="text-muted small mt-2">
                        This action cannot be undone. All associated data may be affected.
                    </p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteDepartment()">
                    <i class="bi bi-trash me-1"></i>Delete Department
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Password Confirmation Modal --}}
<div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-labelledby="passwordConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="passwordConfirmModalLabel">
                    <i class="bi bi-shield-lock me-2"></i>Confirm Your Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="cancelPasswordConfirm()"></button>
            </div>
            <form id="passwordConfirmForm" data-no-page-loader>
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        For security reasons, please re-enter your password to continue.
                    </p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="confirmPassword" class="form-control" 
                                   placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleConfirmPasswordVisibility()" tabindex="-1">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="passwordError"></div>
                        <div id="passwordErrorAlert" class="alert alert-danger mt-2 py-2 px-3 small d-none">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            <span id="passwordErrorMessage"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelPasswordConfirm()">Cancel</button>
                    <button type="submit" class="btn btn-success" id="confirmPasswordBtn">
                        <span id="confirmPasswordBtnText">
                            <i class="bi bi-check-lg me-1"></i>Confirm
                        </span>
                        <span id="confirmPasswordBtnLoading" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>Verifying...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
    
    #departmentsTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .modal-sm {
        max-width: 400px;
    }
    
    #passwordErrorAlert {
        border-radius: 0.375rem;
    }
</style>
@endpush

@push('scripts')
<script>
/**
 * Admin - Departments Page JavaScript
 * Full CRUD with Password Confirmation
 */

// Modal instances
let addDepartmentModal, editDepartmentModal, deleteDepartmentModal, passwordConfirmModal;
let pendingAction = null; // Stores the action to execute after password confirmation
let pendingActionData = null; // Stores data for the pending action

// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals
    addDepartmentModal = new bootstrap.Modal(document.getElementById('addDepartmentModal'));
    editDepartmentModal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
    deleteDepartmentModal = new bootstrap.Modal(document.getElementById('deleteDepartmentModal'));
    passwordConfirmModal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
    
    // Initialize DataTable
    if ($.fn.DataTable && $('#departmentsTable').length) {
        $('#departmentsTable').DataTable({
            order: [[1, 'asc']], // Sort by Code by default
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on Actions column
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search departments...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ departments',
                emptyTable: 'No departments found',
            },
        });
    }
    
    // Password confirm form submission
    document.getElementById('passwordConfirmForm').addEventListener('submit', function(e) {
        e.preventDefault();
        executeWithPassword();
    });
    
    // Clear error on password input
    document.getElementById('confirmPassword').addEventListener('input', function() {
        clearPasswordError();
    });
    
    // Focus password input when modal opens
    document.getElementById('passwordConfirmModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('confirmPassword').focus();
    });
    
    // Clear password field when modal closes
    document.getElementById('passwordConfirmModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('confirmPassword').value = '';
        clearPasswordError();
        resetPasswordButton();
    });
});

// =====================
// ADD DEPARTMENT
// =====================
function openAddDepartmentModal() {
    // Reset form
    document.getElementById('addDepartmentForm').reset();
    clearFormErrors('addDepartmentForm');
    addDepartmentModal.show();
}

function confirmAddDepartment() {
    const code = document.getElementById('addDepartmentCode').value.trim();
    const description = document.getElementById('addDepartmentDescription').value.trim();
    
    // Basic validation
    if (!code || !description) {
        if (!code) showFieldError('addDepartmentCode', 'Department code is required.');
        if (!description) showFieldError('addDepartmentDescription', 'Department description is required.');
        return;
    }
    
    // Store pending action
    pendingAction = 'add';
    pendingActionData = { code, description };
    
    // Hide add modal and show password confirm
    addDepartmentModal.hide();
    setTimeout(() => passwordConfirmModal.show(), 200);
}

// =====================
// EDIT DEPARTMENT
// =====================
function openEditDepartmentModal(id, code, description) {
    document.getElementById('editDepartmentId').value = id;
    document.getElementById('editDepartmentCode').value = code;
    document.getElementById('editDepartmentDescription').value = description;
    clearFormErrors('editDepartmentForm');
    editDepartmentModal.show();
}

function confirmEditDepartment() {
    const id = document.getElementById('editDepartmentId').value;
    const code = document.getElementById('editDepartmentCode').value.trim();
    const description = document.getElementById('editDepartmentDescription').value.trim();
    
    // Basic validation
    if (!code || !description) {
        if (!code) showFieldError('editDepartmentCode', 'Department code is required.');
        if (!description) showFieldError('editDepartmentDescription', 'Department description is required.');
        return;
    }
    
    // Store pending action
    pendingAction = 'edit';
    pendingActionData = { id, code, description };
    
    // Hide edit modal and show password confirm
    editDepartmentModal.hide();
    setTimeout(() => passwordConfirmModal.show(), 200);
}

// =====================
// DELETE DEPARTMENT
// =====================
function openDeleteDepartmentModal(id, code) {
    document.getElementById('deleteDepartmentId').value = id;
    document.getElementById('deleteDepartmentName').textContent = code;
    deleteDepartmentModal.show();
}

function confirmDeleteDepartment() {
    const id = document.getElementById('deleteDepartmentId').value;
    const code = document.getElementById('deleteDepartmentName').textContent;
    
    // Store pending action
    pendingAction = 'delete';
    pendingActionData = { id, code };
    
    // Hide delete modal and show password confirm
    deleteDepartmentModal.hide();
    setTimeout(() => passwordConfirmModal.show(), 200);
}

// =====================
// PASSWORD CONFIRMATION & EXECUTION
// =====================
function executeWithPassword() {
    const password = document.getElementById('confirmPassword').value;
    
    if (!password) {
        showPasswordError('Please enter your password.');
        return;
    }
    
    // Disable button and show loading
    setPasswordButtonLoading(true);
    
    // Execute the appropriate action
    switch (pendingAction) {
        case 'add':
            executeAddDepartment(password);
            break;
        case 'edit':
            executeEditDepartment(password);
            break;
        case 'delete':
            executeDeleteDepartment(password);
            break;
        default:
            setPasswordButtonLoading(false);
            showPasswordError('Invalid action.');
    }
}

function executeAddDepartment(password) {
    fetch("{{ route('admin.storeDepartment') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            department_code: pendingActionData.code,
            department_description: pendingActionData.description,
            password: password,
        }),
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        setPasswordButtonLoading(false);
        
        if (ok && data.success) {
            passwordConfirmModal.hide();
            notify.success(data.message || 'Department added successfully.');
            setTimeout(() => location.reload(), 1000);
        } else {
            showPasswordError(data.message || 'Failed to add department.');
        }
    })
    .catch(error => {
        setPasswordButtonLoading(false);
        showPasswordError('Unable to verify password. Please try again.');
        console.error('Error:', error);
    });
}

function executeEditDepartment(password) {
    fetch(`/admin/departments/${pendingActionData.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            department_code: pendingActionData.code,
            department_description: pendingActionData.description,
            password: password,
        }),
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        setPasswordButtonLoading(false);
        
        if (ok && data.success) {
            passwordConfirmModal.hide();
            notify.success(data.message || 'Department updated successfully.');
            
            // Update table row
            const row = document.querySelector(`tr[data-department-id="${pendingActionData.id}"]`);
            if (row && data.department) {
                row.querySelector('.department-code').textContent = data.department.department_code;
                row.querySelector('.department-description').textContent = data.department.department_description;
                
                // Update edit button onclick
                const editBtn = row.querySelector('.btn-outline-primary');
                if (editBtn) {
                    editBtn.setAttribute('onclick', `openEditDepartmentModal(${data.department.id}, '${data.department.department_code.replace(/'/g, "\\'")}', '${data.department.department_description.replace(/'/g, "\\'")}')`);
                }
            }
        } else {
            showPasswordError(data.message || 'Failed to update department.');
        }
    })
    .catch(error => {
        setPasswordButtonLoading(false);
        showPasswordError('Unable to verify password. Please try again.');
        console.error('Error:', error);
    });
}

function executeDeleteDepartment(password) {
    fetch(`/admin/departments/${pendingActionData.id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            password: password,
        }),
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        setPasswordButtonLoading(false);
        
        if (ok && data.success) {
            passwordConfirmModal.hide();
            notify.success(data.message || 'Department deleted successfully.');
            
            // Remove table row
            const row = document.querySelector(`tr[data-department-id="${pendingActionData.id}"]`);
            if (row) {
                row.remove();
            }
            
            // Refresh DataTable if no rows left
            if ($('#departmentsTable tbody tr').length === 0) {
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            showPasswordError(data.message || 'Failed to delete department.');
        }
    })
    .catch(error => {
        setPasswordButtonLoading(false);
        showPasswordError('Unable to verify password. Please try again.');
        console.error('Error:', error);
    });
}

function cancelPasswordConfirm() {
    pendingAction = null;
    pendingActionData = null;
}

// =====================
// UI HELPERS
// =====================
function showPasswordError(message) {
    const errorAlert = document.getElementById('passwordErrorAlert');
    const errorMessage = document.getElementById('passwordErrorMessage');
    errorMessage.textContent = message;
    errorAlert.classList.remove('d-none');
    document.getElementById('confirmPassword').classList.add('is-invalid');
    document.getElementById('confirmPassword').focus();
}

function clearPasswordError() {
    document.getElementById('passwordErrorAlert').classList.add('d-none');
    document.getElementById('confirmPassword').classList.remove('is-invalid');
}

function setPasswordButtonLoading(loading) {
    const btn = document.getElementById('confirmPasswordBtn');
    const btnText = document.getElementById('confirmPasswordBtnText');
    const btnLoading = document.getElementById('confirmPasswordBtnLoading');
    
    btn.disabled = loading;
    btnText.classList.toggle('d-none', loading);
    btnLoading.classList.toggle('d-none', !loading);
}

function resetPasswordButton() {
    setPasswordButtonLoading(false);
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + 'Error');
    if (field) field.classList.add('is-invalid');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

function clearFormErrors(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
}

function toggleConfirmPasswordVisibility() {
    const input = document.getElementById('confirmPassword');
    const icon = document.getElementById('togglePasswordIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Export functions globally
window.openAddDepartmentModal = openAddDepartmentModal;
window.openEditDepartmentModal = openEditDepartmentModal;
window.openDeleteDepartmentModal = openDeleteDepartmentModal;
window.confirmAddDepartment = confirmAddDepartment;
window.confirmEditDepartment = confirmEditDepartment;
window.confirmDeleteDepartment = confirmDeleteDepartment;
window.cancelPasswordConfirm = cancelPasswordConfirm;
window.toggleConfirmPasswordVisibility = toggleConfirmPasswordVisibility;
</script>
@endpush
@endsection
