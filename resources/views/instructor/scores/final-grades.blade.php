@extends('layouts.app')

@php
    $termLabels = [
        'prelim' => 'Prelim',
        'midterm' => 'Midterm',
        'prefinal' => 'Prefinal',
        'final' => 'Final',
    ];
@endphp

@section('content')

<div class="container-fluid px-0" data-page="instructor.final-grades">
    @if (!request('subject_id'))
        {{-- Card-based Subject Selection --}}
        <div class="px-4 pt-4 pb-2">
            <h1 class="h4 fw-bold mb-0 d-flex align-items-center">
                <i class="bi bi-graph-up text-success me-2" style="font-size: 1.5rem;"></i>
                <span>Final Grades</span>
            </h1>
        </div>
        @if(count($subjects) > 0)
            <div class="row g-4 px-4 py-4" id="subject-selection">
                @foreach($subjects as $subjectItem)
                    <div class="col-md-4">
                        <div
                            class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden"
                            onclick="window.location.href='{{ route('instructor.final-grades.index') }}?subject_id={{ $subjectItem->id }}'"
                            style="cursor: pointer;"
                        >
                            {{-- Top header --}}
                            <div class="position-relative" style="height: 80px;">
                                <div class="subject-circle position-absolute start-50 translate-middle"
                                    style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                    <h5 class="mb-0 text-white fw-bold">{{ $subjectItem->subject_code }}</h5>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="card-body pt-5 text-center">
                                <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $subjectItem->subject_description }}">
                                    {{ $subjectItem->subject_description }}
                                </h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-warning text-center mt-5 mx-4 rounded">
                No subjects have been assigned to you yet.
            </div>
        @endif
    @else
        {{-- Selected Subject View --}}
        <div class="px-4 py-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h1 class="h4 fw-bold mb-0 d-flex align-items-center">
                    <i class="bi bi-graph-up text-success me-2" style="font-size: 1.5rem;"></i>
                    <span>Final Grades</span>
                </h1>

                @if(!empty($finalData) && count($finalData) > 0)
                    <button type="button" id="printOptionsButton" class="btn btn-success shadow-sm d-flex align-items-center gap-2" onclick="fgOpenPrintModal()">
                        <i class="bi bi-printer-fill"></i>
                        <span>Print Options</span>
                    </button>
                @endif
            </div>

            {{-- Breadcrumb Navigation --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('instructor.final-grades.index') }}" class="text-success text-decoration-none border-bottom border-success">
                            Final Grades
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">
                        @php
                            $selectedSubject = $subjects->firstWhere('id', request('subject_id'));
                        @endphp
                        {{ $selectedSubject ? $selectedSubject->subject_code : 'Subject' }}
                    </li>
                </ol>
            </nav>

            {{-- Generate Final Grades --}}
            @if(empty($finalData))
                <form method="POST" action="{{ route('instructor.final-grades.generate') }}" class="mb-4">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                    <button type="submit" class="btn btn-success px-4 shadow-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Generate Final Grades
                    </button>
                </form>
            @endif

    {{-- Final Grades Table --}}
    @if(!empty($finalData) && count($finalData) > 0)
        <div class="card shadow-sm border-0" id="print-area">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Student Name</th>
                            <th>Prelim</th>
                            <th>Midterm</th>
                            <th>Prefinal</th>
                            <th>Final</th>
                            <th class="text-primary">Final Average</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($finalData as $data)
                            <tr class="hover-shadow-sm">
                                <td class="fw-semibold text-start">
                                    {{ $data['student']->last_name }}, {{ $data['student']->first_name }}
                                </td>
                                <td class="text-center">{{ isset($data['prelim']) ? (int) round($data['prelim']) : '–' }}</td>
                                <td class="text-center">{{ isset($data['midterm']) ? (int) round($data['midterm']) : '–' }}</td>
                                <td class="text-center">{{ isset($data['prefinal']) ? (int) round($data['prefinal']) : '–' }}</td>
                                <td class="text-center">{{ isset($data['final']) ? (int) round($data['final']) : '–' }}</td>
                                <td class="text-center fw-bold text-success">
                                    {{ isset($data['final_average']) ? (int) round($data['final_average']) : '–' }}
                                </td>
                                <td class="text-center">
                                    @if(isset($data['remarks']))
                                        @if(strtolower($data['remarks']) === 'passed')
                                            <span class="badge bg-success px-3 py-1">Passed</span>
                                        @elseif(strtolower($data['remarks']) === 'failed')
                                            <span class="badge bg-danger px-3 py-1">Failed</span>
                                        @else
                                            <span class="badge bg-secondary px-3 py-1">{{ $data['remarks'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
            <div class="alert alert-warning text-center mt-3 rounded-3">
                No students or grades found for the selected subject.
            </div>
        @endif
        </div>
    @endif
</div>

{{-- Custom Print Options Modal (No Bootstrap dependency) --}}
<div class="print-modal-overlay" id="fgPrintModalOverlay">
    <div class="print-modal-container">
        <div class="print-modal-header">
            <h5><i class="bi bi-printer"></i>Print Options</h5>
            <button type="button" class="print-modal-close" onclick="fgClosePrintModal();">&times;</button>
        </div>
        <div class="print-modal-body">
            <div class="print-options-grid">
                <div class="print-option-card">
                    <div class="print-option-card-header">
                        <h6><i class="bi bi-calendar-event"></i>Individual Terms</h6>
                    </div>
                    <div class="print-option-card-body">
                        <div class="print-btn-list">
                            <button class="print-btn print-btn-outline" onclick="fgPrintSpecificTable('prelim'); fgClosePrintModal();">
                                <i class="bi bi-printer"></i>Print Prelim Term Sheet
                            </button>
                            <button class="print-btn print-btn-outline" onclick="fgPrintSpecificTable('midterm'); fgClosePrintModal();">
                                <i class="bi bi-printer"></i>Print Midterm Term Sheet
                            </button>
                            <button class="print-btn print-btn-outline" onclick="fgPrintSpecificTable('prefinal'); fgClosePrintModal();">
                                <i class="bi bi-printer"></i>Print Prefinal Term Sheet
                            </button>
                            <button class="print-btn print-btn-outline" onclick="fgPrintSpecificTable('final'); fgClosePrintModal();">
                                <i class="bi bi-printer"></i>Print Final Term Sheet
                            </button>
                        </div>
                    </div>
                </div>
                <div class="print-option-card">
                    <div class="print-option-card-header">
                        <h6><i class="bi bi-table"></i>Complete Report</h6>
                    </div>
                    <div class="print-option-card-body">
                        <div class="print-btn-list">
                            <button class="print-btn print-btn-solid" onclick="fgPrintSpecificTable('summary'); fgClosePrintModal();">
                                <i class="bi bi-table"></i>Print Final Summary
                            </button>
                        </div>
                        <div class="print-info-text">
                            <i class="bi bi-info-circle"></i>
                            <strong>Final Summary:</strong> Shows all term grades and final averages<br>
                            <i class="bi bi-info-circle"></i>
                            <strong>Term Sheets:</strong> Detailed activities and scores per term<br><br>
                             To remove URL or headers/footers in printout, uncheck <em>Headers &amp; footers</em> in your browser's print dialog.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="print-info-alert">
                <div class="print-info-alert-icon">
                    <i class="bi bi-printer"></i>
                </div>
                <div>
                    <h6>Print Settings</h6>
                    <p>All printouts are optimized for <strong>A4 portrait</strong> format with professional styling.</p>
                    <small>Make sure your printer is set to A4 paper size for best results.</small>
                </div>
            </div>
        </div>
        <div class="print-modal-footer">
            <button type="button" class="print-modal-cancel-btn" onclick="fgClosePrintModal();">
                <i class="bi bi-x-circle"></i>Cancel
            </button>
        </div>
    </div>
</div>

{{-- View Notes Modal (Read-Only for Instructors) --}}
<div class="modal fade" id="viewNotesModal" tabindex="-1" aria-labelledby="viewNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewNotesModalLabel">
                    <i class="bi bi-sticky me-2"></i>
                    Chairperson/Coordinator Notes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Student Name:</label>
                    <p class="text-muted" id="viewStudentNameDisplay"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes/Remarks:</label>
                    <div class="border rounded p-3 bg-light" id="viewNotesContent" style="min-height: 100px; white-space: pre-wrap;"></div>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> These notes are added by your chairperson or GE coordinator. You cannot edit them.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Styles: resources/css/instructor/subject-cards.css --}}

@php
    // Calculate data for JavaScript
    $currentSubject = $subjects->firstWhere('id', request('subject_id'));
    $subjectCode = $currentSubject ? $currentSubject->subject_code : '';
    $subjectDesc = $currentSubject ? $currentSubject->description : '';
    
    // Count passed and failed students
    $passedStudents = 0;
    $failedStudents = 0;
    if (!empty($finalData)) {
        foreach ($finalData as $data) {
            if (isset($data['remarks'])) {
                if (strtolower($data['remarks']) === 'passed') {
                    $passedStudents++;
                } elseif (strtolower($data['remarks']) === 'failed') {
                    $failedStudents++;
                }
            }
        }
    }
    $totalStudents = $passedStudents + $failedStudents;
    $passRate = $totalStudents > 0 ? round(($passedStudents / $totalStudents) * 100, 1) : 0;
    
    // Get academic period info
    $activePeriod = \App\Models\AcademicPeriod::find(session('active_academic_period_id'));
    $semesterLabel = '';
    if($activePeriod) {
        switch ($activePeriod->semester) {
            case '1st':
                $semesterLabel = 'First';
                break;
            case '2nd':
                $semesterLabel = 'Second';
                break;
            case 'Summer':
                $semesterLabel = 'Summer';
                break;
        }
    }
@endphp

@push('scripts')
{{-- JavaScript moved to: resources/js/pages/instructor/scores/final-grades.js --}}
<script>
    window.pageData = {
        termReportUrl: @json(route('instructor.final-grades.term-report')),
        bannerUrl: @json(asset('images/banner-header.png')),
        currentSubjectId: @json(request('subject_id')),
        subjectCode: @json($subjectCode),
        subjectDesc: @json($subjectDesc),
        passedStudents: @json($passedStudents),
        failedStudents: @json($failedStudents),
        totalStudents: @json($totalStudents),
        passRate: @json($passRate),
        academicPeriod: @json($activePeriod?->academic_year ?? ''),
        semester: @json($semesterLabel),
        units: @json($currentSubject?->units ?? 'N/A'),
        courseSection: 'N/A'
    };
</script>
@endpush
