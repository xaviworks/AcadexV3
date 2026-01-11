/**
 * Tutorial Builder - Admin Interface
 * Handles the UI for creating and editing tutorials
 */

(function() {
    'use strict';

    let stepCounter = 0;

    document.addEventListener('DOMContentLoaded', function() {
        initTutorialBuilder();
    });

    function initTutorialBuilder() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Set initial step counter
        const existingSteps = document.querySelectorAll('.step-item');
        stepCounter = existingSteps.length;

        // Add Step button
        const addStepBtn = document.getElementById('addStepBtn');
        if (addStepBtn) {
            addStepBtn.addEventListener('click', addStep);
        }

        // Remove Step buttons (event delegation)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-step-btn')) {
                e.preventDefault();
                removeStep(e.target.closest('.step-item'));
            }
        });

        // Pick Element buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pick-element-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.pick-element-btn');
                const stepIndex = btn.dataset.stepIndex;
                startElementPicker(stepIndex);
            }
        });

        // Data check toggle
        const hasDataCheckbox = document.getElementById('has_data_check');
        const dataCheckConfig = document.getElementById('dataCheckConfig');
        if (hasDataCheckbox && dataCheckConfig) {
            hasDataCheckbox.addEventListener('change', function() {
                dataCheckConfig.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Initialize drag and drop for steps
        initSortable();

        // Renumber steps on page load
        renumberSteps();
    }

    function addStep() {
        const container = document.getElementById('stepsContainer');
        const stepIndex = stepCounter;
        
        const stepTemplate = `
            <div class="step-item card mb-3" data-step-index="${stepIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-grip-vertical handle" style="cursor: move;"></i>
                        Step ${stepIndex + 1}
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
                               name="steps[${stepIndex}][title]" 
                               class="form-control" 
                               placeholder="e.g., Welcome to the Dashboard"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Step Content *</label>
                        <textarea name="steps[${stepIndex}][content]" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Describe what the user should learn in this step..."
                                  required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Target Selector * 
                            <i class="bi bi-question-circle" 
                               data-bs-toggle="tooltip" 
                               title="CSS selector for the element to highlight"></i>
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                   name="steps[${stepIndex}][target_selector]" 
                                   class="form-control target-selector-input" 
                                   placeholder=".element-class, #element-id"
                                   required>
                            <button type="button" class="btn btn-outline-secondary pick-element-btn" data-step-index="${stepIndex}">
                                <i class="bi bi-cursor"></i> Pick Element
                            </button>
                        </div>
                        <small class="text-muted">Use comma-separated selectors for fallbacks</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Tooltip Position</label>
                            <select name="steps[${stepIndex}][position]" class="form-select">
                                <option value="top">Top</option>
                                <option value="bottom" selected>Bottom</option>
                                <option value="left">Left</option>
                                <option value="right">Right</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check">
                                <input type="hidden" name="steps[${stepIndex}][is_optional]" value="0">
                                <input type="checkbox" 
                                       name="steps[${stepIndex}][is_optional]" 
                                       class="form-check-input" 
                                       value="1">
                                <label class="form-check-label">Optional</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check">
                                <input type="hidden" name="steps[${stepIndex}][requires_data]" value="0">
                                <input type="checkbox" 
                                       name="steps[${stepIndex}][requires_data]" 
                                       class="form-check-input" 
                                       value="1">
                                <label class="form-check-label">Requires Data</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', stepTemplate);
        stepCounter++;

        // Re-initialize tooltips for new step
        const tooltips = container.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));

        // Scroll to new step
        const newStep = container.lastElementChild;
        newStep.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Re-initialize sortable
        initSortable();
    }

    function removeStep(stepElement) {
        if (document.querySelectorAll('.step-item').length === 1) {
            alert('You must have at least one step in the tutorial.');
            return;
        }

        if (confirm('Are you sure you want to remove this step?')) {
            stepElement.remove();
            renumberSteps();
        }
    }

    function renumberSteps() {
        const steps = document.querySelectorAll('.step-item');
        steps.forEach((step, index) => {
            // Update step header
            const header = step.querySelector('.card-header h6');
            if (header) {
                const gripIcon = header.querySelector('.bi-grip-vertical');
                header.innerHTML = '';
                if (gripIcon) {
                    header.appendChild(gripIcon);
                }
                header.innerHTML += ` Step ${index + 1}`;
            }

            // Update input names
            step.querySelectorAll('[name^="steps["]').forEach(input => {
                const nameParts = input.name.match(/steps\[\d+\](\[.+\])/);
                if (nameParts) {
                    input.name = `steps[${index}]${nameParts[1]}`;
                }
            });

            // Update data-step-index
            step.dataset.stepIndex = index;
            const pickBtn = step.querySelector('.pick-element-btn');
            if (pickBtn) {
                pickBtn.dataset.stepIndex = index;
            }
        });
    }

    function initSortable() {
        const container = document.getElementById('stepsContainer');
        if (!container || typeof Sortable === 'undefined') {
            // Sortable.js not loaded, skip
            return;
        }

        // Destroy existing sortable if exists
        if (container.sortableInstance) {
            container.sortableInstance.destroy();
        }

        container.sortableInstance = Sortable.create(container, {
            animation: 150,
            handle: '.handle',
            ghostClass: 'bg-light',
            onEnd: function() {
                renumberSteps();
            }
        });
    }

    function startElementPicker(stepIndex) {
        alert('Element Picker Tool\n\nThis feature will allow you to visually select elements from the target page.\n\nFor now, please:\n1. Open the target page in a new tab\n2. Right-click the element you want to highlight\n3. Select "Inspect" from the context menu\n4. Copy the CSS selector from DevTools\n5. Paste it into the Target Selector field\n\nAdvanced visual picker coming in Phase 3!');
    }

    // Export for potential use in other scripts
    window.TutorialBuilder = {
        addStep,
        removeStep,
        renumberSteps
    };
})();
