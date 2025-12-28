/**
 * Instructor - Manage Students Page JavaScript
 *
 * Handles:
 * - Student drop/manage modals
 * - File upload for Excel imports
 * - Cross-check functionality for comparing uploaded lists with enrolled students
 * - Import confirmation and student selection
 */

/**
 * Handle subject change dropdown - redirects to filtered view
 * @param {HTMLSelectElement} select - The subject select element
 */
function handleSubjectChange(select) {
  if (select.value === '') {
    // Use a data attribute or global config for the base route
    const baseUrl = select.getAttribute('data-base-url') || '/instructor/students';
    window.location.href = baseUrl;
  } else {
    select.form.submit();
  }
}

/**
 * Ensure the drop form action is valid before submission
 * @param {HTMLFormElement} form - The drop form
 * @returns {boolean} True if valid, false otherwise
 */
function ensureDropFormActionSet(form) {
  const action = form.getAttribute('action') || '';
  const match = action.match(/\/instructor\/students\/([^\/]+)\/drop$/);
  if (!match) {
    alert('Unable to determine the student to drop. Please re-open the Drop dialog and try again.');
    return false;
  }
  return true;
}

/**
 * Ensure the manage form action is valid before submission
 * @param {HTMLFormElement} form - The manage form
 * @returns {boolean} True if valid, false otherwise
 */
function ensureManageFormActionSet(form) {
  const action = form.getAttribute('action') || '';
  const match = action.match(/\/instructor\/students\/([^\/]+)\/update$/);
  if (!match) {
    alert('Unable to determine the student to update. Please re-open the Manage dialog and try again.');
    return false;
  }
  return true;
}

/**
 * Show a floating alert notification
 * @param {string} message - The message to display
 * @param {string} type - Alert type (success, danger, warning, info)
 * @param {number} duration - How long to show the alert in ms
 */
function showAlert(message, type = 'success', duration = 3000) {
  const alertContainer = document.getElementById('alertContainer');
  if (!alertContainer) return;

  const alertId = 'alert-' + Date.now();
  const alert = document.createElement('div');
  alert.className = `alert-floating alert alert-${type} alert-dismissible fade`;
  alert.id = alertId;

  // Set icon based on type
  const icons = {
    success: 'bi-check-circle-fill',
    danger: 'bi-x-circle-fill',
    warning: 'bi-exclamation-circle-fill',
    info: 'bi-info-circle-fill',
  };
  const icon = icons[type] || icons.info;

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

  alertContainer.appendChild(alert);

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

  const dismissTimeout = setTimeout(() => {
    alert.classList.remove('show');
    setTimeout(() => alert.remove(), 300);
  }, duration);

  alert.querySelector('.btn-close')?.addEventListener('click', () => {
    clearTimeout(dismissTimeout);
  });
}

// Alias for backwards compatibility
function showToast(message, type = 'success') {
  showAlert(message, type);
}

/**
 * Extract name parts for comparison
 * @param {string} fullName - Full name string
 * @returns {string} Lowercase first+last name key
 */
function extractNameParts(fullName) {
  const parts = fullName.split(' ').filter(Boolean);
  const first = parts[0] ?? '';
  const last = parts[parts.length - 1] ?? '';
  return (first + last).toLowerCase();
}

/**
 * Show checkbox columns in the table
 */
function showCheckboxes() {
  document.querySelectorAll('.checkbox-column').forEach((col) => {
    col.style.display = '';
  });
}

/**
 * Hide checkbox columns and reset checkboxes
 */
function hideCheckboxes() {
  document.querySelectorAll('.checkbox-column').forEach((col) => {
    col.style.display = 'none';
  });
  document.querySelectorAll('.student-checkbox, #selectAll').forEach((checkbox) => {
    checkbox.checked = false;
  });
  updateSelectedCount();
}

/**
 * Update the selected student count display
 */
function updateSelectedCount() {
  const selectedCount = document.querySelectorAll('.student-checkbox:not(:disabled):checked').length;
  const countBadge = document.getElementById('selectedCount');
  const importBtnCount = document.getElementById('importBtnCount');
  const modalSelectedCount = document.getElementById('modalSelectedCount');
  const importBtn = document.getElementById('importBtn');

  if (countBadge) countBadge.textContent = `${selectedCount} Selected`;
  if (importBtnCount) importBtnCount.textContent = selectedCount;
  if (modalSelectedCount) modalSelectedCount.textContent = selectedCount;

  if (importBtn) {
    importBtn.disabled = selectedCount === 0;
    importBtn.classList.toggle('btn-secondary', selectedCount === 0);
    importBtn.classList.toggle('btn-success', selectedCount > 0);
  }

  updateImportButtonState();
}

/**
 * Update cross-check button state based on selections
 */
function updateCrossCheckButton() {
  const listFilter = document.getElementById('listFilter');
  const compareSubject = document.getElementById('compareSubjectSelect');
  const crossCheckBtn = document.getElementById('crossCheckBtn');

  if (crossCheckBtn) {
    const isEnabled = listFilter?.value && compareSubject?.value;
    crossCheckBtn.disabled = !isEnabled;

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

/**
 * Update import button state based on selections
 */
function updateImportButtonState() {
  const compareSubject = document.getElementById('compareSubjectSelect');
  const importBtn = document.getElementById('importBtn');
  const selectedCount = document.querySelectorAll('.student-checkbox:not(:disabled):checked').length;
  const enabled = selectedCount > 0 && compareSubject?.value;

  if (importBtn) {
    importBtn.disabled = !enabled;
    importBtn.classList.toggle('btn-secondary', !enabled);
    importBtn.classList.toggle('btn-success', enabled);
  }
}

/**
 * Filter the uploaded list
 * @param {string} selected - Selected list name
 */
function filterList(selected) {
  hideCheckboxes();
  const url = new URL(window.location.href);
  url.searchParams.set('list_name', selected);
  url.searchParams.set('tab', 'import');
  window.location.href = url.toString();
}

/**
 * Run cross-check comparison between uploaded list and enrolled students
 */
function runCrossCheck() {
  const listFilter = document.getElementById('listFilter');
  const compareSubject = document.getElementById('compareSubjectSelect');
  const crossCheckBtn = document.getElementById('crossCheckBtn');

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

  showCheckboxes();

  const statusBar = document.getElementById('crossCheckStatus');
  statusBar?.classList.remove('d-none');

  document.getElementById('uploadedLoading')?.classList.add('show');
  document.getElementById('existingLoading')?.classList.add('show');

  const originalBtnContent = crossCheckBtn?.innerHTML;
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
      statusBar?.classList.add('d-none');
      return;
    }

    const enrolledData = [...enrolledRows].map((row) => ({
      row,
      nameKey: extractNameParts(row.dataset.fullName || ''),
      course: row.dataset.course?.trim(),
      year: row.dataset.year?.trim(),
      nameCell: row.querySelector('.student-name'),
      courseCell: row.querySelector('.student-course'),
      yearCell: row.querySelector('.student-year'),
    }));

    // Reset all styling
    [...uploadedRows, ...enrolledRows].forEach((row) => {
      row.classList.remove('highlight-success', 'highlight-danger', 'table-row-transition');
      row.style.display = '';

      row.querySelectorAll('td').forEach((cell) => {
        cell.classList.remove('text-danger', 'text-success');
        cell.style.opacity = '1';
        cell.style.display = '';
      });

      const checkbox = row.querySelector('.student-checkbox');
      if (checkbox) {
        checkbox.disabled = false;
        checkbox.checked = false;
        checkbox.style.display = '';
      }
    });
    updateSelectedCount();

    let matchCount = 0;
    let newCount = 0;

    uploadedRows.forEach((row) => {
      const nameKey = extractNameParts(row.dataset.fullName || '');
      const course = row.dataset.course?.trim();
      const year = row.dataset.year?.trim();

      const nameCell = row.querySelector('.student-name');
      const courseCell = row.querySelector('.student-course');
      const yearCell = row.querySelector('.student-year');
      const checkbox = row.querySelector('.student-checkbox');

      let matched = false;

      enrolledData.forEach((e) => {
        if (e.nameKey === nameKey && e.course === course && e.year === year) {
          row.classList.add('highlight-danger', 'table-row-transition');
          [nameCell, courseCell, yearCell].forEach((el) => {
            if (el) {
              el.classList.add('text-danger');
              el.style.opacity = '1';
            }
          });
          if (checkbox) checkbox.disabled = true;

          e.row.classList.add('highlight-danger', 'table-row-transition');
          [e.nameCell, e.courseCell, e.yearCell].forEach((el) => {
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
        row.classList.add('highlight-success', 'table-row-transition');
        [nameCell, courseCell, yearCell].forEach((el) => {
          if (el) el.classList.add('text-success');
        });
        newCount++;
      }
    });

    const matchStatus = document.getElementById('matchStatus');
    if (matchStatus) {
      matchStatus.textContent = `Found ${newCount} new students and ${matchCount} existing students`;
    }

    document.getElementById('uploadedLoading')?.classList.remove('show');
    document.getElementById('existingLoading')?.classList.remove('show');

    if (crossCheckBtn) {
      crossCheckBtn.disabled = false;
      crossCheckBtn.innerHTML = originalBtnContent;
    }

    setTimeout(() => {
      statusBar?.classList.add('d-none');
    }, 3000);
  }, 500);
}

/**
 * Initialize the manage students page
 */
function initManageStudentsPage() {
  // Drop modal handling
  const dropModal = document.getElementById('confirmDropModal');
  const dropConfirmation = document.getElementById('dropConfirmation');
  const confirmDropBtn = document.getElementById('confirmDropBtn');

  // Fallback click handlers for drop buttons
  document.querySelectorAll('button[data-bs-target="#confirmDropModal"]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const dropUrl = btn.getAttribute('data-drop-url');
      const studentId = btn.getAttribute('data-student-id');
      const form = document.getElementById('dropStudentForm');
      if (form) {
        form.action = dropUrl || `/instructor/students/${studentId}/drop`;
      }
    });
  });

  if (dropModal) {
    dropModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      if (!button) return;

      const studentId = button.getAttribute('data-student-id');
      const dropUrl = button.getAttribute('data-drop-url');
      const studentName = button.getAttribute('data-student-name') || '';
      const form = dropModal.querySelector('#dropStudentForm');
      const placeholder = dropModal.querySelector('#studentNamePlaceholder');

      if (form) {
        form.action = dropUrl || (studentId ? `/instructor/students/${studentId}/drop` : '');
      }
      if (placeholder) placeholder.textContent = studentName;
      if (dropConfirmation) {
        dropConfirmation.value = '';
      }
      if (confirmDropBtn) {
        confirmDropBtn.disabled = true;
      }
    });
  }

  if (dropConfirmation && confirmDropBtn) {
    dropConfirmation.addEventListener('input', function () {
      confirmDropBtn.disabled = this.value.toLowerCase() !== 'drop';
    });
  }

  // Manage student modal handling
  const manageModal = document.getElementById('manageStudentModal');

  // Fallback click handlers for manage buttons
  document.querySelectorAll('button[data-bs-target="#manageStudentModal"]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const updateUrl = btn.getAttribute('data-update-url');
      const studentId = btn.getAttribute('data-student-id');
      const firstName = btn.getAttribute('data-student-first-name');
      const lastName = btn.getAttribute('data-student-last-name');
      const yearLevel = btn.getAttribute('data-student-year-level');

      const form = document.getElementById('manageStudentForm');
      if (form) {
        form.action = updateUrl || `/instructor/students/${studentId}/update`;
      }

      const firstNameInput = document.getElementById('manage_first_name');
      const lastNameInput = document.getElementById('manage_last_name');
      const yearLevelSelect = document.getElementById('manage_year_level');

      if (firstNameInput) firstNameInput.value = firstName || '';
      if (lastNameInput) lastNameInput.value = lastName || '';
      if (yearLevelSelect) yearLevelSelect.value = yearLevel || '';
    });
  });

  if (manageModal) {
    manageModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      if (!button) {
        console.error('No button found as relatedTarget');
        return;
      }

      const studentId = button.getAttribute('data-student-id');
      const updateUrl = button.getAttribute('data-update-url');
      const firstName = button.getAttribute('data-student-first-name');
      const lastName = button.getAttribute('data-student-last-name');
      const yearLevel = button.getAttribute('data-student-year-level');

      const form = manageModal.querySelector('#manageStudentForm');
      if (form) {
        form.action = updateUrl || `/instructor/students/${studentId}/update`;
      }

      const firstNameInput = document.getElementById('manage_first_name');
      const lastNameInput = document.getElementById('manage_last_name');
      const yearLevelSelect = document.getElementById('manage_year_level');

      if (firstNameInput) firstNameInput.value = firstName || '';
      if (lastNameInput) lastNameInput.value = lastName || '';
      if (yearLevelSelect) yearLevelSelect.value = yearLevel || '';
    });
  }

  // File upload handling
  document.getElementById('uploadForm')?.addEventListener('submit', function (e) {
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

    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
      const originalContent = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm"></span>
                <span>Uploading...</span>
            `;
    }

    this.submit();
  });

  // Compare subject change handler
  document.getElementById('compareSubjectSelect')?.addEventListener('change', function () {
    hideCheckboxes();
    const url = new URL(window.location.href);
    url.searchParams.set('compare_subject_id', this.value);
    url.searchParams.set('tab', 'import');
    window.location.href = url.toString();
  });

  // Initialize tooltips
  const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltips.forEach((tooltip) => new bootstrap.Tooltip(tooltip));

  // Select all checkbox handling
  const selectAll = document.getElementById('selectAll');
  const checkboxes = document.querySelectorAll('.student-checkbox');

  if (selectAll) {
    selectAll.addEventListener('change', function () {
      checkboxes.forEach((cb) => {
        if (!cb.disabled) {
          cb.checked = selectAll.checked;
          cb.closest('tr')?.classList.toggle('table-active', selectAll.checked);
        }
      });
      updateSelectedCount();
    });
  }

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', function () {
      this.closest('tr')?.classList.toggle('table-active', this.checked);
      updateSelectedCount();
    });
  });

  // List filter and cross-check button listeners
  document.getElementById('listFilter')?.addEventListener('change', updateCrossCheckButton);
  document.getElementById('compareSubjectSelect')?.addEventListener('change', () => {
    updateCrossCheckButton();
    updateImportButtonState();
  });

  // Confirm form submission handling
  document.getElementById('confirmForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const selected = [...document.querySelectorAll('.student-checkbox:not(:disabled):checked')].map((cb) => cb.value);

    if (selected.length === 0) {
      showAlert('Please select at least one student to import', 'warning');
      return;
    }

    const confirmSubjectId = this.querySelector('input[name="subject_id"]');
    if (!confirmSubjectId?.value) {
      showAlert('Please select a target subject via the "Compare with Subject" dropdown before importing.', 'warning');
      return;
    }

    const selectedStudentIds = document.getElementById('selectedStudentIds');
    if (selectedStudentIds) {
      selectedStudentIds.value = selected.join(',');
    }
    this.submit();
  });

  // Import button click handler
  document.getElementById('importBtn')?.addEventListener('click', function (e) {
    e.preventDefault();
    const selected = [...document.querySelectorAll('.student-checkbox:not(:disabled):checked')].map((cb) => cb.value);

    if (selected.length === 0) {
      showAlert('Please select at least one student to import', 'warning');
      return;
    }

    const compareSubject = document.getElementById('compareSubjectSelect');
    if (!compareSubject?.value) {
      showAlert('Please select a target subject using the "Compare with Subject" dropdown before importing', 'warning');
      compareSubject?.focus();
      return;
    }

    const selectedStudentIds = document.getElementById('selectedStudentIds');
    const confirmSubjectId = document.getElementById('confirmSubjectId');
    const confirmSubjectLabel = document.getElementById('confirmSubjectLabel');
    const confirmStudentCount = document.getElementById('confirmStudentCount');

    if (selectedStudentIds) selectedStudentIds.value = selected.join(',');
    if (confirmSubjectId) confirmSubjectId.value = compareSubject.value;

    const selectedOption = compareSubject.options[compareSubject.selectedIndex];
    if (confirmSubjectLabel) confirmSubjectLabel.textContent = selectedOption?.text || '';
    if (confirmStudentCount) confirmStudentCount.textContent = `${selected.length} student(s) will be imported`;

    // Populate preview list
    const preview = document.getElementById('confirmSelectedList');
    if (preview) {
      preview.innerHTML = '';
      const maxPreview = 10;
      selected.slice(0, maxPreview).forEach((id) => {
        const cb = document.querySelector(`.student-checkbox[value="${id}"]`);
        const tr = cb?.closest('tr');
        const name = tr?.querySelector('.student-name')?.textContent.trim() || id;
        const li = document.createElement('div');
        li.className = 'list-group-item px-0';
        li.textContent = name;
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

    // Show the modal
    if (typeof window.modal !== 'undefined') {
      window.modal.open('confirmModal');
    } else {
      const modalEl = document.getElementById('confirmModal');
      if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
      }
    }
  });

  // Clear preview list when modal hides
  document.getElementById('confirmModal')?.addEventListener('hidden.bs.modal', function () {
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

  // Tab persistence
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get('tab');
  if (tab === 'import') {
    const tabTrigger = document.querySelector('#import-tab');
    if (tabTrigger) {
      const bsTab = new bootstrap.Tab(tabTrigger);
      bsTab.show();
    }
  }

  // Initialize states
  hideCheckboxes();
  updateCrossCheckButton();
  updateImportButtonState();
  updateSelectedCount();
}

// Export functions for global access
window.handleSubjectChange = handleSubjectChange;
window.ensureDropFormActionSet = ensureDropFormActionSet;
window.ensureManageFormActionSet = ensureManageFormActionSet;
window.showAlert = showAlert;
window.showToast = showToast;
window.extractNameParts = extractNameParts;
window.showCheckboxes = showCheckboxes;
window.hideCheckboxes = hideCheckboxes;
window.updateSelectedCount = updateSelectedCount;
window.updateCrossCheckButton = updateCrossCheckButton;
window.updateImportButtonState = updateImportButtonState;
window.filterList = filterList;
window.runCrossCheck = runCrossCheck;
window.initManageStudentsPage = initManageStudentsPage;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initManageStudentsPage);
