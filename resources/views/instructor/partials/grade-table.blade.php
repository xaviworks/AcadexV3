@php
    $activitiesCollection = collect($activities ?? []);
    $hasData = count($students) > 0 && $activitiesCollection->isNotEmpty();
    if (!isset($courseOutcomes) || empty($courseOutcomes)) {
        $courseOutcomes = collect();
    }
    // Determine passing grade threshold (fallback to 75 if not provided)
    $threshold = isset($passingGrade) && is_numeric($passingGrade)
        ? (float) $passingGrade
        : 75.0;

    $orderedActivities = $activitiesCollection
        ->map(function ($activity, $index) {
            return [
                'index' => $index,
                'activity' => $activity,
            ];
        })
        ->sort(function (array $left, array $right) {
            $leftType = mb_strtolower($left['activity']->type ?? '');
            $rightType = mb_strtolower($right['activity']->type ?? '');

            $leftPriority = $leftType === 'exam' ? 1 : 0;
            $rightPriority = $rightType === 'exam' ? 1 : 0;

            if ($leftPriority === $rightPriority) {
                return $left['index'] <=> $right['index'];
            }

            return $leftPriority <=> $rightPriority;
        })
        ->map(function (array $payload) {
            return $payload['activity'];
        })
        ->values();
@endphp

@if ($hasData)
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="input-group shadow-sm" style="width: 300px;" x-data="{ 
                init() {
                    const saved = search.get('gradeStudents');
                    if (saved) this.$el.querySelector('input').value = saved;
                }
            }">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" 
                    id="studentSearch" 
                    class="form-control border-start-0 ps-0" 
                    placeholder="Search student name..."
                    aria-label="Search student"
                    @input="search.set('gradeStudents', $event.target.value)">
            </div>
            <select id="sortFilter" class="form-select shadow-sm" style="width: 140px;">
                <option value="asc" selected>A to Z</option>
                <option value="desc">Z to A</option>
            </select>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                <span id="studentCount">{{ count($students) }}</span> students
            </div>
            <button type="button" 
                    class="btn btn-outline-secondary shadow-sm d-flex align-items-center gap-2" 
                    @click="$store.gradeTable.toggleFullscreen()"
                    x-data
                    title="Toggle expanded view">
                <i :class="$store.gradeTable.isFullscreen ? 'bi bi-fullscreen-exit' : 'bi bi-arrows-fullscreen'"></i>
                <span class="d-none d-md-inline">Expand</span>
            </button>
        </div>
    </div>

@endif

{{-- Backdrop is injected directly onto <body> via JS to avoid parent overflow/transform issues --}}

<div class="shadow-lg rounded-4 overflow-hidden border" 
     x-data 
     @click.stop
     :class="$store.gradeTable.isFullscreen ? 'grade-table-fullscreen' : ''">
    
    <!-- Fullscreen close button (X button on top right) - only visible in fullscreen mode -->
    <template x-if="$store.gradeTable.isFullscreen">
        <div x-transition
             class="position-fixed d-flex justify-content-end" 
             style="top: 16px; right: 16px; z-index: 10000;">
            <button type="button" 
                    class="btn btn-light btn-lg shadow d-flex align-items-center justify-content-center" 
                    @click="$store.gradeTable.toggleFullscreen()"
                    title="Close expanded view (Esc)"
                    style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid white;">
                <i class="bi bi-x-lg" style="font-size: 1.5rem; color: #333;"></i>
            </button>
        </div>
    </template>
    @if ($hasData)
        <div class="table-responsive">
            <div style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="align-middle" style="min-width: 200px; width: 200px;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-badge me-2"></i>
                                    <span class="fw-semibold">Student</span>
                                </div>
                            </th>
                            @foreach ($orderedActivities as $activity)
                                <th class="text-center" style="min-width: 120px; width: 120px;">
                                    <div class="fw-semibold">{{ ucfirst($activity->type) }}</div>
                                    <div class="text-muted">{{ $activity->title }}</div>
                                    <div class="mt-2">
                                        <input type="number"
                                            class="form-control form-control-sm text-center items-input"
                                            value="{{ $activity->number_of_items }}"
                                            min="1"
                                            data-activity-id="{{ $activity->id }}"
                                            style="width: 75px; margin: 0 auto; font-size: 0.95rem;"
                                            title="Number of Items"
                                            placeholder="Items">
                                    </div>
                                    <div class="mt-2">
                                        <select name="course_outcomes[{{ $activity->id }}]" 
                                            class="form-select form-select-sm course-outcome-select" 
                                            data-activity-id="{{ $activity->id }}"
                                            title="Select course outcome for this activity"
                                            style="font-size: 0.8rem; border-color: #198754; color: #000;">
                                            <option value="" 
                                                {{ !$activity->course_outcome_id ? 'selected' : '' }} 
                                                disabled>Select Course Outcome</option>
                                            @foreach ($courseOutcomes->sortBy(function($co) {
                                                // Extract the numeric part after the last space or dot
                                                preg_match('/([\d\.]+)$/', $co->co_identifier, $matches);
                                                return isset($matches[1]) ? floatval($matches[1]) : $co->co_identifier;
                                            }) as $co)
                                                <option value="{{ $co->id }}" 
                                                    {{ $activity->course_outcome_id == $co->id ? 'selected' : '' }}
                                                    @if($co->is_deleted)
                                                        style="color: #ffc107; background-color: #fff8e1;"
                                                    @else
                                                        style="color: #000;"
                                                    @endif>
                                                    {{ $co->co_code }} - {{ $co->co_identifier }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($activity->courseOutcome && $activity->courseOutcome->is_deleted)
                                            <div class="mt-1 alert alert-warning py-1 px-2 mb-0 d-flex align-items-center" style="font-size: 0.75rem; border-radius: 4px;">
                                                <i class="bi bi-exclamation-triangle-fill me-1" style="font-size: 0.8rem;"></i>
                                                <div class="text-danger small fw-bold">Outcome deleted</div>
                                            </div>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                            <th class="text-center align-middle" style="min-width: 100px; width: 100px;">
                                <div class="fw-semibold">{{ ucfirst($term) }} Grade</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        @foreach ($students as $student)
                            <tr class="student-row">
                                <td class="px-3 py-2 fw-medium text-dark" style="width: 200px;">
                                    <div class="text-truncate" title="{{ $student->last_name }}, {{ $student->first_name }} @if($student->middle_name) {{ strtoupper(substr($student->middle_name, 0, 1)) }}. @endif">
                                        {{ $student->last_name }}, {{ $student->first_name }} 
                                        @if($student->middle_name)
                                            {{ strtoupper(substr($student->middle_name, 0, 1)) }}.
                                        @endif
                                    </div>
                                </td>

                                @foreach ($orderedActivities as $activity)
                                    @php
                                        $score = $scores[$student->id][$activity->id] ?? null;
                                    @endphp
                                    <td class="px-2 py-2 text-center" style="width: 120px;">
                                        <input
                                            type="number"
                                            class="form-control text-center grade-input"
                                            name="scores[{{ $student->id }}][{{ $activity->id }}]"
                                            value="{{ $score !== null ? (int) $score : '' }}"
                                            min="0"
                                            max="{{ $activity->number_of_items }}"
                                            step="1"
                                            placeholder="–"
                                            title="Max: {{ $activity->number_of_items }}"
                                            data-student="{{ $student->id }}"
                                            data-activity="{{ $activity->id }}"
                                            style="width: 75px; margin: 0 auto; font-size: 0.95rem; height: 36px;"
                                        >
                                    </td>
                                @endforeach
                                @php
                                    // Raw term grade (to 2 decimals), and separate integer for display
                                    $rawGrade = $termGrades[$student->id] ?? null; // e.g., 89.67
                                    $displayGrade = $rawGrade !== null && is_numeric($rawGrade) ? (int) round($rawGrade) : null;

                                    // Enhanced grade styling based on dynamic passing grade threshold
                                    if ($rawGrade !== null) {
                                        if ($rawGrade >= $threshold) {
                                            $gradeClass = 'bg-success-subtle border-success';
                                            $textClass = 'text-success';
                                            $icon = 'bi-check-circle-fill';
                                        } else {
                                            $gradeClass = 'bg-danger-subtle border-danger';
                                            $textClass = 'text-danger';
                                            $icon = 'bi-x-circle-fill';
                                        }
                                    } else {
                                        $gradeClass = 'bg-secondary-subtle border-secondary';
                                        $textClass = 'text-secondary';
                                        $icon = 'bi-dash-circle';
                                    }
                                @endphp
                                
                                <td class="px-2 py-2 text-center align-middle" style="width: 100px;">
                                    <div class="d-inline-block border rounded-2 {{ $gradeClass }} position-relative" 
                                         style="min-width: 75px; padding: 8px 12px;">
                                        <div class="position-absolute top-50 start-0 translate-middle-y {{ $textClass }}" 
                                             style="margin-left: 8px;">
                                            <i class="bi {{ $icon }}"></i>
                                        </div>
                                        <span class="fw-medium {{ $textClass }}" style="font-size: 1rem; margin-left: 8px;">
                                            {{ $displayGrade !== null ? $displayGrade : '–' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <x-empty-state
            icon="bi-clipboard-data"
            title="No Data Found"
            :message="'No students or activities found for <strong>' . ucfirst($term) . '</strong>.'"
            :compact="true"
        />
    @endif

    @if ($hasData)
        <div class="text-end mt-4 mb-4 me-4 d-flex justify-content-end align-items-center">
            <!-- Alpine-powered unsaved changes indicator -->
            <div x-data x-show="$store.grades.unsavedChanges" x-transition class="me-3">
                <div class="alert alert-warning mb-0 py-2 px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span class="small fw-semibold">Unsaved changes</span>
                </div>
            </div>
            <!-- Container for validation error messages only -->
            <div id="unsavedNotificationContainer" class="me-3"></div>
            <button type="submit" id="saveGradesBtn" class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 position-relative" disabled x-data>
                <i class="bi bi-save"></i>
                <span x-text="$store.loading.isLoading('saveGrades') ? 'Saving...' : 'Save Grades'"></span>
                <div x-show="$store.loading.isLoading('saveGrades')" x-transition class="spinner-border spinner-border-sm ms-1" role="status">
                    <span class="visually-hidden">Saving...</span>
                </div>
            </button>
        </div>
    @endif
</div>



<!-- JavaScript for Client-Side Filtering -->
<script>
    // Create and manage backdrop element directly on body
    function createBackdrop() {
        if (document.getElementById('grade-table-body-backdrop')) return;
        const backdrop = document.createElement('div');
        backdrop.id = 'grade-table-body-backdrop';
        backdrop.style.cssText = [
            'position: fixed',
            'top: 0',
            'left: 0',
            'width: 100vw',
            'height: 100vh',
            'background: rgba(0, 0, 0, 0.4)',
            'backdrop-filter: blur(12px)',
            '-webkit-backdrop-filter: blur(12px)',
            'z-index: 9998',
            'pointer-events: auto',
            'cursor: default',
            'opacity: 0',
            'transition: opacity 0.2s ease',
        ].join(';');
        document.body.appendChild(backdrop);
        // Trigger transition
        requestAnimationFrame(() => backdrop.style.opacity = '1');
    }

    function removeBackdrop() {
        const backdrop = document.getElementById('grade-table-body-backdrop');
        if (!backdrop) return;
        backdrop.style.opacity = '0';
        setTimeout(() => backdrop.remove(), 200);
    }

    // Initialize Alpine store for fullscreen mode (only once)
    document.addEventListener('alpine:init', () => {
        if (!Alpine.store('gradeTable')) {
            Alpine.store('gradeTable', {
                isFullscreen: false,
                _hadAutoSaves: false,
                
                markAutoSaved() {
                    this._hadAutoSaves = true;
                },

                toggleFullscreen() {
                    this.isFullscreen = !this.isFullscreen;
                    if (this.isFullscreen) {
                        document.body.style.overflow = 'hidden';
                        createBackdrop();
                        this._hadAutoSaves = false;
                        if (window.notify) {
                            window.notify.info('Press "Esc" to close expand view');
                        }
                    } else {
                        document.body.style.overflow = '';
                        removeBackdrop();
                        // Refresh grade section to update computed term grades after auto-saves
                        if (this._hadAutoSaves && typeof window.refreshGradeSection === 'function') {
                            this._hadAutoSaves = false;
                            window.refreshGradeSection();
                        }
                    }
                }
            });
        }
    });

    // Escape key handler for fullscreen mode
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && Alpine.store('gradeTable')?.isFullscreen) {
            Alpine.store('gradeTable').toggleFullscreen();
        }
    });

    // Student search functionality
    function initializeStudentSearch() {
        const studentSearch = document.getElementById('studentSearch');
        if (studentSearch) {
            studentSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.student-row');
                let visibleCount = 0;
                
                rows.forEach(function(row) {
                    const studentName = row.querySelector('td').textContent.toLowerCase();
                    const isVisible = studentName.includes(searchTerm);
                    row.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });

                // Update student count
                const studentCount = document.getElementById('studentCount');
                if (studentCount) {
                    studentCount.textContent = visibleCount;
                }
            });
        }
    }
    
    // Initialize student search on page load
    document.addEventListener('DOMContentLoaded', initializeStudentSearch);
    
    // Export for external use
    window.initializeStudentSearch = initializeStudentSearch;
</script>

<style>
.grade-table-fullscreen {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 95vw !important;
    max-width: 1800px !important;
    height: 90vh !important;
    max-height: 900px !important;
    z-index: 9999;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.6) !important;
    border-radius: 16px !important;
    pointer-events: auto;
}

.grade-table-fullscreen .table-responsive {
    height: calc(100% - 20px);
    padding: 10px;
}

.grade-table-fullscreen .table-responsive > div {
    max-height: 100% !important;
    height: 100% !important;
}

/* Backdrop is injected directly on body via JS (#grade-table-body-backdrop) */
#grade-table-body-backdrop {
    pointer-events: auto !important;
}

/* Prevent body scroll and block #app interaction when fullscreen is active */
body:has(.grade-table-fullscreen) {
    overflow: hidden !important;
}

body:has(.grade-table-fullscreen) #app {
    pointer-events: none;
}

body:has(.grade-table-fullscreen) .grade-table-fullscreen {
    pointer-events: auto !important;
}

/* Responsive adjustments */
@media (max-width: 1400px) {
    .grade-table-fullscreen {
        width: 98vw !important;
        height: 92vh !important;
    }
}

@media (max-width: 768px) {
    .grade-table-fullscreen {
        width: 100vw !important;
        height: 100vh !important;
        border-radius: 0 !important;
    }
}</style>
