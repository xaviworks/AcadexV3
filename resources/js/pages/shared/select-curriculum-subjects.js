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
    const loadSubjectsBtn = document.getElementById('loadSubjectsBtn');
    const subjectsContainer = document.getElementById('subjectsContainer');
    const subjectsTableBody = document.getElementById('subjectsTableBody');
    const formCurriculumId = document.getElementById('formCurriculumId');
    const loadBtnText = document.getElementById('loadBtnText');
    const loadBtnSpinner = document.getElementById('loadBtnSpinner');
    const yearTabs = document.getElementById('yearTabs');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const selectedCountEl = document.getElementById('selectedCount');

    if (!curriculumSelect || !subjectsContainer) return;

    curriculumSelect.addEventListener('change', function () {
        if (loadSubjectsBtn) {
            loadSubjectsBtn.classList.toggle('d-none', !this.value);
        }
        subjectsContainer.classList.add('d-none');
        if (yearTabs) yearTabs.innerHTML = '';
        if (subjectsTableBody) subjectsTableBody.innerHTML = '';
    });

    if (loadSubjectsBtn) {
        loadSubjectsBtn.addEventListener('click', loadSubjects);
    }

    function loadSubjects() {
        const curriculumId = curriculumSelect.value;
        if (!curriculumId) return;

        if (formCurriculumId) formCurriculumId.value = curriculumId;
        if (yearTabs) yearTabs.innerHTML = '';
        if (subjectsTableBody) subjectsTableBody.innerHTML = '';
        
        if (loadSubjectsBtn) loadSubjectsBtn.disabled = true;
        if (loadBtnText) loadBtnText.classList.add('d-none');
        if (loadBtnSpinner) loadBtnSpinner.classList.remove('d-none');

        fetch(`/curriculum/${curriculumId}/fetch-subjects`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
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
            data.forEach(subj => {
                // Only include subjects for the current semester
                if (subj.semester !== currentSemester) return;

                const key = `year${subj.year_level}`;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(subj);
            });

            let tabIndex = 0;
            for (const [key, subjects] of Object.entries(grouped)) {
                const year = key.replace('year', '');
                const yearLabels = { '1': '1st Year', '2': '2nd Year', '3': '3rd Year', '4': '4th Year' };
                const isActive = tabIndex === 0 ? 'active' : '';

                if (yearTabs) {
                    yearTabs.insertAdjacentHTML('beforeend', `
                        <li class="nav-item">
                            <button class="nav-link ${isActive}" style="color: #198754; font-weight: 500;" data-bs-toggle="tab" data-bs-target="#tab-${key}" type="button" role="tab">${yearLabels[year]}</button>
                        </li>
                    `);
                }

                const rows = subjects.map(s => {
                    // For GE Coordinator, disable checkboxes for non-GE subjects
                    // For Chairperson, disable checkboxes for GE, PD, PE, RS, NSTP subjects
                    let isDisabled = false;
                    if (isGECoordinator && !s.is_universal) {
                        isDisabled = true; // GE Coordinator can only select GE subjects
                    } else if (isChairperson && s.is_restricted) {
                        isDisabled = true; // Chairperson cannot select restricted subjects
                    }
                    const disabledAttr = isDisabled ? 'disabled' : '';
                    const disabledClass = isDisabled ? 'opacity-50' : '';
                    
                    // Use different table layouts for chairperson vs ge coordinator
                    if (isChairperson) {
                        return `
                            <tr class="${disabledClass}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input subject-checkbox" name="subject_ids[]" value="${s.id}" data-year="${s.year_level}" data-semester="${s.semester}" ${disabledAttr}>
                                </td>
                                <td><strong>${s.subject_code}</strong></td>
                                <td>${s.subject_description}</td>
                                <td class="text-center">${s.year_level}</td>
                                <td class="text-center">${s.semester}</td>
                            </tr>
                        `;
                    } else {
                        return `
                            <tr class="${disabledClass}">
                                <td><input type="checkbox" class="form-check-input subject-checkbox" name="subject_ids[]" value="${s.id}" data-year="${s.year_level}" data-semester="${s.semester}" ${disabledAttr}></td>
                                <td>${s.subject_code}</td>
                                <td>${s.subject_description}</td>
                                <td>${s.year_level}</td>
                                <td>${s.semester}</td>
                            </tr>
                        `;
                    }
                }).join('');

                let table;
                if (isChairperson) {
                    table = `
                        <h6 class="semester-heading">
                            <i class="bi bi-calendar3 me-2"></i>${currentSemester} Semester
                        </h6>
                        <div class="table-container">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;" class="text-center">Select</th>
                                        <th style="width: 150px;">Course Code</th>
                                        <th>Description</th>
                                        <th style="width: 100px;" class="text-center">Year</th>
                                        <th style="width: 120px;" class="text-center">Semester</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows}
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    table = `
                        <h5 class="mt-4 text-success">${currentSemester} Semester</h5>
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-success">
                                <tr>
                                    <th></th>
                                    <th>Course Code</th>
                                    <th>Description</th>
                                    <th>Year</th>
                                    <th>Semester</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    `;
                }

                if (subjectsTableBody) {
                    subjectsTableBody.insertAdjacentHTML('beforeend', `
                        <div class="tab-pane fade ${isActive ? 'show active' : ''}" id="tab-${key}" role="tabpanel">
                            ${table}
                        </div>
                    `);
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
            if (loadSubjectsBtn) loadSubjectsBtn.disabled = false;
            if (loadBtnText) loadBtnText.classList.remove('d-none');
            if (loadBtnSpinner) loadBtnSpinner.classList.add('d-none');
        });
    }

    // Select/Unselect All Handler
    document.addEventListener('click', function (e) {
        if (e.target.closest('#selectAllBtn')) {
            const btn = e.target.closest('#selectAllBtn');
            let allSelected = btn.dataset.selected === 'true';
            allSelected = !allSelected;
            btn.dataset.selected = allSelected;
            
            // Only select enabled checkboxes
            document.querySelectorAll('.subject-checkbox').forEach(cb => {
                if (cb.disabled) {
                    cb.checked = false; // Keep disabled checkboxes unchecked
                } else {
                    cb.checked = allSelected;
                }
            });
            
            // Toggle button styling
            if (isChairperson) {
                if (allSelected) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            } else {
                btn.classList.toggle('btn-outline-success', !allSelected);
                btn.classList.toggle('btn-success', allSelected);
            }
            
            btn.innerHTML = allSelected
                ? '<i class="bi bi-x-square me-1"></i> Unselect All'
                : '<i class="bi bi-check2-square me-1"></i> Select All';
            
            updateSelectedCount();
        }
    });

    function updateSelectedCount() {
        const count = document.querySelectorAll('.subject-checkbox:checked').length;
        if (selectedCountEl) {
            selectedCountEl.textContent = count;
        }
    }

    // Listen for checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('subject-checkbox')) {
            updateSelectedCount();
        }
    });

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
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('curriculumSelect') && document.getElementById('confirmForm')) {
        // Check if we're on the select curriculum subjects page
        const pageDataExists = typeof window.pageData !== 'undefined' && 
                              (window.pageData.currentSemester !== undefined || window.pageData.userRole !== undefined);
        
        // Also check for legacy global variables
        const legacyDataExists = typeof window.currentSemester !== 'undefined' || typeof window.userRole !== 'undefined';
        
        if (pageDataExists || legacyDataExists) {
            // Support legacy global variables
            if (legacyDataExists && !pageDataExists) {
                window.pageData = {
                    currentSemester: window.currentSemester || '',
                    userRole: window.userRole || 0
                };
            }
            initSelectCurriculumSubjectsPage();
        }
    }
});

window.initSelectCurriculumSubjectsPage = initSelectCurriculumSubjectsPage;
