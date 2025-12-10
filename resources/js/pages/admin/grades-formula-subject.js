/**
 * Admin Grades Formula Subject Page JavaScript
 * Handles formula selection, filtering, password modals, and override confirmation
 */

export function initGradesFormulaSubject() {
    const applyForm = document.getElementById('subject-formula-apply-form');
    if (!applyForm) {
        return;
    }

    const optionInputs = applyForm.querySelectorAll('.formula-option-input');
    const applyButton = applyForm.querySelector('[data-action="apply"]');
    const structureFilter = document.getElementById('department-structure-filter');
    const formulaColumns = applyForm.querySelectorAll('.formula-card-column');
    const passwordHiddenField = document.getElementById('subjectFormulaPasswordField');
    const passwordServerError = document.getElementById('subjectFormulaPasswordServerError');
    const requiresPassword = applyForm.dataset.requiresPassword === '1';
    const passwordModalElement = document.getElementById('subjectFormulaPasswordModal');
    const passwordInput = document.getElementById('subjectFormulaPasswordInput');
    const passwordInlineError = document.getElementById('subjectFormulaPasswordInlineError');
    const passwordConfirmBtn = document.getElementById('subjectFormulaPasswordConfirmBtn');
    const modalCtor = window.bootstrap && typeof window.bootstrap.Modal === 'function' ? window.bootstrap.Modal : null;
    let passwordModalInstance = null;

    const syncSelectionState = () => {
        let hasSelection = false;

        optionInputs.forEach((input) => {
            const card = input.nextElementSibling;
            const selected = input.checked;

            if (card) {
                card.classList.toggle('is-selected', selected);
                if (selected) {
                    card.classList.add('pulse');
                }
            }

            if (selected) {
                hasSelection = true;
            }
        });

        if (applyButton) {
            applyButton.disabled = !hasSelection;
        }
    };

    syncSelectionState();

    optionInputs.forEach((input) => {
        const card = input.nextElementSibling;

        input.addEventListener('change', () => {
            syncSelectionState();
            delete applyForm.dataset.overrideConfirmed;
        });

        if (card) {
            card.addEventListener('animationend', () => {
                card.classList.remove('pulse');
            });
        }
    });

    const applyStructureFilter = () => {
        if (!structureFilter) {
            return;
        }

        const selectedType = structureFilter.value;

        formulaColumns.forEach((column) => {
            const matches = selectedType === 'all' || column.dataset.structureType === selectedType;
            column.classList.toggle('d-none', !matches);

            if (!matches) {
                const input = column.querySelector('.formula-option-input');
                if (input && input.checked) {
                    input.checked = false;
                }
            }
        });

        syncSelectionState();
        delete applyForm.dataset.overrideConfirmed;
    };

    if (structureFilter) {
        structureFilter.addEventListener('change', applyStructureFilter);
        applyStructureFilter();
    }

    const hasSubjectFormula = applyForm.dataset.hasSubjectFormula === '1';

    const setServerErrorMessage = (message) => {
        if (!passwordServerError) {
            return;
        }

        if (message) {
            passwordServerError.textContent = message;
            passwordServerError.classList.remove('d-none');
        } else {
            passwordServerError.textContent = '';
            passwordServerError.classList.add('d-none');
        }
    };

    let existingServerError = applyForm.dataset.passwordError === '1'
        ? (applyForm.dataset.passwordErrorMessage || '')
        : '';

    setServerErrorMessage(existingServerError);

    const ensureOverrideConfirmation = () => {
        if (!hasSubjectFormula) {
            return true;
        }

        if (applyForm.dataset.overrideConfirmed === '1') {
            return true;
        }

        const subjectName = applyForm.dataset.subjectName || 'this subject';
        const message = `This will replace the existing custom formula for ${subjectName}. Continue?`;
        const confirmed = window.confirm(message);
        if (confirmed) {
            applyForm.dataset.overrideConfirmed = '1';
        }
        return confirmed;
    };

    if (requiresPassword) {
        if (modalCtor && passwordModalElement && passwordConfirmBtn) {
            passwordModalInstance = modalCtor.getOrCreateInstance(passwordModalElement);

            const resetInlineError = () => {
                if (passwordInlineError) {
                    passwordInlineError.textContent = '';
                    passwordInlineError.classList.add('d-none');
                }
                if (passwordInput) {
                    passwordInput.classList.remove('is-invalid');
                }
            };

            const showInlineError = (message) => {
                if (!passwordInlineError) {
                    return;
                }
                passwordInlineError.textContent = message;
                passwordInlineError.classList.remove('d-none');
                if (passwordInput) {
                    passwordInput.classList.add('is-invalid');
                }
            };

            const openModal = () => {
                resetInlineError();
                setServerErrorMessage(existingServerError);
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.focus();
                }
                passwordModalInstance.show();
            };

            applyForm.addEventListener('submit', (event) => {
                if (applyForm.dataset.passwordBypass === '1' || event.defaultPrevented) {
                    return;
                }

                if (!ensureOverrideConfirmation()) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();
                openModal();
            });

            passwordConfirmBtn.addEventListener('click', () => {
                const value = passwordInput ? passwordInput.value.trim() : '';
                resetInlineError();

                if (!value) {
                    showInlineError('Password is required.');
                    if (passwordInput) {
                        passwordInput.focus();
                    }
                    return;
                }

                if (passwordHiddenField) {
                    passwordHiddenField.value = value;
                }
                applyForm.dataset.passwordError = '0';
                applyForm.dataset.passwordErrorMessage = '';
                setServerErrorMessage('');
                existingServerError = '';
                applyForm.dataset.passwordBypass = '1';
                passwordModalInstance.hide();

                setTimeout(() => {
                    applyForm.requestSubmit();
                    setTimeout(() => {
                        delete applyForm.dataset.passwordBypass;
                        if (passwordHiddenField) {
                            passwordHiddenField.value = '';
                        }
                    }, 0);
                }, 150);
            });

            passwordModalElement.addEventListener('hidden.bs.modal', () => {
                if (applyForm.dataset.passwordBypass === '1') {
                    return;
                }

                if (passwordHiddenField) {
                    passwordHiddenField.value = '';
                }
                resetInlineError();
            });

            if (applyForm.dataset.passwordError === '1' && existingServerError) {
                setTimeout(() => {
                    passwordModalInstance.show();
                    if (passwordInput) {
                        passwordInput.focus();
                    }
                }, 200);
            }
        } else if (passwordHiddenField) {
            applyForm.addEventListener('submit', (event) => {
                if (applyForm.dataset.passwordBypass === '1' || event.defaultPrevented) {
                    return;
                }

                if (!ensureOverrideConfirmation()) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();
                const response = window.prompt('Enter your password to confirm this change:');
                if (!response || !response.trim()) {
                    return;
                }

                passwordHiddenField.value = response.trim();
                applyForm.dataset.passwordError = '0';
                applyForm.dataset.passwordErrorMessage = '';
                existingServerError = '';
                applyForm.dataset.passwordBypass = '1';
                applyForm.requestSubmit();
                setTimeout(() => {
                    delete applyForm.dataset.passwordBypass;
                    passwordHiddenField.value = '';
                }, 0);
            });
        }
    } else if (hasSubjectFormula) {
        applyForm.addEventListener('submit', (event) => {
            if (!ensureOverrideConfirmation()) {
                event.preventDefault();
            }
        });
    }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaSubject);

// Expose function globally
window.initGradesFormulaSubject = initGradesFormulaSubject;
