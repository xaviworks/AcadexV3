<div class="row g-2 mb-2">
    <div class="col-12">
        <label class="form-label small fw-semibold mb-1">Step Title *</label>
        <input type="text" 
               name="steps[{{ $index }}][title]" 
               class="form-control form-control-sm" 
               value="{{ old('steps.' . $index . '.title', $step->title ?? '') }}"
               placeholder="e.g., Welcome to the Dashboard"
               required>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-12">
        <label class="form-label small fw-semibold mb-1">Step Content *</label>
        <textarea name="steps[{{ $index }}][content]" 
                  class="form-control form-control-sm" 
                  rows="2"
                  placeholder="Describe what the user should learn in this step..."
                  required>{{ old('steps.' . $index . '.content', $step->content ?? '') }}</textarea>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-12">
        <label class="form-label small fw-semibold mb-1">
            Target Selector * 
            <i class="bi bi-question-circle" 
               data-bs-toggle="tooltip" 
               title="CSS selector for the element to highlight (e.g., #myButton, .card-header)"></i>
        </label>
        <div class="input-group input-group-sm">
            <input type="text" 
                   name="steps[{{ $index }}][target_selector]" 
                   class="form-control target-selector-input" 
                   value="{{ old('steps.' . $index . '.target_selector', $step->target_selector ?? '') }}"
                   placeholder=".element-class, #element-id"
                   required>
            <button type="button" class="btn btn-outline-secondary btn-sm pick-element-btn" data-step-index="{{ $index }}">
                <i class="bi bi-cursor"></i> Pick
            </button>
        </div>
        <small class="text-muted small">Use comma-separated selectors for fallbacks</small>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-md-4">
        <label class="form-label small fw-semibold mb-1">Tooltip Position</label>
        <select name="steps[{{ $index }}][position]" class="form-select form-select-sm">
            @foreach($positions as $position)
                <option value="{{ $position }}" {{ old('steps.' . $index . '.position', $step->position ?? 'bottom') === $position ? 'selected' : '' }}>
                    {{ ucfirst($position) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold mb-1">&nbsp;</label>
        <div class="form-check mt-2">
            <input type="hidden" name="steps[{{ $index }}][is_optional]" value="0">
            <input type="checkbox" 
                   name="steps[{{ $index }}][is_optional]" 
                   class="form-check-input" 
                   value="1"
                   {{ old('steps.' . $index . '.is_optional', $step->is_optional ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small">Optional</label>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold mb-1">&nbsp;</label>
        <div class="form-check mt-2">
            <input type="hidden" name="steps[{{ $index }}][requires_data]" value="0">
            <input type="checkbox" 
                   name="steps[{{ $index }}][requires_data]" 
                   class="form-check-input" 
                   value="1"
                   {{ old('steps.' . $index . '.requires_data', $step->requires_data ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small">Requires Data</label>
        </div>
    </div>
</div>
