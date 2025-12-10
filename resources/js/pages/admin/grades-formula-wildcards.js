/**
 * Admin Grades Formula Wildcards Page JavaScript
 * Handles sections, overview cards, formula modals, and structure template management
 */

export function initGradesFormulaWildcards() {
    const pageData = window.pageData || {};
    const sectionButtons = document.querySelectorAll('.wildcard-section-btn');
    const sectionContainer = document.querySelector('[data-section-container]');
    const sections = sectionContainer ? sectionContainer.querySelectorAll('[data-section]') : [];
    const shouldReopenTemplateModal = pageData.shouldReopenTemplateModal || false;
    const shouldReopenCreateFormulaModal = pageData.shouldReopenCreateFormulaModal || false;
    const templateModalInitialMode = pageData.templateModalMode || 'create';
    const templateModalInitialEditId = pageData.templateModalEditId || null;
    const templateDeleteReopenId = pageData.reopenTemplateDeleteId || null;
    const templateDeleteErrorMessage = pageData.deleteTemplatePasswordError || '';
    const templateUpdatePlaceholder = 'TEMPLATE_ID';
    const structureTemplates = pageData.structureTemplates || [];
    const templateErrorMessages = pageData.templateErrorMessages || [];
    const oldTemplateInputs = pageData.oldTemplateInputs || {};
    const oldTemplateComponents = oldTemplateInputs.components || {};

    const setActiveSection = (sectionName) => {
        sections.forEach((section) => {
            const matches = section.dataset.section === sectionName;
            section.classList.toggle('d-none', !matches);
        });

        sectionButtons.forEach((button) => {
            const matches = button.dataset.sectionTarget === sectionName;
            button.classList.toggle('btn-success', matches);
            button.classList.toggle('active', matches);
            if (!matches) {
                button.classList.add('btn-outline-success');
            } else {
                button.classList.remove('btn-outline-success');
            }
        });
    };

    const initialSection = sectionContainer?.dataset.initialSection ?? 'overview';
    setActiveSection(initialSection);

    sectionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.sectionTarget;
            if (!target) {
                return;
            }
            setActiveSection(target);
        });
    });

    // Overview cards
    const overviewCards = document.querySelectorAll('#overview-department-grid .wildcard-card');

    overviewCards.forEach((card) => {
        const titleElement = card.querySelector('.wildcard-title');
        if (titleElement && !card.getAttribute('aria-label')) {
            const label = titleElement.textContent?.trim();
            if (label) {
                card.setAttribute('aria-label', `View ${label} formula details`);
            }
        }

        card.setAttribute('role', 'link');
        card.setAttribute('tabindex', '0');

        const clearPressedState = () => {
            card.classList.remove('is-pressed');
        };

        card.addEventListener('pointerdown', () => {
            card.classList.add('is-pressed');
        });

        card.addEventListener('pointerup', clearPressedState);
        card.addEventListener('pointerleave', clearPressedState);
        card.addEventListener('blur', clearPressedState);
        card.addEventListener('keyup', (event) => {
            if (event.key === 'Tab' || event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                clearPressedState();
            }
        });

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                card.classList.add('is-pressed');
                card.click();
            }
        });

        card.addEventListener('click', (event) => {
            const url = card.dataset.url;
            if (!url) {
                return;
            }

            const isInteractiveChild = event.target.closest('a, button, form, input, label');
            if (isInteractiveChild) {
                clearPressedState();
                return;
            }

            clearPressedState();
            window.location.href = url;
        });
    });

    // Create Formula Modal Logic
    const createFormulaModal = document.getElementById('create-formula-modal');
    const createFormulaForm = document.getElementById('create-formula-form');
    const createFormulaContext = document.getElementById('create-formula-context');
    const createFormulaContextSemester = document.getElementById('create-formula-context-semester');
    const createFormulaContextYear = document.getElementById('create-formula-context-year');
    const createFormulaError = document.getElementById('create-formula-error');
    const createFormulaPassword = document.getElementById('create-formula-password');
    const createFormulaTemplate = document.getElementById('create-formula-template');
    const createFormulaStructureType = document.getElementById('create-formula-structure-type');
    const createFormulaStructureConfig = document.getElementById('create-formula-structure-config');
    const createFormulaModalInstance = createFormulaModal && window.bootstrap?.Modal ? window.bootstrap.Modal.getOrCreateInstance(createFormulaModal) : null;

    // Handle template selection to populate hidden fields
    const syncCreateFormulaStructure = () => {
        if (!createFormulaTemplate) {
            return;
        }

        const selectedOption = createFormulaTemplate.options[createFormulaTemplate.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            if (createFormulaStructureType) {
                createFormulaStructureType.value = '';
            }
            if (createFormulaStructureConfig) {
                createFormulaStructureConfig.value = '';
            }
            return;
        }

        const structureType = selectedOption.dataset.structureType || selectedOption.value || '';
        const structureJson = selectedOption.dataset.structure || '';

        if (createFormulaStructureType) {
            createFormulaStructureType.value = structureType;
        }

        if (createFormulaStructureConfig) {
            try {
                const payload = structureJson ? JSON.parse(structureJson) : null;
                createFormulaStructureConfig.value = payload ? JSON.stringify(payload) : '';
            } catch (error) {
                createFormulaStructureConfig.value = '';
            }
        }
    };

    createFormulaTemplate?.addEventListener('change', syncCreateFormulaStructure);
    syncCreateFormulaStructure();

    const syncCreateFormulaContext = () => {
        const contextType = createFormulaContext?.value ?? '';

        if (createFormulaContextSemester) {
            createFormulaContextSemester.classList.toggle('d-none', contextType !== 'semester');
            const semesterSelect = createFormulaContextSemester.querySelector('select');
            if (semesterSelect) {
                semesterSelect.required = contextType === 'semester';
            }
        }

        if (createFormulaContextYear) {
            createFormulaContextYear.classList.toggle('d-none', contextType !== 'academic_year');
            const yearInput = createFormulaContextYear.querySelector('input');
            if (yearInput) {
                yearInput.required = contextType === 'academic_year';
            }
        }
    };

    createFormulaContext?.addEventListener('change', syncCreateFormulaContext);
    syncCreateFormulaContext();

    // Reset form when modal is closed
    createFormulaModal?.addEventListener('hidden.bs.modal', function() {
        if (createFormulaForm) {
            createFormulaForm.reset();
        }
        if (createFormulaError) {
            createFormulaError.classList.add('d-none');
            createFormulaError.textContent = '';
        }
        if (createFormulaPassword) {
            createFormulaPassword.classList.remove('is-invalid');
        }
        if (createFormulaContextSemester) {
            createFormulaContextSemester.classList.add('d-none');
        }
        if (createFormulaContextYear) {
            createFormulaContextYear.classList.add('d-none');
        }
        if (createFormulaStructureType) {
            createFormulaStructureType.value = '';
        }
        if (createFormulaStructureConfig) {
            createFormulaStructureConfig.value = '';
        }
    });

    // Focus password when modal is shown
    createFormulaModal?.addEventListener('shown.bs.modal', function() {
        const formulaLabelInput = document.getElementById('create-formula-label');
        if (formulaLabelInput) {
            formulaLabelInput.focus();
        }
    });

    if (shouldReopenCreateFormulaModal && createFormulaModalInstance) {
        createFormulaModalInstance.show();
    }

    // Delete Formula Modal Logic
    const deleteFormulaModal = document.getElementById('delete-formula-modal');
    const deleteFormulaForm = document.getElementById('delete-formula-form');
    const deleteFormulaName = document.getElementById('delete-formula-name');
    const deleteFormulaError = document.getElementById('delete-formula-error');
    const deleteFormulaPassword = document.getElementById('delete-formula-password');
    const deleteButtons = document.querySelectorAll('.js-delete-formula');
    const deleteFormulaBaseUrl = pageData.deleteFormulaBaseUrl || '/admin/grades-formula/department';

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const formulaId = this.dataset.formulaId;
            const formulaLabel = this.dataset.formulaLabel || 'this formula';
            const departmentId = this.dataset.departmentId;

            if (deleteFormulaName) {
                deleteFormulaName.textContent = formulaLabel;
            }

            if (deleteFormulaForm && formulaId && departmentId) {
                const actionUrl = `${deleteFormulaBaseUrl}/${departmentId}/formulas/${formulaId}`;
                deleteFormulaForm.setAttribute('action', actionUrl);
            }
        });
    });

    // Reset delete form when modal is closed
    deleteFormulaModal?.addEventListener('hidden.bs.modal', function() {
        if (deleteFormulaForm) {
            deleteFormulaForm.reset();
        }
        if (deleteFormulaError) {
            deleteFormulaError.classList.add('d-none');
            deleteFormulaError.textContent = '';
        }
        if (deleteFormulaPassword) {
            deleteFormulaPassword.classList.remove('is-invalid');
        }
    });

    // Focus password when delete modal is shown
    deleteFormulaModal?.addEventListener('shown.bs.modal', function() {
        if (deleteFormulaPassword) {
            window.setTimeout(() => deleteFormulaPassword.focus(), 120);
        }
    });

    // Delete Global Formula Modal Logic
    const deleteGlobalFormulaModal = document.getElementById('delete-global-formula-modal');
    const deleteGlobalFormulaForm = document.getElementById('delete-global-formula-form');
    const deleteGlobalFormulaName = document.getElementById('delete-global-formula-name');
    const deleteGlobalFormulaError = document.getElementById('delete-global-formula-error');
    const deleteGlobalFormulaPassword = document.getElementById('delete-global-formula-password');
    const deleteGlobalButtons = document.querySelectorAll('.js-delete-global-formula');
    const deleteGlobalFormulaBaseUrl = pageData.deleteGlobalFormulaBaseUrl || '/admin/grades-formula';

    deleteGlobalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const formulaId = this.dataset.formulaId;
            const formulaLabel = this.dataset.formulaLabel || 'this formula';

            if (deleteGlobalFormulaName) {
                deleteGlobalFormulaName.textContent = formulaLabel;
            }

            if (deleteGlobalFormulaForm && formulaId) {
                const actionUrl = `${deleteGlobalFormulaBaseUrl}/${formulaId}`;
                deleteGlobalFormulaForm.setAttribute('action', actionUrl);
            }
        });
    });

    // Reset delete global form when modal is closed
    deleteGlobalFormulaModal?.addEventListener('hidden.bs.modal', function() {
        if (deleteGlobalFormulaForm) {
            deleteGlobalFormulaForm.reset();
        }
        if (deleteGlobalFormulaError) {
            deleteGlobalFormulaError.classList.add('d-none');
            deleteGlobalFormulaError.textContent = '';
        }
        if (deleteGlobalFormulaPassword) {
            deleteGlobalFormulaPassword.classList.remove('is-invalid');
        }
    });

    // Focus password when delete global modal is shown
    deleteGlobalFormulaModal?.addEventListener('shown.bs.modal', function() {
        if (deleteGlobalFormulaPassword) {
            window.setTimeout(() => deleteGlobalFormulaPassword.focus(), 120);
        }
    });

    // Create Structure Template Modal Logic
    initStructureTemplateManagement({
        structureTemplates,
        templateErrorMessages,
        oldTemplateInputs,
        oldTemplateComponents,
        shouldReopenTemplateModal,
        templateModalInitialMode,
        templateModalInitialEditId,
        templateDeleteReopenId,
        templateDeleteErrorMessage,
        templateUpdatePlaceholder
    });
}

/**
 * Initialize Structure Template Management
 */
function initStructureTemplateManagement(config) {
    const {
        structureTemplates,
        templateErrorMessages,
        oldTemplateInputs,
        oldTemplateComponents,
        shouldReopenTemplateModal,
        templateModalInitialMode,
        templateModalInitialEditId,
        templateDeleteReopenId,
        templateDeleteErrorMessage,
        templateUpdatePlaceholder
    } = config;

    const createTemplateModal = document.getElementById('create-template-modal');
    const createTemplateForm = document.getElementById('create-template-form');
    const componentsContainer = document.getElementById('components-container');
    const addComponentBtn = document.getElementById('add-component-btn');
    const weightWarning = document.getElementById('weight-warning');
    const totalWeightSpan = document.getElementById('total-weight');
    const templateKeyInput = document.getElementById('template-key');
    const templateLabelInput = document.getElementById('template-label');
    const templateDescriptionInput = document.getElementById('template-description');
    const templatePasswordHidden = document.getElementById('template-password-hidden');
    const templateErrorContainer = document.getElementById('template-error');
    const templatePasswordModal = document.getElementById('template-password-modal');
    const templatePasswordInput = document.getElementById('template-password-input');
    const templatePasswordConfirm = document.getElementById('template-password-confirm');
    const templatePasswordModalError = document.getElementById('template-password-modal-error');
    const createTemplateSubmitBtn = document.getElementById('create-template-submit');
    const templateMethodField = document.getElementById('template-method-field');
    const templateIdField = document.getElementById('template-id-field');
    const openCreateTemplateBtn = document.getElementById('open-create-template');
    const createTemplateModalLabel = document.getElementById('create-template-modal-label');
    const templatePasswordModalLabel = document.getElementById('template-password-modal-label');
    const deleteTemplateModal = document.getElementById('delete-structure-template-modal');
    const deleteTemplateForm = document.getElementById('delete-structure-template-form');
    const deleteTemplateName = document.getElementById('delete-template-name');
    const deleteTemplatePassword = document.getElementById('delete-template-password');
    const deleteTemplateError = document.getElementById('delete-template-error');
    const templateStoreAction = createTemplateForm?.dataset.storeAction ?? '';
    const templateUpdateActionPattern = createTemplateForm?.dataset.updateAction ?? '';
    const deleteTemplateActionPattern = deleteTemplateForm?.dataset.action ?? '';
    const templateModeState = {
        mode: templateModalInitialMode || 'create',
        editingId: templateModalInitialEditId ? String(templateModalInitialEditId) : null,
    };

    const editTemplateButtons = document.querySelectorAll('.js-edit-structure-template');
    const deleteTemplateButtons = document.querySelectorAll('.js-delete-structure-template');

    const bootstrapModalInstance = templatePasswordModal && window.bootstrap?.Modal ? new window.bootstrap.Modal(templatePasswordModal) : null;
    const deleteTemplateModalInstance = deleteTemplateModal && window.bootstrap?.Modal ? window.bootstrap.Modal.getOrCreateInstance(deleteTemplateModal) : null;

    let createTemplateModalInstance = null;
    if (createTemplateModal && window.bootstrap?.Modal) {
        createTemplateModalInstance = window.bootstrap.Modal.getOrCreateInstance(createTemplateModal);
    }

    let componentCounter = 0;

    openCreateTemplateBtn?.addEventListener('click', () => {
        applyTemplateMode('create');
        if (componentsContainer) {
            componentsContainer.innerHTML = '';
        }
        componentCounter = 0;
        renderTemplateErrors([]);
        updateWeightWarning();
        if (templatePasswordHidden) {
            templatePasswordHidden.value = '';
        }
        if (templatePasswordInput) {
            templatePasswordInput.value = '';
            templatePasswordInput.classList.remove('is-invalid');
        }
        if (templatePasswordModalError) {
            templatePasswordModalError.classList.add('d-none');
            templatePasswordModalError.textContent = '';
        }
    });

    const formatPercentValue = (value) => {
        const numeric = Number(value);
        if (!Number.isFinite(numeric)) {
            return '';
        }

        if (Number.isInteger(numeric)) {
            return numeric.toString();
        }

        const fixed = (Math.round(numeric * 100) / 100).toFixed(2);
        return fixed
            .replace(/(\.\d*?[1-9])0+$/, '$1')
            .replace(/\.00$/, '')
            .replace(/\.$/, '');
    };

    function convertStructureToComponentMap(structure) {
        const map = {};
        if (!structure || typeof structure !== 'object') {
            return map;
        }

        let counter = 1;
        const rootChildren = Array.isArray(structure.children) ? structure.children : [];

        rootChildren.forEach((child) => {
            const entry = typeof child === 'object' && child !== null ? child : {};
            const currentId = counter++;
            const childWeight = entry.weight_percent ?? ((entry.weight ?? 0) * 100);

            map[currentId] = {
                activity_type: entry.activity_type ?? entry.key ?? '',
                weight: formatPercentValue(childWeight),
                label: entry.label ?? '',
                is_main: 1,
            };

            const subChildren = Array.isArray(entry.children) ? entry.children : [];

            subChildren.forEach((subChild) => {
                const subEntry = typeof subChild === 'object' && subChild !== null ? subChild : {};
                const subId = counter++;
                const subWeight = subEntry.weight_percent ?? ((subEntry.weight ?? 0) * 100);

                map[subId] = {
                    activity_type: subEntry.activity_type ?? subEntry.key ?? '',
                    weight: formatPercentValue(subWeight),
                    label: subEntry.label ?? '',
                    parent_id: currentId,
                };
            });
        });

        return map;
    }

    function applyTemplateMode(mode, templateData = null, options = {}) {
        const preserveExistingValues = options.preserveExistingValues ?? false;

        templateModeState.mode = mode;
        templateModeState.editingId = templateData && templateData.id ? String(templateData.id) : null;

        if (!createTemplateForm) {
            return;
        }

        if (mode === 'edit' && templateData) {
            if (templateUpdateActionPattern) {
                createTemplateForm.action = templateUpdateActionPattern.replace(templateUpdatePlaceholder, templateModeState.editingId ?? '');
            }
            if (templateMethodField) {
                templateMethodField.disabled = false;
                templateMethodField.value = 'PUT';
            }
            if (templateIdField) {
                templateIdField.value = templateModeState.editingId ?? '';
            }
            if (!preserveExistingValues) {
                if (templateLabelInput) {
                    templateLabelInput.value = templateData.label ?? '';
                }
                if (templateDescriptionInput) {
                    templateDescriptionInput.value = templateData.description ?? '';
                }
            }
            if (templateKeyInput) {
                templateKeyInput.value = templateData.template_key ?? templateData.key ?? '';
                templateKeyInput.readOnly = true;
                templateKeyInput.classList.add('bg-light');
                templateKeyInput.dataset.userModified = 'true';
            }
            if (createTemplateModalLabel) {
                createTemplateModalLabel.textContent = 'Edit Structure Template';
            }
            if (createTemplateSubmitBtn) {
                createTemplateSubmitBtn.innerHTML = '<i class="bi bi-save me-1"></i>Save Changes';
            }
            if (templatePasswordModalLabel) {
                templatePasswordModalLabel.innerHTML = '<i class="bi bi-shield-lock me-2"></i>Confirm Template Update';
            }
            if (templatePasswordConfirm) {
                templatePasswordConfirm.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm and Update';
            }
        } else {
            if (templateStoreAction) {
                createTemplateForm.action = templateStoreAction;
            }
            if (templateMethodField) {
                templateMethodField.disabled = true;
                templateMethodField.value = 'PUT';
            }
            if (templateIdField) {
                templateIdField.value = '';
            }
            if (!preserveExistingValues) {
                if (templateLabelInput) {
                    templateLabelInput.value = '';
                }
                if (templateDescriptionInput) {
                    templateDescriptionInput.value = '';
                }
            }
            if (templateKeyInput) {
                templateKeyInput.readOnly = false;
                templateKeyInput.classList.remove('bg-light');
                if (!preserveExistingValues) {
                    templateKeyInput.value = '';
                }
                delete templateKeyInput.dataset.userModified;
            }
            if (createTemplateModalLabel) {
                createTemplateModalLabel.textContent = 'Create Structure Template';
            }
            if (createTemplateSubmitBtn) {
                createTemplateSubmitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Create Template';
            }
            if (templatePasswordModalLabel) {
                templatePasswordModalLabel.innerHTML = '<i class="bi bi-shield-lock me-2"></i>Confirm Template Creation';
            }
            if (templatePasswordConfirm) {
                templatePasswordConfirm.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm and Create';
            }
        }
    }

    function loadTemplateStructure(structure) {
        if (!componentsContainer) {
            return;
        }

        componentsContainer.innerHTML = '';
        componentCounter = 0;

        const componentMap = convertStructureToComponentMap(structure ?? {});
        if (Object.keys(componentMap).length === 0) {
            return;
        }

        restoreTemplateComponents(componentMap);
        updateWeightWarning();
    }

    function calculateTotalWeight() {
        // Only count main components (exclude sub-components)
        const mainComponents = document.querySelectorAll('.component-item[data-is-main="true"]');
        let total = 0;
        mainComponents.forEach(component => {
            const weightInput = component.querySelector('.component-weight');
            if (weightInput) {
                const value = parseFloat(weightInput.value) || 0;
                total += value;
            }
        });
        return Math.round(total * 10) / 10;
    }

    function updateWeightWarning() {
        const total = calculateTotalWeight();
        if (totalWeightSpan) {
            totalWeightSpan.textContent = total;
        }
        if (weightWarning) {
            if (Math.abs(total - 100) > 0.1) {
                weightWarning.style.display = 'block';
                if (total > 100) {
                    weightWarning.classList.remove('alert-warning');
                    weightWarning.classList.add('alert-danger');
                } else {
                    weightWarning.classList.remove('alert-danger');
                    weightWarning.classList.add('alert-warning');
                }
            } else {
                weightWarning.style.display = 'none';
            }
        }
    }

    function syncMainComponentMaxState(mainComponentId) {
        if (!mainComponentId) {
            return;
        }

        const mainComponent = document.querySelector(`.component-item[data-component-id="${mainComponentId}"][data-is-main="true"]`);
        if (!mainComponent) {
            return;
        }

        const maxInput = mainComponent.querySelector('.component-max-items');
        const helperText = mainComponent.querySelector('.component-max-helper');
        const subContainer = mainComponent.querySelector(`.subcomponents-container[data-parent-id="${mainComponentId}"]`);
        const hasSubComponents = subContainer ? subContainer.querySelectorAll('.component-item').length > 0 : false;

        if (!maxInput) {
            return;
        }

        maxInput.disabled = hasSubComponents;
        maxInput.classList.toggle('bg-light', hasSubComponents);
        maxInput.classList.toggle('text-muted', hasSubComponents);

        if (hasSubComponents) {
            maxInput.value = '';
            if (helperText) {
                helperText.textContent = 'Disabled when sub-components exist';
            }
        } else if (helperText) {
            helperText.textContent = 'Limit: 1-5';
        }
    }

    function addComponent(type = '', weight = '', label = '', isMain = true, parentId = null, maxItems = '') {
        componentCounter++;
        const currentId = componentCounter;
        const isSubComponent = !isMain;

        const componentHtml = `
            <div class="component-item card mb-3 ${isSubComponent ? 'ms-4 border-start border-3 border-primary' : ''}" data-component-id="${currentId}" data-is-main="${isMain}" data-parent-component="${parentId || ''}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-semibold ${isSubComponent ? 'text-secondary' : 'text-primary'}">
                            ${isSubComponent ? '<i class="bi bi-arrow-return-right me-1"></i>Sub-Component' : 'Main Component'} ${isSubComponent ? '' : currentId}
                        </h6>
                        <div>
                            ${!isSubComponent ? `
                            <button type="button" class="btn btn-sm btn-outline-primary me-1 add-subcomponent-btn" data-component-id="${currentId}" title="Add Sub-Component">
                                <i class="bi bi-plus-circle"></i> Sub-Component
                            </button>
                            ` : ''}
                            <button type="button" class="btn btn-sm btn-outline-danger remove-component-btn" data-component-id="${currentId}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Activity Type</label>
                            <input type="text" class="form-control form-control-sm component-activity-type" name="components[${currentId}][activity_type]" value="${type}" placeholder="e.g., Quiz, Exam, OCR" required>
                            ${!isSubComponent ? `<input type="hidden" name="components[${currentId}][is_main]" value="1">` : `<input type="hidden" name="components[${currentId}][parent_id]" value="${parentId}">`}
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Weight (%)</label>
                            <input type="number" class="form-control form-control-sm component-weight" name="components[${currentId}][weight]" value="${weight}" min="0" max="100" step="0.1" required>
                            ${!isSubComponent ? '<small class="text-muted">Main component weight</small>' : '<small class="text-muted">Sub-component weight (relative to parent)</small>'}
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Max Components</label>
                            <input type="number" class="form-control form-control-sm component-max-items" name="components[${currentId}][max_items]" value="${maxItems}" min="1" max="5" step="1" placeholder="1-5">
                            ${!isSubComponent ? '<small class="text-muted component-max-helper">Limit: 1-5</small>' : '<small class="text-muted">Limit: 1-5</small>'}
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Label</label>
                            <input type="text" class="form-control form-control-sm component-label" name="components[${currentId}][label]" value="${label}" placeholder="e.g., ${isSubComponent ? 'Lecture Quizzes' : 'Lecture Component'}" required>
                        </div>
                    </div>
                </div>
                <div class="subcomponents-container" data-parent-id="${currentId}"></div>
            </div>
        `;

        if (isSubComponent && parentId) {
            const parentContainer = document.querySelector(`.subcomponents-container[data-parent-id="${parentId}"]`);
            if (parentContainer) {
                parentContainer.insertAdjacentHTML('beforeend', componentHtml);
            }
        } else if (componentsContainer) {
            componentsContainer.insertAdjacentHTML('beforeend', componentHtml);
        }

        updateWeightWarning();

        const newComponent = document.querySelector(`.component-item[data-component-id="${currentId}"]`);
        if (!newComponent) {
            return currentId;
        }

        const weightInput = newComponent.querySelector('.component-weight');
        if (weightInput) {
            weightInput.addEventListener('input', updateWeightWarning);
        }

        const activityTypeInput = newComponent.querySelector('.component-activity-type');
        const labelInput = newComponent.querySelector('.component-label');
        if (activityTypeInput && labelInput) {
            activityTypeInput.addEventListener('input', function() {
                if (!labelInput.dataset.userModified) {
                    labelInput.value = this.value;
                }
            });
            labelInput.addEventListener('input', function() {
                if (this.value !== activityTypeInput.value) {
                    this.dataset.userModified = 'true';
                }
            });
        }

        const removeBtn = newComponent.querySelector('.remove-component-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const componentId = this.dataset.componentId;
                const component = document.querySelector(`.component-item[data-component-id="${componentId}"]`);
                if (component) {
                    const parentComponentId = component.dataset.parentComponent || '';
                    const isMainComponent = component.dataset.isMain === 'true';
                    component.remove();
                    updateWeightWarning();
                    if (!isMainComponent && parentComponentId) {
                        syncMainComponentMaxState(parentComponentId);
                    }
                }
            });
        }

        if (!isSubComponent) {
            const addSubBtn = newComponent.querySelector('.add-subcomponent-btn');
            if (addSubBtn) {
                addSubBtn.addEventListener('click', function() {
                    const parentIdValue = this.dataset.componentId;
                    addComponent('', '', '', false, parentIdValue);
                });
            }
        }

        if (isSubComponent && parentId) {
            syncMainComponentMaxState(parentId);
        } else {
            syncMainComponentMaxState(currentId);
        }

        return currentId;
    }

    function resolveComponentValue(component, key, fallback = '') {
        if (!component || typeof component !== 'object') {
            return fallback;
        }

        const value = component[key];
        return value === undefined || value === null ? fallback : value;
    }

    function isMainComponent(component) {
        if (!component || typeof component !== 'object') {
            return false;
        }

        const explicit = resolveComponentValue(component, 'is_main', null);
        if (explicit !== null && explicit !== '' && explicit !== false) {
            return explicit === true || explicit === 1 || explicit === '1';
        }

        const parentId = resolveComponentValue(component, 'parent_id', null);
        return parentId === null || parentId === '';
    }

    function renderTemplateErrors(messages) {
        if (!templateErrorContainer) {
            return;
        }

        templateErrorContainer.innerHTML = '';

        if (!Array.isArray(messages) || messages.length === 0) {
            templateErrorContainer.classList.add('d-none');
            return;
        }

        messages.forEach((message) => {
            const item = document.createElement('div');
            item.textContent = message;
            templateErrorContainer.appendChild(item);
        });

        templateErrorContainer.classList.remove('d-none');
    }

    function restoreTemplateComponents(oldComponents) {
        if (!oldComponents || typeof oldComponents !== 'object' || Object.keys(oldComponents).length === 0) {
            return false;
        }

        const entries = Object.entries(oldComponents);
        if (entries.length === 0) {
            return false;
        }

        const mainEntries = entries.filter(([, component]) => isMainComponent(component));
        const subEntries = entries.filter(([, component]) => !isMainComponent(component) && resolveComponentValue(component, 'parent_id', null) !== null);

        mainEntries.sort((a, b) => Number(a[0]) - Number(b[0]));
        subEntries.sort((a, b) => Number(a[0]) - Number(b[0]));

        const idMap = {};

        mainEntries.forEach(([oldId, component]) => {
            const newId = addComponent(
                resolveComponentValue(component, 'activity_type', ''),
                resolveComponentValue(component, 'weight', ''),
                resolveComponentValue(component, 'label', ''),
                true,
                null,
                resolveComponentValue(component, 'max_items', '')
            );
            idMap[oldId] = newId;
        });

        subEntries.forEach(([oldId, component]) => {
            const parentOldId = resolveComponentValue(component, 'parent_id', null);
            const parentNewId = parentOldId !== null ? idMap[parentOldId] : null;
            if (!parentNewId) {
                return;
            }

            addComponent(
                resolveComponentValue(component, 'activity_type', ''),
                resolveComponentValue(component, 'weight', ''),
                resolveComponentValue(component, 'label', ''),
                false,
                parentNewId,
                resolveComponentValue(component, 'max_items', '')
            );
        });

        updateWeightWarning();
        return true;
    }

    addComponentBtn?.addEventListener('click', () => addComponent());

    editTemplateButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const templateId = button.dataset.templateId;
            if (!templateId) {
                return;
            }

            const template = structureTemplates.find((entry) => String(entry.id) === String(templateId));
            if (!template) {
                return;
            }

            if (createTemplateForm) {
                createTemplateForm.reset();
            }

            applyTemplateMode('edit', template);
            renderTemplateErrors([]);
            if (templatePasswordHidden) {
                templatePasswordHidden.value = '';
            }
            if (templatePasswordInput) {
                templatePasswordInput.value = '';
                templatePasswordInput.classList.remove('is-invalid');
            }
            if (templatePasswordModalError) {
                templatePasswordModalError.classList.add('d-none');
                templatePasswordModalError.textContent = '';
            }
            loadTemplateStructure(template.structure);

            if (createTemplateModalInstance) {
                createTemplateModalInstance.show();
            }
        });
    });

    deleteTemplateButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const templateId = button.dataset.templateId;
            if (!templateId) {
                return;
            }

            const template = structureTemplates.find((entry) => String(entry.id) === String(templateId));
            if (!template) {
                return;
            }

            if (deleteTemplateForm && deleteTemplateActionPattern) {
                deleteTemplateForm.action = deleteTemplateActionPattern.replace(templateUpdatePlaceholder, templateId);
            }

            if (deleteTemplateName) {
                deleteTemplateName.textContent = template.label ?? 'Structure Template';
            }

            if (deleteTemplatePassword) {
                deleteTemplatePassword.value = '';
                deleteTemplatePassword.classList.remove('is-invalid');
            }

            if (deleteTemplateError) {
                deleteTemplateError.textContent = '';
            }

            if (deleteTemplateModalInstance) {
                deleteTemplateModalInstance.show();
            }
        });
    });

    deleteTemplateModal?.addEventListener('hidden.bs.modal', () => {
        if (deleteTemplateForm) {
            deleteTemplateForm.reset();
        }
        if (deleteTemplateError) {
            deleteTemplateError.textContent = '';
        }
        deleteTemplatePassword?.classList.remove('is-invalid');
    });

    deleteTemplateModal?.addEventListener('shown.bs.modal', () => {
        window.setTimeout(() => deleteTemplatePassword?.focus(), 120);
    });

    templateLabelInput?.addEventListener('input', function() {
        if (templateKeyInput && !templateKeyInput.dataset.userModified) {
            const key = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
            templateKeyInput.value = key;
        }
    });

    templateKeyInput?.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });

    if (createTemplateModal) {
        createTemplateModal.addEventListener('shown.bs.modal', function() {
            if (componentsContainer && componentsContainer.children.length === 0) {
                let restored = false;
                if (shouldReopenTemplateModal) {
                    restored = restoreTemplateComponents(oldTemplateComponents);
                }

                if (!restored) {
                    addComponent('quiz', '40', 'Quizzes');
                    addComponent('exam', '40', 'Exam');
                    addComponent('ocr', '20', 'Other Course Requirements');
                }
            }
            if (shouldReopenTemplateModal) {
                if (templateLabelInput) {
                    const labelValue = oldTemplateInputs.label || '';
                    templateLabelInput.value = labelValue;
                }

                if (templateKeyInput) {
                    const keyValue = oldTemplateInputs.key || '';
                    if (keyValue !== '') {
                        templateKeyInput.value = keyValue;
                        templateKeyInput.dataset.userModified = 'true';
                    }
                }

                if (templateDescriptionInput) {
                    const descriptionValue = oldTemplateInputs.description || '';
                    templateDescriptionInput.value = descriptionValue;
                }

                renderTemplateErrors(templateErrorMessages);
            }

            templateLabelInput?.focus();
        });

        createTemplateModal.addEventListener('hidden.bs.modal', function() {
            applyTemplateMode('create');
            if (createTemplateForm) {
                createTemplateForm.reset();
            }
            if (componentsContainer) {
                componentsContainer.innerHTML = '';
            }
            componentCounter = 0;
            if (templateKeyInput) {
                delete templateKeyInput.dataset.userModified;
            }
            if (templatePasswordHidden) {
                templatePasswordHidden.value = '';
            }
            renderTemplateErrors([]);
            updateWeightWarning();
        });
    }

    if (shouldReopenTemplateModal && createTemplateModalInstance) {
        if (templateModalInitialMode === 'edit' && templateModalInitialEditId) {
            const template = structureTemplates.find((entry) => String(entry.id) === String(templateModalInitialEditId));
            applyTemplateMode('edit', template || null, { preserveExistingValues: true });
        } else {
            applyTemplateMode('create', null, { preserveExistingValues: true });
        }

        createTemplateModalInstance.show();
    }

    if (templateDeleteReopenId && deleteTemplateModalInstance) {
        const template = structureTemplates.find((entry) => String(entry.id) === String(templateDeleteReopenId));

        if (deleteTemplateForm && deleteTemplateActionPattern) {
            deleteTemplateForm.action = deleteTemplateActionPattern.replace(templateUpdatePlaceholder, templateDeleteReopenId);
        }

        if (deleteTemplateName) {
            deleteTemplateName.textContent = template?.label ?? 'Structure Template';
        }

        if (deleteTemplatePassword) {
            deleteTemplatePassword.value = '';
            if (templateDeleteErrorMessage) {
                deleteTemplatePassword.classList.add('is-invalid');
            }
        }

        if (deleteTemplateError) {
            deleteTemplateError.textContent = templateDeleteErrorMessage ?? '';
        }

        deleteTemplateModalInstance.show();
    }

    // Handle "Create Template" button click - show password modal
    createTemplateSubmitBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validate the form first
        if (createTemplateForm && !createTemplateForm.checkValidity()) {
            createTemplateForm.reportValidity();
            return;
        }

        // Check weight total
        const total = calculateTotalWeight();
        if (Math.abs(total - 100) > 0.1) {
            alert('Total weight must equal 100%. Current total: ' + total + '%');
            return;
        }

        // Show password modal
        if (bootstrapModalInstance) {
            bootstrapModalInstance.show();
        }
    });

    // Handle password modal confirmation
    templatePasswordConfirm?.addEventListener('click', function() {
        const password = templatePasswordInput?.value || '';
        
        if (!password) {
            templatePasswordInput?.classList.add('is-invalid');
            return;
        }

        templatePasswordInput?.classList.remove('is-invalid');

        // Set password in hidden field
        if (templatePasswordHidden) {
            templatePasswordHidden.value = password;
        }

        // Hide password modal
        if (bootstrapModalInstance) {
            bootstrapModalInstance.hide();
        }

        // Submit the form
        if (createTemplateForm) {
            createTemplateForm.submit();
        }
    });

    // Focus password input when modal is shown
    templatePasswordModal?.addEventListener('shown.bs.modal', function() {
        window.setTimeout(() => templatePasswordInput?.focus(), 120);
    });

    // Clear password input when modal is hidden
    templatePasswordModal?.addEventListener('hidden.bs.modal', function() {
        if (templatePasswordInput) {
            templatePasswordInput.value = '';
            templatePasswordInput.classList.remove('is-invalid');
        }
        if (templatePasswordModalError) {
            templatePasswordModalError.classList.add('d-none');
            templatePasswordModalError.textContent = '';
        }
    });

    // Clear error on password input
    templatePasswordInput?.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        if (templatePasswordModalError) {
            templatePasswordModalError.classList.add('d-none');
        }
    });
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaWildcards);

// Expose function globally
window.initGradesFormulaWildcards = initGradesFormulaWildcards;
