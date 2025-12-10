/**
 * Import Students Page JavaScript
 * Handles Excel upload, cross-checking, and student import functionality
 */

// Enhanced Alert System
export function showAlert(message, type = 'success', duration = 3000) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alertId = 'alert-' + Date.now();
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert-floating alert alert-${type} alert-dismissible fade`;
    alert.id = alertId;
    
    // Set icon based on type
    let icon = '';
    switch(type) {
        case 'success':
            icon = 'bi-check-circle-fill';
            break;
        case 'danger':
            icon = 'bi-x-circle-fill';
            break;
        case 'warning':
            icon = 'bi-exclamation-circle-fill';
            break;
        default:
            icon = 'bi-info-circle-fill';
    }
    
    // Create alert content
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <span class="alert-icon">
                <i class="bi ${icon}"></i>
            </span>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="alert-progress">
            <div class="alert-progress-bar"></div>
        </div>
    `;
    
    // Add to container
    alertContainer.appendChild(alert);
    
    // Show alert with animation
    setTimeout(() => {
        alert.classList.add('show');
        const progressBar = alert.querySelector('.alert-progress-bar');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.style.transitionDuration = duration + 'ms';
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 50);
        }
    }, 10);
    
    // Auto dismiss
    const dismissTimeout = setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 300);
    }, duration);
    
    // Clear timeout if manually closed
    alert.querySelector('.btn-close')?.addEventListener('click', () => {
        clearTimeout(dismissTimeout);
    });
}

// Replace the old showToast function with showAlert
export function showToast(message, type = 'success') {
    showAlert(message, type);
}

function extractNameParts(fullName) {
    const parts = fullName.split(' ').filter(Boolean);
    const first = parts[0] ?? '';
    const last = parts[parts.length - 1] ?? '';
    return (first + last).toLowerCase();
}

function showCheckboxes() {
    document.querySelectorAll('.checkbox-column').forEach(col => {
        col.style.display = '';
    });
}

function hideCheckboxes() {
    document.querySelectorAll('.checkbox-column').forEach(col => {
        col.style.display = 'none';
    });
    // Reset all checkboxes
    document.querySelectorAll('.student-checkbox, #selectAll').forEach(checkbox => {
        checkbox.checked = false;
    });
    // Update counts
    updateSelectedCount();
}

export function filterList(selected) {
    hideCheckboxes(); // Hide checkboxes when changing list
    const url = new URL(window.location.href);
    url.searchParams.set('list_name', selected);
    window.location.href = url.toString();
}

function updateSelectedCount() {
    // Count only enabled and checked checkboxes
    const selectedCount = document.querySelectorAll('.student-checkbox:not(:disabled):checked').length;
    const countBadge = document.getElementById('selectedCount');
    const importBtnCount = document.getElementById('importBtnCount');
    const modalSelectedCount = document.getElementById('modalSelectedCount');
    const importBtn = document.getElementById('importBtn');
    
    // Update counts
    if (countBadge) countBadge.textContent = `${selectedCount} Selected`;
    if (importBtnCount) importBtnCount.textContent = selectedCount;
    if (modalSelectedCount) modalSelectedCount.textContent = selectedCount;
    
    // Update import button state
    if (importBtn) {
        importBtn.disabled = selectedCount === 0;
        
        // Update button appearance
        if (selectedCount === 0) {
            importBtn.classList.add('btn-secondary');
            importBtn.classList.remove('btn-success');
        } else {
            importBtn.classList.add('btn-success');
            importBtn.classList.remove('btn-secondary');
        }
    }
    // Update import button enabled state whenever selected count changes
    updateImportButtonState();
}

function updateCrossCheckButton() {
    const listFilter = document.getElementById('listFilter');
    const compareSubject = document.getElementById('compareSubjectSelect');
    const crossCheckBtn = document.getElementById('crossCheckBtn');
    
    if (crossCheckBtn) {
        const isEnabled = listFilter?.value && compareSubject?.value;
        crossCheckBtn.disabled = !isEnabled;
        
        // Update button appearance
        if (isEnabled) {
            crossCheckBtn.classList.remove('btn-secondary');
            crossCheckBtn.classList.add('btn-success');
        } else {
            crossCheckBtn.classList.remove('btn-success');
            crossCheckBtn.classList.add('btn-secondary');
            crossCheckBtn.innerHTML = `
                <i class="bi bi-search"></i>
                <span>Cross Check Data</span>
            `;
        }
    }
}

function updateImportButtonState() {
    const compareSubject = document.getElementById('compareSubjectSelect');
    const importBtn = document.getElementById('importBtn');
    const selectedCount = document.querySelectorAll('.student-checkbox:not(:disabled):checked').length;
    const enabled = selectedCount > 0 && compareSubject && compareSubject.value;
    if (importBtn) {
        importBtn.disabled = !enabled;
        if (!enabled) {
            importBtn.classList.remove('btn-success');
            importBtn.classList.add('btn-secondary');
        } else {
            importBtn.classList.add('btn-success');
            importBtn.classList.remove('btn-secondary');
        }
    }
}

export function runCrossCheck() {
    const listFilter = document.getElementById('listFilter');
    const compareSubject = document.getElementById('compareSubjectSelect');
    const crossCheckBtn = document.getElementById('crossCheckBtn');
    
    // Validate both selections
    if (!listFilter?.value) {
        showAlert('Please select an uploaded list to compare', 'warning');
        listFilter?.focus();
        hideCheckboxes();
        return;
    }
    
    if (!compareSubject?.value) {
        showAlert('Please select a subject to compare with', 'warning');
        compareSubject?.focus();
        hideCheckboxes();
        return;
    }
    
    // Show checkboxes when starting cross-check
    showCheckboxes();

    // Show status bar
    const statusBar = document.getElementById('crossCheckStatus');
    if (statusBar) statusBar.classList.remove('d-none');
    
    // Show loading overlays
    document.getElementById('uploadedLoading')?.classList.add('show');
    document.getElementById('existingLoading')?.classList.add('show');
    
    // Disable cross check button and show spinner
    const originalBtnContent = crossCheckBtn?.innerHTML || '';
    if (crossCheckBtn) {
        crossCheckBtn.disabled = true;
        crossCheckBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm"></span>
            <span>Checking...</span>
        `;
    }

    setTimeout(() => {
        const uploadedRows = document.querySelectorAll('.uploaded-row');
        const enrolledRows = document.querySelectorAll('.enrolled-row');

        if (uploadedRows.length === 0) {
            showAlert('No students found in the selected list', 'warning');
            if (crossCheckBtn) {
                crossCheckBtn.disabled = false;
                crossCheckBtn.innerHTML = originalBtnContent;
            }
            document.getElementById('uploadedLoading')?.classList.remove('show');
            document.getElementById('existingLoading')?.classList.remove('show');
            if (statusBar) statusBar.classList.add('d-none');
            return;
        }

        const enrolledData = [...enrolledRows].map(row => ({
            row,
            nameKey: extractNameParts(row.dataset.fullName || ''),
            course: row.dataset.course?.trim(),
            year: row.dataset.year?.trim(),
            nameCell: row.querySelector('.student-name'),
            courseCell: row.querySelector('.student-course'),
            yearCell: row.querySelector('.student-year')
        }));

        // Reset all styling while keeping rows visible
        [...uploadedRows, ...enrolledRows].forEach(row => {
            // Remove all highlight classes
            row.classList.remove(
                'highlight-success', 'highlight-danger',
                'table-row-transition'
            );
            row.style.display = ''; // Ensure row is visible
            
            // Reset cell styling while maintaining visibility
            row.querySelectorAll('td').forEach(cell => {
                cell.classList.remove('text-danger', 'text-success');
                cell.style.opacity = '1';
                cell.style.display = ''; // Ensure cell is visible
            });
            
            // Reset checkbox state
            const checkbox = row.querySelector('.student-checkbox');
            if (checkbox) {
                checkbox.disabled = false;
                checkbox.checked = false; // Uncheck the checkbox
                checkbox.style.display = ''; // Ensure checkbox is visible
            }
            
            // Update the selected count
            updateSelectedCount();
        });

        let matchCount = 0;
        let newCount = 0;

        uploadedRows.forEach(row => {
            const nameKey = extractNameParts(row.dataset.fullName || '');
            const course = row.dataset.course?.trim();
            const year = row.dataset.year?.trim();

            const nameCell = row.querySelector('.student-name');
            const courseCell = row.querySelector('.student-course');
            const yearCell = row.querySelector('.student-year');
            const checkbox = row.querySelector('.student-checkbox');

            let matched = false;

            enrolledData.forEach(e => {
                if (e.nameKey === nameKey && e.course === course && e.year === year) {
                    // Style for duplicate entries with smooth animation
                    row.classList.add('highlight-danger', 'table-row-transition');
                    [nameCell, courseCell, yearCell].forEach(el => {
                        if (el) {
                            el.classList.add('text-danger');
                            el.style.opacity = '1';
                        }
                    });
                    if (checkbox) checkbox.disabled = true;

                    // Style matching row in existing students table
                    e.row.classList.add('highlight-danger', 'table-row-transition');
                    [e.nameCell, e.courseCell, e.yearCell].forEach(el => {
                        if (el) {
                            el.classList.add('text-danger');
                            el.style.opacity = '1';
                        }
                    });
                    matched = true;
                    matchCount++;
                }
            });

            if (!matched) {
                // Style for new entries with smooth animation
                row.classList.add('highlight-success', 'table-row-transition');
                [nameCell, courseCell, yearCell].forEach(el => {
                    if (el) el.classList.add('text-success');
                });
                newCount++;
            }
        });

        // Update status bar
        const matchStatus = document.getElementById('matchStatus');
        if (matchStatus) {
            matchStatus.textContent = `Found ${newCount} new students and ${matchCount} existing students`;
        }

        // Hide loading overlays
        document.getElementById('uploadedLoading')?.classList.remove('show');
        document.getElementById('existingLoading')?.classList.remove('show');

        // Reset cross check button
        if (crossCheckBtn) {
            crossCheckBtn.disabled = false;
            crossCheckBtn.innerHTML = originalBtnContent;
        }

        // Hide status bar after a delay
        setTimeout(() => {
            if (statusBar) statusBar.classList.add('d-none');
        }, 3000);
    }, 500);
}

export function initImportStudentsPage() {
    // File upload handling
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('file');
            if (!fileInput?.files.length) {
                showAlert('Please select an Excel file to upload', 'warning');
                return;
            }

            const file = fileInput.files[0];
            if (!file.name.match(/\.(xlsx|xls)$/i)) {
                showAlert('Please select a valid Excel file (.xlsx or .xls)', 'warning');
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm"></span>
                    <span>Uploading...</span>
                `;
            }

            // Submit the form
            this.submit();
        });
    }

    // Compare subject change handler
    const compareSubjectSelect = document.getElementById('compareSubjectSelect');
    if (compareSubjectSelect) {
        compareSubjectSelect.addEventListener('change', function () {
            hideCheckboxes(); // Hide checkboxes when changing subject
            const url = new URL(window.location.href);
            url.searchParams.set('compare_subject_id', this.value);
            window.location.href = url.toString();
        });
    }

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(tooltip);
        }
    });

    // Select all checkbox handling
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.student-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = selectAll.checked;
                    cb.closest('tr')?.classList.toggle('table-active', selectAll.checked);
                }
            });
            updateSelectedCount();
        });
    }

    // Individual checkbox handling
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            this.closest('tr')?.classList.toggle('table-active', this.checked);
            updateSelectedCount();
        });
    });

    // Add event listeners for the filters
    const listFilter = document.getElementById('listFilter');
    if (listFilter) {
        listFilter.addEventListener('change', updateCrossCheckButton);
    }
    if (compareSubjectSelect) {
        compareSubjectSelect.addEventListener('change', updateCrossCheckButton);
        compareSubjectSelect.addEventListener('change', updateImportButtonState);
    }

    // Cross check button handler
    const crossCheckBtn = document.getElementById('crossCheckBtn');
    if (crossCheckBtn) {
        crossCheckBtn.addEventListener('click', runCrossCheck);
    }

    // Confirm form submit handling
    const confirmForm = document.getElementById('confirmForm');
    if (confirmForm) {
        confirmForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get all enabled and checked checkboxes
            const selected = [...document.querySelectorAll('.student-checkbox:not(:disabled):checked')].map(cb => cb.value);
            
            if (selected.length === 0) {
                showAlert('Please select at least one student to import', 'warning');
                return;
            }

            // Ensure confirm subject ID is set (comes from Compare with Subject dropdown)
            const confirmSubjectId = this.querySelector('input[name="subject_id"]');
            if (!confirmSubjectId || !confirmSubjectId.value) {
                showAlert('Please select a target subject via the "Compare with Subject" dropdown before importing.', 'warning');
                return;
            }
            
            // Set the selected student IDs and submit
            const selectedStudentIds = document.getElementById('selectedStudentIds');
            if (selectedStudentIds) {
                selectedStudentIds.value = selected.join(',');
            }
            this.submit();
        });
    }

    // Import button click handler
    const importBtn = document.getElementById('importBtn');
    if (importBtn) {
        importBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const selected = [...document.querySelectorAll('.student-checkbox:not(:disabled):checked')].map(cb => cb.value);
            if (selected.length === 0) {
                showAlert('Please select at least one student to import', 'warning');
                return;
            }
            const compareSubject = document.getElementById('compareSubjectSelect');
            if (!compareSubject || !compareSubject.value) {
                showAlert('Please select a target subject using the "Compare with Subject" dropdown before importing', 'warning');
                compareSubject?.focus();
                return;
            }

            // Populate hidden inputs in the confirm form
            const selectedStudentIds = document.getElementById('selectedStudentIds');
            const confirmSubjectId = document.getElementById('confirmSubjectId');
            const confirmSubjectLabel = document.getElementById('confirmSubjectLabel');
            const confirmStudentCount = document.getElementById('confirmStudentCount');
            
            if (selectedStudentIds) selectedStudentIds.value = selected.join(',');
            if (confirmSubjectId) confirmSubjectId.value = compareSubject.value;
            
            // Show subject label in modal
            const selectedOption = compareSubject.options[compareSubject.selectedIndex];
            if (confirmSubjectLabel) {
                confirmSubjectLabel.textContent = selectedOption ? selectedOption.text : '';
            }
            // Show selected count in modal
            if (confirmStudentCount) {
                confirmStudentCount.textContent = `${selected.length} student(s) will be imported`;
            }

            // Populate preview list (show first 10 names)
            const preview = document.getElementById('confirmSelectedList');
            if (preview) {
                preview.innerHTML = '';
                const maxPreview = 10;
                selected.slice(0, maxPreview).forEach(id => {
                    const cb = document.querySelector(`.student-checkbox[value="${id}"]`);
                    const tr = cb ? cb.closest('tr') : null;
                    const name = tr ? tr.querySelector('.student-name')?.textContent.trim() : id;
                    const li = document.createElement('div');
                    li.className = 'list-group-item px-0';
                    li.textContent = name || id;
                    preview.appendChild(li);
                });
                if (selected.length === 0) {
                    const li = document.createElement('div');
                    li.className = 'list-group-item px-0 text-muted';
                    li.textContent = 'No students selected';
                    preview.appendChild(li);
                } else if (selected.length > maxPreview) {
                    const more = document.createElement('div');
                    more.className = 'list-group-item px-0 text-muted';
                    more.textContent = `+ ${selected.length - maxPreview} more...`;
                    preview.appendChild(more);
                }
            }

            // Show the modal programmatically
            if (typeof window.modal !== 'undefined' && window.modal.open) {
                window.modal.open('confirmModal');
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalEl = document.getElementById('confirmModal');
                if (modalEl) {
                    const bsModal = new bootstrap.Modal(modalEl);
                    bsModal.show();
                }
            }
        });
    }

    // Clear preview list when modal hides
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
        confirmModal.addEventListener('hidden.bs.modal', function () {
            const preview = document.getElementById('confirmSelectedList');
            if (preview) {
                preview.innerHTML = '<div class="list-group-item px-0 text-muted">No students selected</div>';
            }
            const confirmStudentCount = document.getElementById('confirmStudentCount');
            const confirmSubjectLabel = document.getElementById('confirmSubjectLabel');
            const confirmSubjectId = document.getElementById('confirmSubjectId');
            
            if (confirmStudentCount) confirmStudentCount.textContent = '-';
            if (confirmSubjectLabel) confirmSubjectLabel.textContent = '-';
            if (confirmSubjectId) confirmSubjectId.value = '';
        });
    }

    // Add event listeners for checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.matches('.student-checkbox') || e.target.matches('#selectAll')) {
            updateSelectedCount();
        }
    });

    // Initialize page state
    hideCheckboxes(); // Ensure checkboxes are hidden on page load
    updateSelectedCount();
    updateCrossCheckButton();
    updateImportButtonState();

    // Handle server-side alerts on page load
    const serverAlerts = document.querySelectorAll('.alert:not(.alert-floating)');
    serverAlerts.forEach(alert => {
        const message = alert.innerText.trim();
        const type = alert.classList.contains('alert-success') ? 'success' : 'danger';
        if (message) {
            showAlert(message, type);
        }
        alert.remove();
    });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('uploadForm') || document.getElementById('crossCheckBtn')) {
        initImportStudentsPage();
    }
});

// Export for global use
window.showAlert = showAlert;
window.showToast = showToast;
window.filterList = filterList;
window.runCrossCheck = runCrossCheck;
window.initImportStudentsPage = initImportStudentsPage;
