@extends('layouts.app')

@extends('layouts.app')

{{-- Styles: resources/css/instructor/common.css --}}

@section('content')
<div class="container-fluid py-4" style="background-color: var(--theme-green-light);">
    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <div class="row">
        <div class="col-12">
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Card Container -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                
                <form method="POST" 
                      action="{{ route('instructor.students.import.upload') }}" 
                      enctype="multipart/form-data" 
                      id="uploadForm" 
                      class="d-flex align-items-center gap-2">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="file" 
                               name="file" 
                               id="file" 
                               class="form-control form-control-sm border-success" 
                               accept=".xlsx,.xls"
                               required>
                        <button type="submit" 
                                class="btn btn-success btn-sm d-flex align-items-center gap-2">
                            <i class="bi bi-upload"></i>
                            <span>Upload Excel</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="card shadow border-0 rounded-4 hover-card">
                <div class="card-body p-0">
                    <!-- Persistent Info Message -->
                    <div class="px-4 py-3 bg-success bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center text-success">
                                    <i class="bi bi-clipboard-check fs-5 me-2"></i>
                                    <span class="fw-semibold">Import Status</span>
                                </div>
                                <div class="vr text-success opacity-25" style="height: 20px;"></div>
                                <div class="text-success small">
                                    Ready to import new students to your subject roster
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success rounded-pill" id="selectedCount">0 Selected</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Controls -->
                    <div class="bg-light border-top border-bottom px-4 py-3">
                        <div class="row align-items-end g-3">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">
                                    <i class="bi bi-funnel-fill text-success me-1"></i>
                                    Filter Uploaded List
                                </label>
                                <div class="input-group input-group-sm">
                                    <select id="listFilter" 
                                            class="form-select form-select-sm border-success" 
                                            name="list_name" 
                                            onchange="filterList(this.value)">
                                        <option value="">All Uploaded Lists</option>
                                        @foreach ($reviewStudents->unique('list_name')->pluck('list_name') as $name)
                                            <option value="{{ $name }}" {{ request('list_name') === $name ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text bg-success text-white border-success">
                                        <i class="bi bi-list-check"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">
                                    <i class="bi bi-book-fill text-success me-1"></i>
                                    Compare with Subject
                                </label>
                                <div class="input-group input-group-sm">
                                    <select id="compareSubjectSelect" 
                                            class="form-select form-select-sm border-success">
                                        <option value="">Select Subject</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}" 
                                                    {{ request('compare_subject_id') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->subject_code }} - {{ $subject->subject_description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text bg-success text-white border-success">
                                        <i class="bi bi-journal-text"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" 
                                        class="btn btn-success btn-sm w-100 d-flex align-items-center justify-content-center gap-2" 
                                        onclick="runCrossCheck()"
                                        id="crossCheckBtn"
                                        style="height: 31px;">
                                    <i class="bi bi-search"></i>
                                    <span>Cross Check Data</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Add a divider with cross-check status -->
                    <div class="px-4 py-2 bg-success bg-opacity-10 border-bottom d-none" id="crossCheckStatus">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle text-success"></i>
                                <span class="text-success">Cross-check in progress...</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-success" id="matchStatus"></span>
                            </div>
                        </div>
                    </div>

                    @php
                        $listName = request('list_name');
                        $compareSubjectId = request('compare_subject_id');
                        $filteredReviewStudents = $listName ? $reviewStudents->where('list_name', $listName) : collect();
                        $existingStudents = $compareSubjectId ? \App\Models\Subject::find($compareSubjectId)?->students()->where('students.is_deleted', 0)->get() : collect();
                    @endphp

                    <!-- Data Tables Container -->
                    <div class="row g-0">
                        <!-- Uploaded Students -->
                        <div class="col-md-6 border-end position-relative">
                            <div class="loading-overlay" id="uploadedLoading">
                                <div class="loading-spinner"></div>
                            </div>
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                        Uploaded Students
                                    </h6>
                                    <div class="badge bg-success rounded-pill">
                                        {{ $filteredReviewStudents->count() }} students
                                    </div>
                                </div>
                                <div class="table-responsive custom-scrollbar" style="max-height: 600px;">
                                    <table class="table table-sm table-hover mb-0 border">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th class="text-center checkbox-column" style="width: 40px; display: none;">
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               id="selectAll" 
                                                               class="form-check-input"
                                                               data-bs-toggle="tooltip"
                                                               title="Select All">
                                                    </div>
                                                </th>
                                                <th>Full Name</th>
                                                <th class="text-center">Course</th>
                                                <th class="text-end">Year</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($filteredReviewStudents as $student)
                                                <tr class="uploaded-row table-row-transition"
                                                    data-full-name="{{ strtolower(trim($student->full_name)) }}"
                                                    data-course="{{ trim($student->course->course_code ?? '') }}"
                                                    data-year="{{ trim($student->formatted_year_level) }}">
                                                    <td class="text-center checkbox-column" style="display: none;">
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                   name="selected_students[]" 
                                                                   value="{{ $student->id }}" 
                                                                   class="form-check-input student-checkbox">
                                                        </div>
                                                    </td>
                                                    <td class="student-name">{{ $student->full_name }}</td>
                                                    <td class="text-center student-course">
                                                        {{ $student->course->course_code ?? 'N/A' }}
                                                    </td>
                                                    <td class="text-end student-year">
                                                        {{ $student->formatted_year_level }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox-fill fs-2 d-block mb-2"></i>
                                                        No uploaded list selected
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Students -->
                        <div class="col-md-6 position-relative">
                            <div class="loading-overlay" id="existingLoading">
                                <div class="loading-spinner"></div>
                            </div>
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-people-fill text-secondary me-2"></i>
                                        Existing Enrolled Students
                                    </h6>
                                    <div class="badge bg-secondary rounded-pill">
                                        {{ $existingStudents->count() }} students
                                    </div>
                                </div>
                                <div class="table-responsive custom-scrollbar" style="max-height: 600px;">
                                    <table class="table table-sm table-hover mb-0 border">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th>Full Name</th>
                                                <th class="text-center">Course</th>
                                                <th class="text-end">Year</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($existingStudents as $student)
                                                <tr class="enrolled-row table-row-transition"
                                                    data-full-name="{{ strtolower(trim($student->full_name)) }}"
                                                    data-course="{{ trim($student->course->course_code ?? '') }}"
                                                    data-year="{{ trim($student->formatted_year_level) }}">
                                                    <td class="student-name">{{ $student->full_name }}</td>
                                                    <td class="text-center student-course">
                                                        {{ $student->course->course_code ?? 'N/A' }}
                                                    </td>
                                                    <td class="text-end student-year">
                                                        {{ $student->formatted_year_level }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">
                                                        <i class="bi bi-people fs-2 d-block mb-2"></i>
                                                        No subject selected
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success">
                        <i class="bi bi-check-circle-fill me-1"></i> New Student
                    </span>
                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> Already Enrolled
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('instructor.students.import') }}" 
                       class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                        <button type="button" 
                            class="btn btn-success btn-sm d-flex align-items-center gap-2" 
                            id="importBtn"
                            disabled>
                        <i class="bi bi-check-circle"></i>
                        <span>Import Selected</span>
                        <span class="badge bg-white text-success" id="importBtnCount">0</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" 
              action="{{ route('instructor.students.import.confirm') }}" 
              class="modal-content"
              id="confirmForm">
            @csrf
            <input type="hidden" name="list_name" value="{{ $listName }}">
            <input type="hidden" name="selected_student_ids" id="selectedStudentIds">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-check text-success me-2"></i>
                    Confirm Import
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-0">
                    <label class="form-label">Target Subject</label>
                    <p class="form-control-plaintext fw-semibold" id="confirmSubjectLabel">-</p>
                    <p class="small text-muted mt-1" id="confirmStudentCount">-</p>
                    <input type="hidden" name="subject_id" id="confirmSubjectId" value="">
                </div>
                <div class="mt-3">
                    <label class="form-label small">Selected Students</label>
                    <div id="confirmSelectedList" class="list-group list-group-flush small" style="max-height: 180px; overflow:auto;">
                        <div class="list-group-item px-0 text-muted">No students selected</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-check2-all"></i>
                    <span>Confirm Import</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/instructor/excel/import-students.js --}}
@endpush
    
