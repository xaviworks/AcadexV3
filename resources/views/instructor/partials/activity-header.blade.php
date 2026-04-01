@php
    $componentStatus = $componentStatus ?? null;
    $componentComponents = collect($componentStatus['components'] ?? []);
    $componentOptions = $componentComponents->map(function ($component) {
        $maxLabel = $component['max_allowed'] ?? null;
        $available = $component['available_slots'];
        return [
            'value' => $component['type'],
            'label' => $component['label'],
            'count' => $component['count'],
            'max' => $component['max_allowed'],
            'available' => $available,
            'status' => $component['status'],
            'helper' => $maxLabel !== null
                ? sprintf('%d/%d used%s', $component['count'], $maxLabel, ($available === 0 ? ' • Full' : ''))
                : sprintf('%d scheduled', $component['count']),
        ];
    });
@endphp

<!-- Trigger Button -->
<div class="d-flex justify-content-between align-items-center mb-4 mt-2 flex-wrap gap-2">
    <h4 class="mb-0 fw-semibold text-dark">
        <i class="bi bi-journal-text me-2 text-primary"></i>
        Activities & Grades – {{ ucfirst($term) }}
    </h4>

    <button class="btn btn-success d-flex align-items-center gap-2 shadow-sm"
            data-bs-toggle="modal" data-bs-target="#addActivityModal">
        <i class="bi bi-plus-circle-fill"></i> Add Activity
    </button>
</div>

@if ($componentComponents->isNotEmpty())
    <div class="border rounded shadow-sm py-3 px-3 mb-3" id="componentUsageSummary" style="background-color: rgba(25, 135, 84, 0.03);">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-sliders text-success"></i>
                <span class="fw-semibold text-dark">Activity Slots:</span>
            </div>
            @foreach ($componentComponents as $component)
                @php
                    $isFull = $component['max_allowed'] !== null && $component['available_slots'] === 0;
                    $isMissing = $component['status'] === 'missing';
                    $textClass = $isFull
                        ? 'text-danger'
                        : ($isMissing ? 'text-warning' : 'text-success');
                    $maxLabel = $component['max_allowed'] ?? '∞';
                @endphp
                <div class="d-flex align-items-center gap-1">
                    <span class="{{ $textClass }} fw-medium">
                        <strong>{{ $component['count'] }}/{{ $maxLabel }}</strong> {{ $component['label'] }}
                    </span>
                    @if ($isFull)
                        <span class="badge bg-danger" style="font-size: 0.65rem;">Full</span>
                    @elseif ($isMissing)
                        <span class="badge bg-warning" style="font-size: 0.65rem;">Required</span>
                    @endif
                </div>
                @if (!$loop->last)
                    <span class="text-muted" style="font-size: 1.2rem;">·</span>
                @endif
            @endforeach
            @if (!empty($formulaMeta))
                <span class="text-muted mx-2" style="font-size: 1.2rem;">|</span>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calculator text-success"></i>
                    <span class="fw-semibold text-dark">{{ $formulaMeta['label'] ?? 'Institution Baseline Formula' }}</span>
                    @php
                        $scopeLabel = strtolower($formulaMeta['scope'] ?? 'global');
                        $scopeClass = match($scopeLabel) {
                            'subject' => 'bg-primary-subtle text-primary border-primary',
                            'course' => 'bg-info-subtle text-info border-info',
                            'department' => 'bg-warning-subtle text-warning border-warning',
                            default => 'bg-success-subtle text-success border-success'
                        };
                        $scopeIcon = match($scopeLabel) {
                            'subject' => 'bi-journal-text',
                            'course' => 'bi-mortarboard',
                            'department' => 'bi-building',
                            default => 'bi-globe'
                        };
                    @endphp
                    <span class="badge {{ $scopeClass }}" style="font-size: 0.7rem;">
                        <i class="{{ $scopeIcon }}"></i> {{ ucfirst($scopeLabel) }}
                    </span>
                </div>
            @endif
        </div>
    </div>
@endif

@if ($componentStatus && ($componentStatus['all_full'] ?? false))
    <div class="alert alert-warning d-flex align-items-center gap-2 shadow-sm mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <strong>All {{ $componentStatus['label'] ?? ucfirst($term) }} components reached their limit.</strong>
            <div class="small mb-0">Realign or archive activities from Manage Activities to free up slots.</div>

        <script>
            (function() {
                function initializeActivityComponentGuard() {
                    const modal = document.getElementById('addActivityModal');
                    if (!modal) {
                        return;
                    }

                    const select = modal.querySelector('select[name="type"]');
                    const notice = modal.querySelector('[data-component-notice]');
                    const emptyAlert = modal.querySelector('[data-component-empty]');
                    const saveButton = modal.querySelector('[data-component-save]');

                    const hasAvailableOptions = () => {
                        if (!select) {
                            return false;
                        }

                        return Array.from(select.options).some(option => option.value && !option.disabled);
                    };

                    const renderNotice = () => {
                        if (!select || !notice) {
                            return;
                        }

                        const option = select.options[select.selectedIndex];
                        if (!option || !option.value) {
                            notice.classList.add('d-none');
                            notice.textContent = '';
                            return;
                        }

                        const label = option.dataset.label || option.textContent.trim();
                        const count = option.dataset.count ?? '0';
                        const max = option.dataset.max && option.dataset.max !== '' ? option.dataset.max : '∞';
                        const available = option.dataset.available ?? '';
                        const status = option.dataset.status || 'ok';

                        let helper = `${label} · ${count}/${max} used`;
                        if (available !== '' && max !== '∞') {
                            helper += ` · ${available} slot${available === '1' ? '' : 's'} left`;
                        }

                        notice.textContent = helper;
                        notice.classList.remove('d-none');
                        notice.classList.toggle('text-success', status === 'ok');
                        notice.classList.toggle('text-warning', status === 'missing');
                        notice.classList.toggle('text-danger', status === 'full');
                    };

                    const updateSaveState = () => {
                        if (!saveButton || !select) {
                            return;
                        }

                        const available = hasAvailableOptions();
                        const option = select.options[select.selectedIndex];
                        const optionAvailable = option && option.dataset
                            ? parseInt(option.dataset.available ?? '1', 10)
                            : 1;

                        if (!available) {
                            saveButton.disabled = true;
                        } else if (!select.value) {
                            saveButton.disabled = true;
                        } else if (!Number.isNaN(optionAvailable) && optionAvailable <= 0) {
                            saveButton.disabled = true;
                        } else {
                            saveButton.disabled = false;
                        }

                        if (emptyAlert) {
                            emptyAlert.classList.toggle('d-none', available);
                        }
                    };

                    if (select) {
                        select.addEventListener('change', () => {
                            renderNotice();
                            updateSaveState();
                        });
                    }

                    modal.addEventListener('shown.bs.modal', () => {
                        renderNotice();
                        updateSaveState();
                    });

                    renderNotice();
                    updateSaveState();
                }

                window.initializeActivityComponentGuard = initializeActivityComponentGuard;
                document.addEventListener('DOMContentLoaded', initializeActivityComponentGuard);
            })();
        </script>
        </div>
    </div>
@endif

<!-- Add Activity Modal -->
<div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('instructor.activities.store') }}">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="term" value="{{ $term }}">
            <input type="hidden" name="return_to" value="grades">
            {{-- When adding an activity from the Manage Grades quick-add modal, we should only create a single activity by default --}}
            <input type="hidden" name="create_single" value="1">

            @php
                $fallbackTypes = collect($activityTypes ?? [])
                    ->map(fn ($type) => mb_strtolower($type))
                    ->unique()
                    ->values();

                if ($fallbackTypes->isEmpty()) {
                    $fallbackTypes = collect(['quiz', 'ocr', 'exam']);
                }
            @endphp

            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-success border-0 pb-0">
                    <h5 class="modal-title fw-bold text-white" id="addActivityModalLabel">
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
                            <input
                                type="text"
                                class="form-control shadow-sm"
                                style="border: 2px solid #e9ecef;"
                                value="{{ $subject->subject_code }} — {{ $subject->subject_description }}"
                                readonly
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                                <i class="bi bi-calendar3 me-1"></i>Period
                            </label>
                            <input
                                type="text"
                                class="form-control shadow-sm"
                                style="border: 2px solid #e9ecef;"
                                value="{{ ucfirst($term) }}"
                                readonly
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                                <i class="bi bi-diagram-3 me-1"></i>Component Type
                            </label>
                            <select name="type" class="form-select shadow-sm" required style="border: 2px solid #e9ecef;">
                                <option value="">Select Component</option>
                                @forelse($componentOptions as $option)
                                    @php
                                        $isDisabled = $option['max'] !== null && $option['available'] === 0;
                                    @endphp
                                    <option
                                        value="{{ $option['value'] }}"
                                        {{ $isDisabled ? 'disabled' : '' }}
                                        data-label="{{ $option['label'] }}"
                                        data-count="{{ $option['count'] }}"
                                        data-max="{{ $option['max'] ?? '' }}"
                                        data-available="{{ $option['available'] ?? '' }}"
                                        data-status="{{ $option['status'] }}"
                                    >
                                        {{ $option['label'] }} — {{ $option['helper'] }}
                                    </option>
                                @empty
                                    @foreach($fallbackTypes as $type)
                                        @php
                                            $formatted = \App\Support\Grades\FormulaStructure::formatLabel($type);
                                        @endphp
                                        <option value="{{ $type }}" data-label="{{ $formatted }}">
                                            {{ $formatted }}
                                        </option>
                                    @endforeach
                                @endforelse
                            </select>
                            <div class="small text-muted d-none mt-2" data-component-notice></div>
                            <div class="alert alert-warning d-none mt-2 mb-0 py-2" data-component-empty>
                                All components for this period are at capacity. Please manage activities to free up slots.
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
                                value="{{ old('title') }}"
                            >
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
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase" style="color: #198754; letter-spacing: 0.5px;">
                                <i class="bi bi-target me-1"></i>Course Outcome (Optional)
                            </label>
                            <select name="course_outcome_id" class="form-select shadow-sm" style="border: 2px solid #e9ecef;">
                                <option value="">No specific outcome</option>
                                @foreach (($courseOutcomes ?? collect()) as $outcome)
                                    <option value="{{ $outcome->id }}" {{ (string) old('course_outcome_id') === (string) $outcome->id ? 'selected' : '' }}>
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
                    <button type="submit" class="btn btn-success shadow-sm" style="font-weight: 500;" data-component-save>
                        <i class="bi bi-check-circle me-1"></i>Save Activity
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
