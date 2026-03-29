@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-mortarboard-fill text-success me-2"></i>Programs</h1>
            <p class="text-muted mb-0">Manage academic programs and courses</p>
        </div>
        <button class="btn btn-success" onclick="openAddCourseModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Program
        </button>
    </div>

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

    <div class="card shadow-sm" style="overflow: visible;">
        <div class="card-body p-0" style="overflow: visible;">
            <div class="table-responsive">
                <table id="coursesTable" class="table table-bordered table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Department</th>
                            <th class="text-center">Created At</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr data-course-id="{{ $course->id }}">
                                <td>{{ $course->id }}</td>
                                <td class="fw-semibold course-code">{{ $course->course_code }}</td>
                                <td class="course-description">{{ $course->course_description }}</td>
                                <td class="course-department">
                                    <span class="badge bg-light text-dark border">
                                        {{ $course->department->department_code ?? 'N/A' }}
                                    </span>
                                    <small class="text-muted d-block">{{ $course->department->department_description ?? '' }}</small>
                                </td>
                                <td class="text-center">{{ $course->created_at?->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            onclick='openEditCourseModal({{ $course->id }}, @json($course->course_code), @json($course->course_description), {{ (int) $course->department_id }})'
                                            title="Edit Program">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick='openDeleteCourseModal({{ $course->id }}, @json($course->course_code))'
                                            title="Delete Program">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addCourseModalLabel">
                    <i class="bi bi-mortarboard-fill me-2"></i>Add New Program
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCourseForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Program Code <span class="text-danger">*</span></label>
                        <input type="text" name="course_code" id="addCourseCode" class="form-control" placeholder="e.g. BSIT" required maxlength="50">
                        <div class="invalid-feedback" id="addCourseCodeError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Program Description <span class="text-danger">*</span></label>
                        <input type="text" name="course_description" id="addCourseDescription" class="form-control" placeholder="e.g. Bachelor of Science in Information Technology" required maxlength="255">
                        <div class="invalid-feedback" id="addCourseDescriptionError"></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="addCourseDepartment" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_code }} - {{ $department->department_description }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="addCourseDepartmentError"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-success" onclick="confirmAddCourse()">
                        <i class="bi bi-plus-lg me-1"></i>Add Program
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editCourseModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Program
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCourseForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editCourseId" name="course_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Program Code <span class="text-danger">*</span></label>
                        <input type="text" name="course_code" id="editCourseCode" class="form-control" required maxlength="50">
                        <div class="invalid-feedback" id="editCourseCodeError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Program Description <span class="text-danger">*</span></label>
                        <input type="text" name="course_description" id="editCourseDescription" class="form-control" required maxlength="255">
                        <div class="invalid-feedback" id="editCourseDescriptionError"></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="editCourseDepartment" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_code }} - {{ $department->department_description }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="editCourseDepartmentError"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-primary" onclick="confirmEditCourse()">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCourseModalLabel">
                    <i class="bi bi-trash me-2"></i>Delete Program
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteCourseId">
                <div class="text-center mb-3">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">
                        You are about to delete the program: <strong id="deleteCourseName" class="text-danger"></strong>
                    </p>
                    <p class="text-muted small mt-2">
                        This action cannot be undone. Associated records must be removed or reassigned first.
                    </p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-danger" onclick="confirmDeleteCourse()">
                    <i class="bi bi-trash me-1"></i>Delete Program
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

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
                            <input type="password" name="password" id="confirmPassword" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
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
                    <button type="submit" class="btn btn-success" id="confirmPasswordBtn">
                        <span id="confirmPasswordBtnText">
                            <i class="bi bi-check-lg me-1"></i>Confirm
                        </span>
                        <span id="confirmPasswordBtnLoading" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>Verifying...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelPasswordConfirm()">Cancel</button>
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

    #coursesTable tbody tr:hover {
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
@php
    $courseDepartmentsById = $departments->mapWithKeys(function ($department) {
        return [
            $department->id => [
                'code' => $department->department_code,
                'description' => $department->department_description,
            ],
        ];
    });
@endphp
<script>
let addCourseModal;
let editCourseModal;
let deleteCourseModal;
let passwordConfirmModal;
let pendingAction = null;
let pendingActionData = null;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const departmentsById = @json($courseDepartmentsById);

document.addEventListener('DOMContentLoaded', function () {
    addCourseModal = new bootstrap.Modal(document.getElementById('addCourseModal'));
    editCourseModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    deleteCourseModal = new bootstrap.Modal(document.getElementById('deleteCourseModal'));
    passwordConfirmModal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));

    if ($.fn.DataTable && $('#coursesTable').length && !$.fn.DataTable.isDataTable('#coursesTable')) {
        $('#coursesTable').DataTable({
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: -1 }
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search programs...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ programs',
                emptyTable: 'No programs found',
            },
        });
    }

    document.getElementById('passwordConfirmForm').addEventListener('submit', function (e) {
        e.preventDefault();
        executeWithPassword();
    });

    document.getElementById('confirmPassword').addEventListener('input', clearPasswordError);

    document.getElementById('passwordConfirmModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('confirmPassword').focus();
    });

    document.getElementById('passwordConfirmModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('confirmPassword').value = '';
        clearPasswordError();
        resetPasswordButton();
    });
});

function openAddCourseModal() {
    document.getElementById('addCourseForm').reset();
    clearFormErrors('addCourseForm');
    addCourseModal.show();
}

function confirmAddCourse() {
    const code = document.getElementById('addCourseCode').value.trim();
    const description = document.getElementById('addCourseDescription').value.trim();
    const departmentId = document.getElementById('addCourseDepartment').value;

    clearFormErrors('addCourseForm');

    if (!code || !description || !departmentId) {
        if (!code) showFieldError('addCourseCode', 'Program code is required.');
        if (!description) showFieldError('addCourseDescription', 'Program description is required.');
        if (!departmentId) showFieldError('addCourseDepartment', 'Department is required.');
        return;
    }

    pendingAction = 'add';
    pendingActionData = { code, description, departmentId };

    addCourseModal.hide();
    setTimeout(() => passwordConfirmModal.show(), 200);
}

function openEditCourseModal(id, code, description, departmentId) {
    document.getElementById('editCourseId').value = id;
    document.getElementById('editCourseCode').value = code;
    document.getElementById('editCourseDescription').value = description;
    document.getElementById('editCourseDepartment').value = String(departmentId ?? '');
    clearFormErrors('editCourseForm');
    editCourseModal.show();
}

function confirmEditCourse() {
    const id = document.getElementById('editCourseId').value;
    const code = document.getElementById('editCourseCode').value.trim();
    const description = document.getElementById('editCourseDescription').value.trim();
    const departmentId = document.getElementById('editCourseDepartment').value;

    clearFormErrors('editCourseForm');

    if (!code || !description || !departmentId) {
        if (!code) showFieldError('editCourseCode', 'Program code is required.');
        if (!description) showFieldError('editCourseDescription', 'Program description is required.');
        if (!departmentId) showFieldError('editCourseDepartment', 'Department is required.');
        return;
    }

    pendingAction = 'edit';
    pendingActionData = { id, code, description, departmentId };

    editCourseModal.hide();
    setTimeout(() => passwordConfirmModal.show(), 200);
}

function openDeleteCourseModal(id, code) {
    document.getElementById('deleteCourseId').value = id;
    document.getElementById('deleteCourseName').textContent = code;
    deleteCourseModal.show();
}

function confirmDeleteCourse() {
    const id = document.getElementById('deleteCourseId').value;
    const code = document.getElementById('deleteCourseName').textContent;

    pendingAction = 'delete';
    pendingActionData = { id, code };

    deleteCourseModal.hide();
    setTimeout(() => passwordConfirmModal.show(), 200);
}

function executeWithPassword() {
    const password = document.getElementById('confirmPassword').value;

    if (!password) {
        showPasswordError('Please enter your password.');
        return;
    }

    setPasswordButtonLoading(true);

    switch (pendingAction) {
        case 'add':
            executeAddCourse(password);
            break;
        case 'edit':
            executeEditCourse(password);
            break;
        case 'delete':
            executeDeleteCourse(password);
            break;
        default:
            setPasswordButtonLoading(false);
            showPasswordError('Invalid action.');
    }
}

function executeAddCourse(password) {
    fetch("{{ route('admin.storeCourse') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            course_code: pendingActionData.code,
            course_description: pendingActionData.description,
            department_id: pendingActionData.departmentId,
            password: password,
        }),
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            setPasswordButtonLoading(false);

            if (ok && data.success) {
                passwordConfirmModal.hide();
                notify.success(data.message || 'Program added successfully.');
                setTimeout(() => location.reload(), 1000);
            } else {
                showPasswordError(data.message || 'Failed to add program.');
            }
        })
        .catch(error => {
            setPasswordButtonLoading(false);
            showPasswordError('Unable to verify password. Please try again.');
            console.error('Error:', error);
        });
}

function executeEditCourse(password) {
    fetch(`/admin/courses/${pendingActionData.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            course_code: pendingActionData.code,
            course_description: pendingActionData.description,
            department_id: pendingActionData.departmentId,
            password: password,
        }),
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            setPasswordButtonLoading(false);

            if (ok && data.success) {
                passwordConfirmModal.hide();
                notify.success(data.message || 'Program updated successfully.');

                const row = document.querySelector(`tr[data-course-id="${pendingActionData.id}"]`);
                const department = data.course?.department || departmentsById[data.course?.department_id] || { code: 'N/A', description: '' };

                if (row && data.course) {
                    row.querySelector('.course-code').textContent = data.course.course_code;
                    row.querySelector('.course-description').textContent = data.course.course_description;
                    row.querySelector('.course-department').innerHTML = `
                        <span class="badge bg-light text-dark border">${department.code ?? 'N/A'}</span>
                        <small class="text-muted d-block">${department.description ?? ''}</small>
                    `;

                    const buttons = row.querySelectorAll('.btn-group .btn');
                    const editButton = buttons[0];
                    const deleteButton = buttons[1];

                    if (editButton) {
                        editButton.setAttribute(
                            'onclick',
                            `openEditCourseModal(${data.course.id}, ${JSON.stringify(data.course.course_code)}, ${JSON.stringify(data.course.course_description)}, ${Number(data.course.department_id)})`
                        );
                    }

                    if (deleteButton) {
                        deleteButton.setAttribute(
                            'onclick',
                            `openDeleteCourseModal(${data.course.id}, ${JSON.stringify(data.course.course_code)})`
                        );
                    }
                }
            } else {
                showPasswordError(data.message || 'Failed to update program.');
            }
        })
        .catch(error => {
            setPasswordButtonLoading(false);
            showPasswordError('Unable to verify password. Please try again.');
            console.error('Error:', error);
        });
}

function executeDeleteCourse(password) {
    fetch(`/admin/courses/${pendingActionData.id}`, {
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
                notify.success(data.message || 'Program deleted successfully.');

                const row = document.querySelector(`tr[data-course-id="${pendingActionData.id}"]`);
                if (row) {
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#coursesTable')) {
                        $('#coursesTable').DataTable().row(row).remove().draw();
                    } else {
                        row.remove();
                    }
                }

                if ($('#coursesTable tbody tr').length === 0) {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                showPasswordError(data.message || 'Failed to delete program.');
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

window.openAddCourseModal = openAddCourseModal;
window.openEditCourseModal = openEditCourseModal;
window.openDeleteCourseModal = openDeleteCourseModal;
window.confirmAddCourse = confirmAddCourse;
window.confirmEditCourse = confirmEditCourse;
window.confirmDeleteCourse = confirmDeleteCourse;
window.cancelPasswordConfirm = cancelPasswordConfirm;
window.toggleConfirmPasswordVisibility = toggleConfirmPasswordVisibility;
</script>
@endpush
@endsection
