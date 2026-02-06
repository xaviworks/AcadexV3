@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">

  @if (session('error'))
    <script>document.addEventListener('DOMContentLoaded', () => window.notify?.error(@json(session('error'))));</script>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert" style="border-left: 4px solid #dc3545 !important;">
      <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-octagon-fill me-3 fs-4 mt-1" style="color: #dc3545;"></i>
        <div class="flex-grow-1">
          <strong class="d-block mb-2">Please review the following issues:</strong>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($subjects->isEmpty())
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5">
            <div class="mb-4">
              <i class="bi bi-inbox text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h5 class="fw-semibold mb-2" style="color: #198754;">No Assigned Subjects Found</h5>
            <p class="text-muted mb-4">You don't have any subjects assigned for the current academic period.</p>
            <p class="small text-muted">
              <i class="bi bi-info-circle me-1"></i>
              Contact your chairperson to get subjects assigned so you can start managing activities.
            </p>
          </div>
        </div>
      </div>
    </div>
  @else
    @php
      $meta = $formulaSettings['meta'] ?? null;
      $structureTypeKey = $meta['structure_type'] ?? 'lecture_only';
      $structureDefinition = \App\Support\Grades\FormulaStructure::STRUCTURE_DEFINITIONS[$structureTypeKey] ?? null;
    @endphp

    <h1 class="text-2xl font-bold mb-4 d-flex align-items-center">
        <i class="bi bi-list-task text-success me-2" style="font-size: 2rem; line-height: 1; vertical-align: middle;"></i>
        <span>Manage Activities</span>
    </h1>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="activityTabs" role="tablist" style="background: transparent; border-bottom: 2px solid #dee2e6;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-top border-0 px-4 py-3 fw-semibold" 
                    id="activities-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#activities" 
                    type="button" 
                    role="tab" 
                    aria-controls="activities" 
                    aria-selected="true">
                My Activities
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-top border-0 px-4 py-3 fw-semibold" 
                    id="alignment-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#alignment" 
                    type="button" 
                    role="tab" 
                    aria-controls="alignment" 
                    aria-selected="false">
                Formula Alignment
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-top border-0 px-4 py-3 fw-semibold" 
                    id="formula-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#formula" 
                    type="button" 
                    role="tab" 
                    aria-controls="formula" 
                    aria-selected="false">
                Formula Info
            </button>
        </li>
    </ul>

    {{-- Filters section - visible only on My Activities tab --}}
    <div class="row mb-4 align-items-end" id="activityFilters" style="padding-top: 1rem;">
        <div class="col-md-5">
            <form method="GET" action="{{ route('instructor.activities.create') }}">
                <label class="form-label fw-medium mb-2">Select Course</label>
                <select name="subject_id" class="form-select" onchange="this.form.submit()">
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ optional($selectedSubject)->id === $subject->id ? 'selected' : '' }}>
                            {{ $subject->subject_code }} — {{ $subject->subject_description }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="col-md-3">
            <form method="GET" action="{{ route('instructor.activities.create') }}">
                <input type="hidden" name="subject_id" value="{{ optional($selectedSubject)->id }}">
                <label class="form-label fw-medium mb-2">Filter by Term</label>
                <select name="term" class="form-select" onchange="this.form.submit()">
                    <option value="">All Terms</option>
                    @foreach ($termLabels as $key => $label)
                        <option value="{{ $key }}" {{ $selectedTerm === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="col-md-4 text-end">
            @if ($isAligned)
                <span class="badge bg-success px-3 py-2 me-2" style="font-size: 0.9rem;">
                    <i class="bi bi-check-circle me-1"></i>Perfectly Aligned
                </span>
            @else
                <span class="badge bg-warning px-3 py-2 me-2" style="font-size: 0.9rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>Needs Alignment
                </span>
            @endif
        </div>
    </div>

    <style>
        #activityTabs {
            background: transparent !important;
        }
        #activityTabs .nav-link {
            background-color: transparent !important;
            color: #6c757d !important;
            transition: all 0.3s ease;
            position: relative;
        }
        #activityTabs .nav-link:not(.active):hover {
            background-color: rgba(25, 135, 84, 0.08) !important;
            color: var(--dark-green) !important;
        }
        #activityTabs .nav-link.active {
            background-color: rgba(25, 135, 84, 0.12) !important;
            color: var(--dark-green) !important;
            border-bottom: 3px solid var(--dark-green) !important;
            margin-bottom: -2px;
            z-index: 1;
        }
        #activityTabsContent {
            background: transparent !important;
            padding-top: 1.5rem;
        }
        #activityTabsContent .tab-pane {
            background: transparent !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const activityFilters = document.getElementById('activityFilters');
            const tabButtons = document.querySelectorAll('#activityTabs button[data-bs-toggle="tab"]');
            
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function(event) {
                    // Show filters only on My Activities tab
                    if (event.target.id === 'activities-tab') {
                        activityFilters.style.display = '';
                    } else {
                        activityFilters.style.display = 'none';
                    }
                });
            });
        });
    </script>

    <div class="tab-content" id="activityTabsContent" style="background: transparent;">
        {{-- Tab 1: My Activities --}}
        <div class="tab-pane fade show active" id="activities" role="tabpanel" aria-labelledby="activities-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">
                    @if ($selectedSubject)
                        {{ $selectedSubject->subject_code }} • {{ $selectedTerm ? $termLabels[$selectedTerm] : 'All Terms' }}
                    @else
                        Select a subject to view activities
                    @endif
                </p>
                <button 
                    type="button" 
                    class="btn btn-success" 
                    data-bs-toggle="modal" 
                    data-bs-target="#createActivityModal"
                >
                    <i class="bi bi-plus-circle me-1"></i>New Activity
                </button>
            </div>

            @if ($activities->isNotEmpty())
                <div class="card shadow-sm">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th>Title</th>
                                    <th>Component</th>
                                    <th class="text-center">Term</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activities as $index => $activity)
                                    @php
                                        $activityLabel = optional($structureDetails->firstWhere('activity_type', mb_strtolower($activity->type)))['label']
                                            ?? \App\Support\Grades\FormulaStructure::formatLabel($activity->type);
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border" style="font-size: 0.9rem; padding: 0.4rem 0.6rem; min-width: 35px;">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">{{ $activity->title }}</td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success px-2 py-1">{{ $activityLabel }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark px-2 py-1 text-capitalize">
                                                {{ $termLabels[$activity->term] ?? $activity->term }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold" style="color: #198754;">{{ $activity->number_of_items }}</td>
                                        <td class="text-center">
                                            <button 
                                                type="button"
                                                class="btn btn-danger btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDeleteModal"
                                                data-activity-id="{{ $activity->id }}"
                                                data-activity-title="{{ $activity->title }}"
                                                data-delete-url="{{ route('instructor.activities.delete', $activity->id) }}"
                                                title="Delete activity"
                                            >
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                        <h6 class="fw-semibold mb-2" style="color: #6c757d;">No Activities Found</h6>
                        <p class="text-muted small mb-3">No activities match your current filter selection.</p>
                        <button 
                            type="button" 
                            class="btn btn-success btn-sm shadow-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#createActivityModal"
                        >
                            <i class="bi bi-plus-circle me-1"></i>Create Your First Activity
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Tab 2: Formula Alignment --}}
        <div class="tab-pane fade" id="alignment" role="tabpanel" aria-labelledby="alignment-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">Track assessment distribution across all terms</p>
                @if ($selectedSubject)
                    <form method="POST" action="{{ route('instructor.activities.realign') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                        @if ($selectedTerm)
                            <input type="hidden" name="term" value="{{ $selectedTerm }}">
                        @endif
                        <button 
                            type="submit" 
                            class="btn btn-success"
                            title="Auto-adjust activities to match formula"
                        >
                            <i class="bi bi-arrow-repeat me-1"></i>Realign Activities
                        </button>
                    </form>
                @endif
            </div>

            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Component</th>
                                <th class="text-center">Weight</th>
                                <th class="text-center">Max/Term</th>
                                @foreach ($termLabels as $key => $label)
                                    <th class="text-center">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grouped_table = collect($structureDetails ?? [])->groupBy(function($d) {
                                    $parts = explode('.', $d['activity_type']);
                                    return $parts[0] ?? $d['activity_type'];
                                });
                            @endphp
                            @foreach ($grouped_table as $groupKey => $children)
                                @php
                                    $mainLabel = \App\Support\Grades\FormulaStructure::formatLabel($groupKey);
                                    $mainOverall = $children->sum('weight_percent');
                                    $mainLeaf = $structureDetails->firstWhere('activity_type', $groupKey);
                                    $hasNestedChildren = $children->contains(fn($c) => $c['activity_type'] !== $groupKey);
                                @endphp
                                @if ($hasNestedChildren)
                                    <tr class="border-bottom bg-white">
                                        <td class="fw-semibold">
                                            {{ $mainLabel }}
                                            <div class="text-muted small">{{ $mainLeaf ? $mainLeaf['activity_type'] : '' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-semibold" style="color: #198754;">-</div>
                                            <div class="text-muted small">{{ number_format($mainOverall, 1) }}%</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">
                                                {{ $mainLeaf && $mainLeaf['max_assessments'] ? $mainLeaf['max_assessments'] : '∞' }}
                                            </span>
                                        </td>
                                        @foreach ($termLabels as $termKey => $termLabel)
                                            <td class="text-center">-</td>
                                        @endforeach
                                    </tr>
                                @endif
                                @foreach ($children as $detail)
                                    <tr class="border-bottom">
                                        <td class="{{ $hasNestedChildren ? 'ps-4' : '' }}">
                                            <div class="fw-semibold">{{ $detail['label'] }}</div>
                                            <div class="text-muted small">{{ $detail['activity_type'] }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-semibold" style="color: #198754;">{{ number_format($detail['relative_weight_percent'] ?? $detail['weight_percent'], 1) }}%</div>
                                            <div class="text-muted small">{{ number_format($detail['weight_percent'], 1) }}%</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">
                                                {{ $detail['max_assessments'] ? $detail['max_assessments'] : '∞' }}
                                            </span>
                                        </td>
                                        @foreach ($termLabels as $termKey => $termLabel)
                                            @php
                                                $termComponent = collect($componentStatuses[$termKey]['components'] ?? [])->firstWhere('type', $detail['activity_type']);
                                                $count = $termComponent['count'] ?? 0;
                                                $status = $termComponent['status'] ?? 'missing';
                                                $badgeClass = match ($status) {
                                                    'ok' => 'bg-success',
                                                    'exceeds' => 'bg-danger',
                                                    default => 'bg-warning text-dark',
                                                };
                                                $badgeIcon = match ($status) {
                                                    'ok' => 'check-circle',
                                                    'exceeds' => 'x-circle',
                                                    default => 'exclamation-circle',
                                                };
                                            @endphp
                                            <td class="text-center">
                                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                                    <i class="bi bi-{{ $badgeIcon }} me-1"></i>{{ $count }}
                                                </span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab 3: Formula Info --}}
        <div class="tab-pane fade" id="formula" role="tabpanel" aria-labelledby="formula-tab">
            @if ($meta)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" style="color: #198754;">{{ $meta['label'] ?? 'ASBME Default' }}</h6>
                        
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-dark border px-3 py-2">
                                <i class="bi bi-plus-circle me-1"></i>Base {{ number_format($formulaSettings['base_score'] ?? 0, 0) }}
                            </span>
                            <span class="badge bg-light text-dark border px-3 py-2">
                                <i class="bi bi-x-circle me-1"></i>Scale ×{{ number_format($formulaSettings['scale_multiplier'] ?? 0, 0) }}
                            </span>
                            <span class="badge bg-light text-dark border px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i>Passing {{ number_format($meta['passing_grade'] ?? ($formulaSettings['passing_grade'] ?? 0), 0) }}
                            </span>
                        </div>

                        <div class="badge bg-success mb-3" style="color: white; font-weight: 500; padding: 0.5rem 1rem;">
                            <i class="bi bi-diagram-3 me-1"></i>{{ $structureDefinition['label'] ?? 'Lecture Only' }}
                        </div>
                        
                        @if (! empty($meta['scope']))
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Scope: <span class="fw-semibold">{{ ucfirst($meta['scope']) }}</span>
                                </small>
                            </div>
                        @endif

                        @if ($structureDefinition && ! empty($structureDefinition['description']))
                            <div class="alert alert-success border-0 mb-3" style="background-color: #EAF8E7;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-lightbulb text-success me-2 mt-1"></i>
                                    <small class="text-success mb-0">{{ $structureDefinition['description'] }}</small>
                                </div>
                            </div>
                        @endif

                        @if ($structureDetails->isNotEmpty())
                            <hr class="my-3">
                            <h6 class="fw-semibold mb-3">Component Breakdown</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Component</th>
                                            <th class="text-center">Weight</th>
                                            <th class="text-center">Max</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grouped = collect($structureDetails ?? [])->groupBy(function($d) {
                                                $parts = explode('.', $d['activity_type']);
                                                return $parts[0] ?? $d['activity_type'];
                                            });
                                        @endphp
                                        @foreach ($grouped as $groupKey => $children)
                                            @php
                                                $mainLabel = \App\Support\Grades\FormulaStructure::formatLabel($groupKey);
                                                $mainOverall = $children->sum('weight_percent');
                                                $mainLeaf = $structureDetails->firstWhere('activity_type', $groupKey);
                                            @endphp
                                            <tr class="table-light">
                                                <td class="fw-semibold">{{ $mainLabel }}</td>
                                                <td class="text-center fw-semibold">{{ number_format($mainOverall, 1) }}%</td>
                                                <td class="text-center">{{ $mainLeaf && $mainLeaf['max_assessments'] ? $mainLeaf['max_assessments'] : '∞' }}</td>
                                            </tr>
                                            @foreach ($children as $detail)
                                                @if ($detail['activity_type'] !== $groupKey)
                                                    <tr>
                                                        <td class="ps-4">{{ $detail['label'] }}</td>
                                                        <td class="text-center">{{ number_format($detail['weight_percent'], 1) }}%</td>
                                                        <td class="text-center">{{ $detail['max_assessments'] ? $detail['max_assessments'] : '∞' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <p class="text-muted">No formula information available.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="deleteActivityForm">
          @csrf
          @method('DELETE')
          <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
              <h5 class="modal-title fw-bold" id="confirmDeleteModalLabel" style="color: #dc3545;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Activity
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
              <p class="mb-2">You are about to permanently delete:</p>
              <div class="alert alert-danger-subtle border-0 mb-3" style="background-color: #f8d7da;">
                <strong id="activityTitlePlaceholder">this activity</strong>
              </div>
              <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>This action cannot be undone.
              </p>
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-1"></i>Cancel
              </button>
              <button type="submit" class="btn btn-danger shadow-sm">
                <i class="bi bi-trash me-1"></i>Delete Activity
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Activity Modal -->
    <div class="modal fade" id="createActivityModal" tabindex="-1" aria-labelledby="createActivityModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('instructor.activities.store') }}" class="modal-content border-0 shadow-lg needs-validation" novalidate>
          @csrf
          <input type="hidden" name="create_single" value="1">
          <div class="modal-header bg-success border-0 pb-0">
            <h5 class="modal-title fw-bold text-white" id="createActivityModalLabel">
              <i class="bi bi-plus-circle me-2"></i>Create New Activity
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                  <i class="bi bi-book me-1"></i>Subject
                </label>
                <select name="subject_id" class="form-select shadow-sm" required style="border: 2px solid #e9ecef;">
                  @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ optional($selectedSubject)->id === $subject->id ? 'selected' : '' }}>
                      {{ $subject->subject_code }} — {{ $subject->subject_description }}
                    </option>
                  @endforeach
                </select>
                <div class="invalid-feedback">
                  <i class="bi bi-exclamation-circle me-1"></i>Please select a subject.
                </div>
              </div>
              
              <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                  <i class="bi bi-calendar3 me-1"></i>Term
                </label>
                <select name="term" class="form-select shadow-sm" required style="border: 2px solid #e9ecef;">
                  <option value="">Select Term</option>
                  @foreach ($termLabels as $key => $label)
                    <option value="{{ $key }}" {{ $selectedTerm === $key ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback">
                  <i class="bi bi-exclamation-circle me-1"></i>Please select a grading term.
                </div>
              </div>
              
              <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                  <i class="bi bi-diagram-3 me-1"></i>Component Type
                </label>
                <select name="type" class="form-select shadow-sm" required style="border: 2px solid #e9ecef;">
                  <option value="">Select Component</option>
                  @php
                    $groupedSelect = collect($structureDetails ?? [])->groupBy(function($d) { $parts = explode('.', $d['activity_type']); return $parts[0] ?? $d['activity_type']; });
                  @endphp
                  @foreach ($groupedSelect as $groupKey => $children)
                    <optgroup label="{{ \App\Support\Grades\FormulaStructure::formatLabel($groupKey) }}">
                      @foreach ($children as $detail)
                        <option value="{{ $detail['activity_type'] }}">
                          {{-- Show relative weight as primary, with effective overall percent for clarity --}}
                          {{ $detail['label'] }} · {{ number_format($detail['relative_weight_percent'] ?? $detail['weight_percent'], 1) }}% (Overall {{ number_format($detail['weight_percent'], 1) }}% / {{ number_format(($detail['weight_percent'] ?? 0) / 100, 2) }})
                          @if ($detail['max_assessments'])
                            (Max {{ $detail['max_assessments'] }})
                          @endif
                        </option>
                      @endforeach
                    </optgroup>
                  @endforeach
                  @if (empty($groupedSelect) || $groupedSelect->isEmpty())
                    @foreach ($activityTypes as $type)
                      <option value="{{ $type }}">{{ \App\Support\Grades\FormulaStructure::formatLabel($type) }}</option>
                    @endforeach
                  @endif
                </select>
                <div class="invalid-feedback">
                  <i class="bi bi-exclamation-circle me-1"></i>Select the activity component type.
                </div>
              </div>
              
              <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                  <i class="bi bi-pencil me-1"></i>Activity Title
                </label>
                <input 
                  type="text" 
                  name="title" 
                  class="form-control shadow-sm" 
                  placeholder="e.g., Quiz on Chapters 1-3" 
                  required
                  style="border: 2px solid #e9ecef;"
                >
                <div class="invalid-feedback">
                  <i class="bi bi-exclamation-circle me-1"></i>Provide a descriptive activity title.
                </div>
              </div>
              
              <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                  <i class="bi bi-hash me-1"></i>Number of Items
                </label>
                <input 
                  type="number" 
                  name="number_of_items" 
                  class="form-control shadow-sm" 
                  min="1" 
                  max="500" 
                  required
                  style="border: 2px solid #e9ecef;"
                  placeholder="Enter total items"
                  value="{{ old('number_of_items', 100) }}"
                >
                <div class="invalid-feedback">
                  <i class="bi bi-exclamation-circle me-1"></i>Enter the total number of items.
                </div>
              </div>
              
              <div class="col-md-6">
                <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                  <i class="bi bi-target me-1"></i>Course Outcome (Optional)
                </label>
                <select name="course_outcome_id" class="form-select shadow-sm" style="border: 2px solid #e9ecef;">
                  <option value="">No specific outcome</option>
                  @foreach ($courseOutcomes as $outcome)
                    <option value="{{ $outcome->id }}">
                      {{ $outcome->co_code ?? 'Outcome' }} — {{ \Illuminate\Support\Str::limit($outcome->description ?? 'No description provided', 60) }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted">
                  <i class="bi bi-info-circle me-1"></i>Link to a course outcome for attainment tracking.
                </small>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-light shadow-sm" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i>Cancel
            </button>
            <button type="submit" class="btn btn-success shadow-sm" style="font-weight: 500;">
              <i class="bi bi-check-circle me-1"></i>Save Activity
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>
@endsection

{{-- Styles: resources/css/instructor/common.css --}}
{{-- JavaScript: resources/js/pages/instructor/activities-create.js --}}
