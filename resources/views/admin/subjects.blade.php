@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark fw-bold mb-0"><i class="bi bi-book-fill text-success me-2"></i>Courses</h1>
            <p class="text-muted mb-0">Manage academic courses (subjects)</p>
        </div>
        <button class="btn btn-success" onclick="openAddSubjectModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Course
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
                <table id="subjectsTable" class="table table-bordered table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th class="text-center">Units</th>
                            <th class="text-center">Year Level</th>
                            <th>Department</th>
                            <th>Program</th>
                            <th>Academic Period</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            @php
                                $yearLevelLabel = match ((int) $subject->year_level) {
                                    1 => '1st Year',
                                    2 => '2nd Year',
                                    3 => '3rd Year',
                                    4 => '4th Year',
                                    5 => '5th Year',
                                    default => '-',
                                };
                            @endphp
                            <tr data-subject-id="{{ $subject->id }}">
                                <td>{{ $subject->id }}</td>
                                <td class="fw-semibold subject-code">{{ $subject->subject_code }}</td>
                                <td class="subject-description">{{ $subject->subject_description ?? '-' }}</td>
                                <td class="text-center subject-units">{{ $subject->units ?? '-' }}</td>
                                <td class="text-center subject-year-level">{{ $yearLevelLabel }}</td>
                                <td class="subject-department">{{ $subject->department->department_description ?? '-' }}</td>
                                <td class="subject-course">{{ $subject->course->course_description ?? '-' }}</td>
                                <td class="subject-period">{{ trim(($subject->academicPeriod->academic_year ?? '-') . ' ' . ($subject->academicPeriod->semester ?? '')) }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            onclick='openEditSubjectModal({{ $subject->id }}, @json($subject->subject_code), @json($subject->subject_description), @json($subject->units), @json($subject->year_level), @json($subject->department_id), @json($subject->course_id), @json($subject->academic_period_id))'
                                            title="Edit Course">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick='openDeleteSubjectModal({{ $subject->id }}, @json($subject->subject_code))'
                                            title="Delete Course">
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

<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addSubjectModalLabel">
                    <i class="bi bi-book-fill me-2"></i>Add New Course
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSubjectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Period <span class="text-danger">*</span></label>
                        <select name="academic_period_id" id="addSubjectAcademicPeriod" class="form-select" required>
                            <option value="">Select Academic Period</option>
                            @foreach($academicPeriods as $period)
                                <option value="{{ $period->id }}">
                                    {{ $period->academic_year }} - {{ ucfirst($period->semester) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="addSubjectAcademicPeriodError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="addSubjectDepartment" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->department_code }} - {{ $department->department_description }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="addSubjectDepartmentError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                        <select name="course_id" id="addSubjectCourse" class="form-select" required>
                            <option value="">Select Program</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" data-department="{{ $course->department_id }}">
                                    {{ $course->course_code }} - {{ $course->course_description }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="addSubjectCourseError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                        <input type="text" name="subject_code" id="addSubjectCode" class="form-control" placeholder="e.g. ITE 101" required maxlength="255">
                        <div class="invalid-feedback" id="addSubjectCodeError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Description <span class="text-danger">*</span></label>
                        <input type="text" name="subject_description" id="addSubjectDescription" class="form-control" placeholder="e.g. Introduction to Computing" required maxlength="255">
                        <div class="invalid-feedback" id="addSubjectDescriptionError"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Units <span class="text-danger">*</span></label>
                            <input type="number" name="units" id="addSubjectUnits" class="form-control" required min="1" max="6">
                            <div class="invalid-feedback" id="addSubjectUnitsError"></div>
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                            <select name="year_level" id="addSubjectYearLevel" class="form-select" required>
                                <option value="">Select Year Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                            </select>
                            <div class="invalid-feedback" id="addSubjectYearLevelError"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-success" onclick="confirmAddSubject()">
                        <i class="bi bi-plus-lg me-1"></i>Add Course
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editSubjectModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Course
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubjectForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editSubjectId" name="subject_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Period <span class="text-danger">*</span></label>
                        <select name="academic_period_id" id="editSubjectAcademicPeriod" class="form-select" required>
                            <option value="">Select Academic Period</option>
                            @foreach($academicPeriods as $period)
                                <option value="{{ $period->id }}">
                                    {{ $period->academic_year }} - {{ ucfirst($period->semester) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="editSubjectAcademicPeriodError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="editSubjectDepartment" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->department_code }} - {{ $department->department_description }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="editSubjectDepartmentError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Program <span class="text-danger">*</span></label>
                        <select name="course_id" id="editSubjectCourse" class="form-select" required>
                            <option value="">Select Program</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" data-department="{{ $course->department_id }}">
                                    {{ $course->course_code }} - {{ $course->course_description }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="editSubjectCourseError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                        <input type="text" name="subject_code" id="editSubjectCode" class="form-control" required maxlength="255">
                        <div class="invalid-feedback" id="editSubjectCodeError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Description <span class="text-danger">*</span></label>
                        <input type="text" name="subject_description" id="editSubjectDescription" class="form-control" required maxlength="255">
                        <div class="invalid-feedback" id="editSubjectDescriptionError"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Units <span class="text-danger">*</span></label>
                            <input type="number" name="units" id="editSubjectUnits" class="form-control" required min="1" max="6">
                            <div class="invalid-feedback" id="editSubjectUnitsError"></div>
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                            <select name="year_level" id="editSubjectYearLevel" class="form-select" required>
                                <option value="">Select Year Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                            </select>
                            <div class="invalid-feedback" id="editSubjectYearLevelError"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-primary" onclick="confirmEditSubject()">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-labelledby="deleteSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteSubjectModalLabel">
                    <i class="bi bi-trash me-2"></i>Delete Course
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteSubjectId">
                <div class="text-center mb-3">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">
                        You are about to delete the course: <strong id="deleteSubjectName" class="text-danger"></strong>
                    </p>
                    <p class="text-muted small mt-2">
                        This action cannot be undone. Associated records must be removed or reassigned first.
                    </p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-danger" onclick="confirmDeleteSubject()">
                    <i class="bi bi-trash me-1"></i>Delete Course
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="subjectPasswordConfirmModal" tabindex="-1" aria-labelledby="subjectPasswordConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="subjectPasswordConfirmModalLabel">
                    <i class="bi bi-shield-lock me-2"></i>Confirm Your Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="cancelSubjectPasswordConfirm()"></button>
            </div>
            <form id="subjectPasswordConfirmForm" data-no-page-loader>
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        For security reasons, please re-enter your password to continue.
                    </p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="subjectConfirmPassword" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleSubjectConfirmPasswordVisibility()" tabindex="-1">
                                <i class="bi bi-eye" id="subjectTogglePasswordIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="subjectPasswordError"></div>
                        <div id="subjectPasswordErrorAlert" class="alert alert-danger mt-2 py-2 px-3 small d-none">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            <span id="subjectPasswordErrorMessage"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-success" id="subjectConfirmPasswordBtn">
                        <span id="subjectConfirmPasswordBtnText">
                            <i class="bi bi-check-lg me-1"></i>Confirm
                        </span>
                        <span id="subjectConfirmPasswordBtnLoading" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span>Verifying...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelSubjectPasswordConfirm()">Cancel</button>
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

    #subjectsTable tbody tr:hover {
        background-color: #f8f9fa;
    }

    .modal-sm {
        max-width: 400px;
    }

    #subjectPasswordErrorAlert {
        border-radius: 0.375rem;
    }
</style>
@endpush

@push('scripts')
@php
    $subjectDepartmentsById = $departments->mapWithKeys(function ($department) {
        return [
            $department->id => [
                'code' => $department->department_code,
                'description' => $department->department_description,
            ],
        ];
    });

    $subjectCoursesById = $courses->mapWithKeys(function ($course) {
        return [
            $course->id => [
                'code' => $course->course_code,
                'description' => $course->course_description,
                'department_id' => $course->department_id,
            ],
        ];
    });

    $subjectPeriodsById = $academicPeriods->mapWithKeys(function ($period) {
        return [
            $period->id => [
                'label' => $period->academic_year . ' - ' . ucfirst($period->semester),
            ],
        ];
    });
@endphp
<script>
let addSubjectModal;
let editSubjectModal;
let deleteSubjectModal;
let subjectPasswordConfirmModal;
let pendingSubjectAction = null;
let pendingSubjectActionData = null;

const subjectCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const subjectDepartmentsById = @json($subjectDepartmentsById);
const subjectCoursesById = @json($subjectCoursesById);
const subjectPeriodsById = @json($subjectPeriodsById);

document.addEventListener('DOMContentLoaded', function () {
    addSubjectModal = new bootstrap.Modal(document.getElementById('addSubjectModal'));
    editSubjectModal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
    deleteSubjectModal = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
    subjectPasswordConfirmModal = new bootstrap.Modal(document.getElementById('subjectPasswordConfirmModal'));

    if ($.fn.DataTable && $('#subjectsTable').length && !$.fn.DataTable.isDataTable('#subjectsTable')) {
        $('#subjectsTable').DataTable({
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: -1 }
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search courses...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ courses',
                emptyTable: 'No courses found',
            },
        });
    }

    document.getElementById('subjectPasswordConfirmForm').addEventListener('submit', function (e) {
        e.preventDefault();
        executeSubjectWithPassword();
    });

    document.getElementById('subjectConfirmPassword').addEventListener('input', clearSubjectPasswordError);
    document.getElementById('addSubjectDepartment').addEventListener('change', function () {
        filterSubjectCourses('addSubjectCourse', this.value);
    });
    document.getElementById('editSubjectDepartment').addEventListener('change', function () {
        filterSubjectCourses('editSubjectCourse', this.value);
    });

    document.getElementById('subjectPasswordConfirmModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('subjectConfirmPassword').focus();
    });

    document.getElementById('subjectPasswordConfirmModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('subjectConfirmPassword').value = '';
        clearSubjectPasswordError();
        resetSubjectPasswordButton();
    });

    filterSubjectCourses('addSubjectCourse', document.getElementById('addSubjectDepartment').value);
    filterSubjectCourses('editSubjectCourse', document.getElementById('editSubjectDepartment').value);
});

function openAddSubjectModal() {
    document.getElementById('addSubjectForm').reset();
    clearSubjectFormErrors('addSubjectForm');
    filterSubjectCourses('addSubjectCourse', document.getElementById('addSubjectDepartment').value);
    addSubjectModal.show();
}

function confirmAddSubject() {
    const payload = collectSubjectFormData('add');

    if (!validateSubjectForm('add', payload)) {
        return;
    }

    pendingSubjectAction = 'add';
    pendingSubjectActionData = payload;

    addSubjectModal.hide();
    setTimeout(() => subjectPasswordConfirmModal.show(), 200);
}

function openEditSubjectModal(id, code, description, units, yearLevel, departmentId, courseId, academicPeriodId) {
    document.getElementById('editSubjectId').value = id;
    document.getElementById('editSubjectCode').value = code ?? '';
    document.getElementById('editSubjectDescription').value = description ?? '';
    document.getElementById('editSubjectUnits').value = units ?? '';
    document.getElementById('editSubjectYearLevel').value = yearLevel ?? '';
    document.getElementById('editSubjectDepartment').value = departmentId ?? '';
    document.getElementById('editSubjectAcademicPeriod').value = academicPeriodId ?? '';
    filterSubjectCourses('editSubjectCourse', departmentId, courseId);
    clearSubjectFormErrors('editSubjectForm');
    editSubjectModal.show();
}

function confirmEditSubject() {
    const payload = collectSubjectFormData('edit');

    if (!validateSubjectForm('edit', payload)) {
        return;
    }

    pendingSubjectAction = 'edit';
    pendingSubjectActionData = payload;

    editSubjectModal.hide();
    setTimeout(() => subjectPasswordConfirmModal.show(), 200);
}

function openDeleteSubjectModal(id, code) {
    document.getElementById('deleteSubjectId').value = id;
    document.getElementById('deleteSubjectName').textContent = code;
    deleteSubjectModal.show();
}

function confirmDeleteSubject() {
    pendingSubjectAction = 'delete';
    pendingSubjectActionData = {
        id: document.getElementById('deleteSubjectId').value,
        code: document.getElementById('deleteSubjectName').textContent,
    };

    deleteSubjectModal.hide();
    setTimeout(() => subjectPasswordConfirmModal.show(), 200);
}

function collectSubjectFormData(prefix) {
    return {
        id: prefix === 'edit' ? document.getElementById('editSubjectId').value : null,
        code: document.getElementById(`${prefix}SubjectCode`).value.trim(),
        description: document.getElementById(`${prefix}SubjectDescription`).value.trim(),
        units: document.getElementById(`${prefix}SubjectUnits`).value,
        yearLevel: document.getElementById(`${prefix}SubjectYearLevel`).value,
        departmentId: document.getElementById(`${prefix}SubjectDepartment`).value,
        courseId: document.getElementById(`${prefix}SubjectCourse`).value,
        academicPeriodId: document.getElementById(`${prefix}SubjectAcademicPeriod`).value,
    };
}

function validateSubjectForm(prefix, payload) {
    clearSubjectFormErrors(`${prefix}SubjectForm`);

    let isValid = true;

    if (!payload.academicPeriodId) {
        showSubjectFieldError(`${prefix}SubjectAcademicPeriod`, 'Academic period is required.');
        isValid = false;
    }

    if (!payload.departmentId) {
        showSubjectFieldError(`${prefix}SubjectDepartment`, 'Department is required.');
        isValid = false;
    }

    if (!payload.courseId) {
        showSubjectFieldError(`${prefix}SubjectCourse`, 'Program is required.');
        isValid = false;
    }

    if (!payload.code) {
        showSubjectFieldError(`${prefix}SubjectCode`, 'Course code is required.');
        isValid = false;
    }

    if (!payload.description) {
        showSubjectFieldError(`${prefix}SubjectDescription`, 'Course description is required.');
        isValid = false;
    }

    if (!payload.units) {
        showSubjectFieldError(`${prefix}SubjectUnits`, 'Units are required.');
        isValid = false;
    }

    if (!payload.yearLevel) {
        showSubjectFieldError(`${prefix}SubjectYearLevel`, 'Year level is required.');
        isValid = false;
    }

    return isValid;
}

function executeSubjectWithPassword() {
    const password = document.getElementById('subjectConfirmPassword').value;

    if (!password) {
        showSubjectPasswordError('Please enter your password.');
        return;
    }

    setSubjectPasswordButtonLoading(true);

    switch (pendingSubjectAction) {
        case 'add':
            executeAddSubject(password);
            break;
        case 'edit':
            executeEditSubject(password);
            break;
        case 'delete':
            executeDeleteSubject(password);
            break;
        default:
            setSubjectPasswordButtonLoading(false);
            showSubjectPasswordError('Invalid action.');
    }
}

function executeAddSubject(password) {
    fetch("{{ route('admin.storeSubject') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': subjectCsrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            subject_code: pendingSubjectActionData.code,
            subject_description: pendingSubjectActionData.description,
            units: pendingSubjectActionData.units,
            year_level: pendingSubjectActionData.yearLevel,
            department_id: pendingSubjectActionData.departmentId,
            course_id: pendingSubjectActionData.courseId,
            academic_period_id: pendingSubjectActionData.academicPeriodId,
            password: password,
        }),
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            setSubjectPasswordButtonLoading(false);

            if (ok && data.success) {
                subjectPasswordConfirmModal.hide();
                notify.success(data.message || 'Subject added successfully.');
                setTimeout(() => location.reload(), 1000);
            } else {
                showSubjectPasswordError(data.message || 'Failed to add subject.');
            }
        })
        .catch(error => {
            setSubjectPasswordButtonLoading(false);
            showSubjectPasswordError('Unable to verify password. Please try again.');
            console.error('Error:', error);
        });
}

function executeEditSubject(password) {
    fetch(`/admin/subjects/${pendingSubjectActionData.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': subjectCsrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            subject_code: pendingSubjectActionData.code,
            subject_description: pendingSubjectActionData.description,
            units: pendingSubjectActionData.units,
            year_level: pendingSubjectActionData.yearLevel,
            department_id: pendingSubjectActionData.departmentId,
            course_id: pendingSubjectActionData.courseId,
            academic_period_id: pendingSubjectActionData.academicPeriodId,
            password: password,
        }),
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            setSubjectPasswordButtonLoading(false);

            if (ok && data.success) {
                subjectPasswordConfirmModal.hide();
                notify.success(data.message || 'Subject updated successfully.');

                const row = document.querySelector(`tr[data-subject-id="${pendingSubjectActionData.id}"]`);
                const subject = data.subject;

                if (row && subject) {
                    row.querySelector('.subject-code').textContent = subject.subject_code;
                    row.querySelector('.subject-description').textContent = subject.subject_description || '-';
                    row.querySelector('.subject-units').textContent = subject.units ?? '-';
                    row.querySelector('.subject-year-level').textContent = formatSubjectYearLevel(subject.year_level);
                    row.querySelector('.subject-department').textContent = subject.department?.department_description || subjectDepartmentsById[subject.department_id]?.description || '-';
                    row.querySelector('.subject-course').textContent = subject.course?.course_description || subjectCoursesById[subject.course_id]?.description || '-';
                    row.querySelector('.subject-period').textContent = formatSubjectPeriod(subject.academic_period_id, subject.academic_period);

                    const [editButton, deleteButton] = row.querySelectorAll('.btn-group .btn');
                    if (editButton) {
                        editButton.onclick = () => openEditSubjectModal(
                            subject.id,
                            subject.subject_code,
                            subject.subject_description,
                            subject.units,
                            subject.year_level,
                            subject.department_id,
                            subject.course_id,
                            subject.academic_period_id
                        );
                    }
                    if (deleteButton) {
                        deleteButton.onclick = () => openDeleteSubjectModal(subject.id, subject.subject_code);
                    }

                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#subjectsTable')) {
                        $('#subjectsTable').DataTable().row(row).invalidate().draw(false);
                    }
                }
            } else {
                showSubjectPasswordError(data.message || 'Failed to update subject.');
            }
        })
        .catch(error => {
            setSubjectPasswordButtonLoading(false);
            showSubjectPasswordError('Unable to verify password. Please try again.');
            console.error('Error:', error);
        });
}

function executeDeleteSubject(password) {
    fetch(`/admin/subjects/${pendingSubjectActionData.id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': subjectCsrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            password: password,
        }),
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            setSubjectPasswordButtonLoading(false);

            if (ok && data.success) {
                subjectPasswordConfirmModal.hide();
                notify.success(data.message || 'Subject deleted successfully.');

                const row = document.querySelector(`tr[data-subject-id="${pendingSubjectActionData.id}"]`);
                if (row) {
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#subjectsTable')) {
                        $('#subjectsTable').DataTable().row(row).remove().draw();
                    } else {
                        row.remove();
                    }
                }

                if ($('#subjectsTable tbody tr').length === 0) {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                showSubjectPasswordError(data.message || 'Failed to delete subject.');
            }
        })
        .catch(error => {
            setSubjectPasswordButtonLoading(false);
            showSubjectPasswordError('Unable to verify password. Please try again.');
            console.error('Error:', error);
        });
}

function filterSubjectCourses(selectId, departmentId, selectedCourseId = '') {
    const select = document.getElementById(selectId);
    if (!select) {
        return;
    }

    Array.from(select.options).forEach((option) => {
        if (option.value === '') {
            option.hidden = false;
            option.disabled = false;
            return;
        }

        const matchesDepartment = !departmentId || option.dataset.department === String(departmentId);
        option.hidden = !matchesDepartment;
        option.disabled = !matchesDepartment;
    });

    if (selectedCourseId) {
        select.value = String(selectedCourseId);
        return;
    }

    const selectedOption = select.options[select.selectedIndex];
    if (!selectedOption || selectedOption.hidden) {
        select.value = '';
    }
}

function formatSubjectYearLevel(yearLevel) {
    if (!yearLevel) {
        return '-';
    }

    const suffixes = { 1: 'st', 2: 'nd', 3: 'rd' };
    const suffix = suffixes[yearLevel] || 'th';
    return `${yearLevel}${suffix} Year`;
}

function formatSubjectPeriod(academicPeriodId, relationData) {
    if (relationData?.academic_year && relationData?.semester) {
        return `${relationData.academic_year} ${relationData.semester}`.trim();
    }

    return subjectPeriodsById[academicPeriodId]?.label || '-';
}

function cancelSubjectPasswordConfirm() {
    pendingSubjectAction = null;
    pendingSubjectActionData = null;
}

function showSubjectPasswordError(message) {
    const errorAlert = document.getElementById('subjectPasswordErrorAlert');
    const errorMessage = document.getElementById('subjectPasswordErrorMessage');

    errorMessage.textContent = message;
    errorAlert.classList.remove('d-none');
    document.getElementById('subjectConfirmPassword').classList.add('is-invalid');
    document.getElementById('subjectConfirmPassword').focus();
}

function clearSubjectPasswordError() {
    document.getElementById('subjectPasswordErrorAlert').classList.add('d-none');
    document.getElementById('subjectConfirmPassword').classList.remove('is-invalid');
}

function setSubjectPasswordButtonLoading(loading) {
    const btn = document.getElementById('subjectConfirmPasswordBtn');
    const btnText = document.getElementById('subjectConfirmPasswordBtnText');
    const btnLoading = document.getElementById('subjectConfirmPasswordBtnLoading');

    btn.disabled = loading;
    btnText.classList.toggle('d-none', loading);
    btnLoading.classList.toggle('d-none', !loading);
}

function resetSubjectPasswordButton() {
    setSubjectPasswordButtonLoading(false);
}

function showSubjectFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(fieldId + 'Error');

    if (field) {
        field.classList.add('is-invalid');
    }

    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

function clearSubjectFormErrors(formId) {
    const form = document.getElementById(formId);
    if (!form) {
        return;
    }

    form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach((element) => {
        element.textContent = '';
        element.style.display = 'none';
    });
}

function toggleSubjectConfirmPasswordVisibility() {
    const input = document.getElementById('subjectConfirmPassword');
    const icon = document.getElementById('subjectTogglePasswordIcon');

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

window.openAddSubjectModal = openAddSubjectModal;
window.openEditSubjectModal = openEditSubjectModal;
window.openDeleteSubjectModal = openDeleteSubjectModal;
window.confirmAddSubject = confirmAddSubject;
window.confirmEditSubject = confirmEditSubject;
window.confirmDeleteSubject = confirmDeleteSubject;
window.cancelSubjectPasswordConfirm = cancelSubjectPasswordConfirm;
window.toggleSubjectConfirmPasswordVisibility = toggleSubjectConfirmPasswordVisibility;
</script>
@endpush
@endsection
