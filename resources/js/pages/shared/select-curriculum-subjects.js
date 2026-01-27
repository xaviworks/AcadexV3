/**
 * Shared Select Curriculum Subjects Page JavaScript
 * Used by both Chairperson and GE Coordinator portals
 */

export function initSelectCurriculumSubjectsPage(options = {}) {
  // Page data should be set by Blade: window.pageData = { currentSemester, userRole }
  const pageData = window.pageData || {};
  const currentSemester = pageData.currentSemester || options.currentSemester || '';
  const userRole = pageData.userRole || options.userRole || 0;
  const isChairperson = userRole === 1;
  const isGECoordinator = userRole === 4;

  const curriculumSelect = document.getElementById('curriculumSelect');
  const subjectsContainer = document.getElementById('subjectsContainer');
  const subjectsTableBody = document.getElementById('subjectsTableBody');
  const formCurriculumId = document.getElementById('formCurriculumId');
  const loadBtnSpinner = document.getElementById('loadBtnSpinner');
  const yearTabs = document.getElementById('yearTabs');
  const selectAllBtn = document.getElementById('selectAllBtn');
  const selectedCountEl = document.getElementById('selectedCount');

  if (!curriculumSelect || !subjectsContainer) return;

  curriculumSelect.addEventListener('change', function () {
    console.log('Curriculum changed:', this.value);
    subjectsContainer.classList.add('d-none');
    if (yearTabs) yearTabs.innerHTML = '';
    if (subjectsTableBody) subjectsTableBody.innerHTML = '';

    // Automatically load subjects when curriculum is selected
    if (this.value) {
      console.log('Loading subjects for curriculum:', this.value);
      loadSubjects();
    }
  });

  function loadSubjects() {
    const curriculumId = curriculumSelect.value;
    console.log('loadSubjects called with curriculumId:', curriculumId);
    if (!curriculumId) return;

    if (formCurriculumId) formCurriculumId.value = curriculumId;
    if (yearTabs) yearTabs.innerHTML = '';
    if (subjectsTableBody) subjectsTableBody.innerHTML = '';

    // Disable select and show spinner
    curriculumSelect.disabled = true;
    if (loadBtnSpinner) {
      console.log('Showing spinner');
      loadBtnSpinner.classList.remove('d-none');
    }

    const url = `/curriculum/${curriculumId}/fetch-subjects`;
    console.log('Fetching from URL:', url);

    fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.length) {
          if (yearTabs) yearTabs.innerHTML = '';
          if (subjectsTableBody) {
            // Use different styling based on context
            if (isChairperson) {
              subjectsTableBody.innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p class="mb-0">No courses found for this curriculum.</p>
                            </div>
                        `;
            } else {
              subjectsTableBody.innerHTML = '<div class="text-muted text-center">No subjects found.</div>';
            }
          }
          subjectsContainer.classList.remove('d-none');
          return;
        }

        const grouped = {};
        data.forEach((subj) => {
          // Only include subjects for the current semester
          if (subj.semester !== currentSemester) return;

          const key = `year${subj.year_level}`;
          if (!grouped[key]) grouped[key] = [];
          grouped[key].push(subj);
        });

        let tabIndex = 0;
        for (const [key, subjects] of Object.entries(grouped)) {
          const year = key.replace('year', '');
          const yearLabels = { 1: '1st Year', 2: '2nd Year', 3: '3rd Year', 4: '4th Year' };
          const isActive = tabIndex === 0 ? 'active' : '';

          if (yearTabs) {
            yearTabs.insertAdjacentHTML(
              'beforeend',
              `
                        <li class="nav-item">
                            <button class="nav-link ${isActive}" style="color: #198754; font-weight: 500;" data-bs-toggle="tab" data-bs-target="#tab-${key}" type="button" role="tab">${yearLabels[year]}</button>
                        </li>
                    `
            );
          }

          const rows = subjects
            .map((s) => {
              // For GE Coordinator, disable checkboxes for non-GE subjects
              // For Chairperson, disable checkboxes for GE, PD, PE, RS, NSTP subjects
              // Also disable if already imported
              let isDisabled = false;
              let disabledReason = '';

              if (s.already_imported) {
                isDisabled = true;
                disabledReason = 'already-imported';
              } else if (isGECoordinator && !s.is_universal) {
                isDisabled = true; // GE Coordinator can only select GE subjects
                disabledReason = 'restricted';
              } else if (isChairperson && s.is_restricted) {
                isDisabled = true; // Chairperson cannot select restricted subjects
                disabledReason = 'restricted';
              }

              // Row class - only light background for already imported, no opacity for restricted
              const rowClass = s.already_imported ? 'table-light' : '';
              // Text class - muted for disabled items
              const textClass = isDisabled ? 'text-muted' : '';

              // Already imported indicator
              const importedIndicator = s.already_imported
                ? '<span class="badge bg-success bg-opacity-75 ms-2" style="font-size: 0.7rem; font-weight: 500;"><i class="bi bi-check2-circle me-1"></i>Already Added</span>'
                : '';

              // Checkbox or checkmark for the select column
              let selectCell;
              if (s.already_imported) {
                selectCell = '<i class="bi bi-check-circle-fill text-success" title="Already added to subjects"></i>';
              } else if (isDisabled) {
                // Disabled - show dash for restricted subjects
                selectCell = '<span class="text-muted" title="Managed by another coordinator">—</span>';
              } else {
                selectCell = `<input type="checkbox" class="form-check-input subject-checkbox" name="subject_ids[]" value="${s.id}" data-year="${s.year_level}" data-semester="${s.semester}" style="border: 2px solid #198754; cursor: pointer;">`;
              }

              // Same table layout for both roles
              return `
                <tr class="${rowClass}">
                    <td class="text-center">${selectCell}</td>
                    <td class="${textClass}"><strong>${s.subject_code}</strong>${importedIndicator}</td>
                    <td class="${textClass}">${s.subject_description}</td>
                    <td class="text-center ${textClass}">${s.year_level}</td>
                    <td class="text-center ${textClass}">${s.semester}</td>
                </tr>
              `;
            })
            .join('');

          // Same table structure for both roles
          const table = `
            <h5 class="mt-4 text-success fw-semibold">
                <i class="bi bi-calendar3 me-2"></i>${currentSemester} Semester
            </h5>
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-success">
                    <tr>
                        <th style="width: 80px;" class="text-center">
                            <div class="d-flex align-items-center justify-content-center m-0">
                                <input type="checkbox" class="form-check-input m-0" id="selectAllBtn" data-selected="false" style="width: 20px; height: 20px; cursor: pointer; border: 2px solid #198754;" title="Select/Unselect All">
                            </div>
                        </th>
                        <th style="width: 180px;">Course Code</th>
                        <th>Description</th>
                        <th style="width: 80px;" class="text-center">Year</th>
                        <th style="width: 100px;" class="text-center">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
          `;

          if (subjectsTableBody) {
            subjectsTableBody.insertAdjacentHTML(
              'beforeend',
              `
                        <div class="tab-pane fade ${isActive ? 'show active' : ''}" id="tab-${key}" role="tabpanel">
                            ${table}
                        </div>
                    `
            );
          }

          tabIndex++;
        }

        subjectsContainer.classList.remove('d-none');
        updateSelectedCount();
      })
      .catch(() => {
        if (subjectsTableBody) {
          if (isChairperson) {
            subjectsTableBody.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-exclamation-triangle text-danger"></i>
                            <p class="text-danger mb-0">Failed to load courses. Please try again.</p>
                        </div>
                    `;
          } else {
            subjectsTableBody.innerHTML = '<div class="text-danger text-center">Failed to load subjects.</div>';
          }
        }
        subjectsContainer.classList.remove('d-none');
      })
      .finally(() => {
        // Re-enable select and hide spinner
        curriculumSelect.disabled = false;
        if (loadBtnSpinner) loadBtnSpinner.classList.add('d-none');
      });
  }

  // Select/Unselect All Handler
  document.addEventListener('change', function (e) {
    if (e.target.id === 'selectAllBtn' && e.target.type === 'checkbox') {
      const checkbox = e.target;
      const allSelected = checkbox.checked;
      checkbox.dataset.selected = allSelected;

      // Only select enabled checkboxes
      document.querySelectorAll('.subject-checkbox').forEach((cb) => {
        if (cb.disabled) {
          cb.checked = false; // Keep disabled checkboxes unchecked
        } else {
          cb.checked = allSelected;
        }
      });

      updateSelectedCount();
    }
  });

  // Get the open modal button
  const openConfirmModalBtn = document.getElementById('openConfirmModalBtn');

  function updateSelectedCount() {
    const count = document.querySelectorAll('.subject-checkbox:checked').length;
    if (selectedCountEl) {
      selectedCountEl.textContent = count;
    }

    // Enable/disable confirm button based on selection count
    if (openConfirmModalBtn) {
      openConfirmModalBtn.disabled = count === 0;
      if (count === 0) {
        openConfirmModalBtn.classList.add('btn-secondary');
        openConfirmModalBtn.classList.remove('btn-success');
      } else {
        openConfirmModalBtn.classList.remove('btn-secondary');
        openConfirmModalBtn.classList.add('btn-success');
      }
    }
  }

  // Listen for checkbox changes
  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('subject-checkbox')) {
      updateSelectedCount();
    }
  });

  // Handle confirm button click - validate before opening modal
  if (openConfirmModalBtn) {
    openConfirmModalBtn.addEventListener('click', function () {
      const count = document.querySelectorAll('.subject-checkbox:checked').length;
      if (count === 0) {
        // Show warning notification
        if (window.notify) {
          window.notify.warning('Please select at least one course to import.');
        } else {
          alert('Please select at least one course to import.');
        }
        return;
      }

      // Open the modal
      const confirmModal = document.getElementById('confirmModal');
      if (confirmModal && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(confirmModal);
        modal.show();
      }
    });
  }

  // Confirm Modal Submission
  const submitConfirmBtn = document.getElementById('submitConfirmBtn');
  const confirmForm = document.getElementById('confirmForm');

  if (submitConfirmBtn && confirmForm) {
    submitConfirmBtn.addEventListener('click', function () {
      confirmForm.submit();
    });
  }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('curriculumSelect') && document.getElementById('confirmForm')) {
    // Check if we're on the select curriculum subjects page
    const pageDataExists =
      typeof window.pageData !== 'undefined' &&
      (window.pageData.currentSemester !== undefined || window.pageData.userRole !== undefined);

    // Also check for legacy global variables
    const legacyDataExists = typeof window.currentSemester !== 'undefined' || typeof window.userRole !== 'undefined';

    if (pageDataExists || legacyDataExists) {
      // Support legacy global variables
      if (legacyDataExists && !pageDataExists) {
        window.pageData = {
          currentSemester: window.currentSemester || '',
          userRole: window.userRole || 0,
        };
      }
      initSelectCurriculumSubjectsPage();
    }
  }
});

window.initSelectCurriculumSubjectsPage = initSelectCurriculumSubjectsPage;
