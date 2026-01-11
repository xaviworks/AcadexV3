<div class="step-item card mb-3" data-step-index="{{ $index }}">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-grip-vertical handle" style="cursor: move;"></i>
            Step {{ $index + 1 }}
        </h6>
        <div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-step-btn">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Step Title *</label>
            <input type="text" 
                   name="steps[{{ $index }}][title]" 
                   class="form-control" 
                   value="{{ old('steps.' . $index . '.title', $step->title ?? '') }}"
                   placeholder="e.g., Welcome to the Dashboard"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Step Content *</label>
            <textarea name="steps[{{ $index }}][content]" 
                      class="form-control" 
                      rows="3"
                      placeholder="Describe what the user should learn in this step..."
                      required>{{ old('steps.' . $index . '.content', $step->content ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Target Selector * 
                <i class="bi bi-question-circle" 
                   data-bs-toggle="tooltip" 
                   title="CSS selector for the element to highlight (e.g., #myButton, .card-header)"></i>
            </label>
            <div class="input-group">
                <input type="text" 
                       name="steps[{{ $index }}][target_selector]" 
                       class="form-control target-selector-input" 
                       value="{{ old('steps.' . $index . '.target_selector', $step->target_selector ?? '') }}"
                       placeholder=".element-class, #element-id"
                       required>
                <button type="button" class="btn btn-outline-secondary pick-element-btn" data-step-index="{{ $index }}">
                    <i class="bi bi-cursor"></i> Pick Element
                </button>
            </div>
            <small class="text-muted">Use comma-separated selectors for fallbacks</small>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Tooltip Position</label>
                <select name="steps[{{ $index }}][position]" class="form-select">
                    @foreach($positions as $position)
                        <option value="{{ $position }}" {{ old('steps.' . $index . '.position', $step->position ?? 'bottom') === $position ? 'selected' : '' }}>
                            {{ ucfirst($position) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="form-check">
                    <input type="hidden" name="steps[{{ $index }}][is_optional]" value="0">
                    <input type="checkbox" 
                           name="steps[{{ $index }}][is_optional]" 
                           class="form-check-input" 
                           value="1"
                           {{ old('steps.' . $index . '.is_optional', $step->is_optional ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Optional</label>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="form-check">
                    <input type="hidden" name="steps[{{ $index }}][requires_data]" value="0">
                    <input type="checkbox" 
                           name="steps[{{ $index }}][requires_data]" 
                           class="form-check-input" 
                           value="1"
                           {{ old('steps.' . $index . '.requires_data', $step->requires_data ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Requires Data</label>
                </div>
            </div>
        </div>
    </div>
</div>
