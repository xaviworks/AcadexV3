/**
 * GE Coordinator Assign Subjects Page JavaScript
 * Handles instructor assignment/unassignment for subjects
 */

let currentSubjectId = null;
let currentModalMode = 'view'; // 'view', 'unassign', or 'edit'
let currentUnassignInstructorIds = [];
let currentUnassignInstructorNames = [];

// Global instructor data for search/sort
let assignedInstructorsData = [];
let availableInstructorsData = [];

// Store references for bulk operations
window.bulkAssignInstructorIds = [];
window.bulkAssignCallerBtn = null;

// Get page data from window (set by Blade)
function getPageData() {
    return window.pageData || {};
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// Function to show Bootstrap toasts (top-right floating) for consistency
export function showNotification(type, message) {
    const toastContainer = document.getElementById('globalToastContainer') || createGlobalToastContainer();
    const toastId = `toast-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
    const toastClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${toastClass} border-0 shadow`;
    toastEl.role = 'alert';
    toastEl.ariaLive = 'assertive';
    toastEl.ariaAtomic = 'true';
    toastEl.id = toastId;
    toastEl.style.pointerEvents = 'auto';

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);

    const bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
    bsToast.show();

    toastEl.addEventListener('hidden.bs.toast', function () {
        toastEl.remove();
    });
}

function createGlobalToastContainer() {
    const container = document.createElement('div');
    container.id = 'globalToastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = 1040;
    container.style.pointerEvents = 'none';
    document.body.appendChild(container);
    return container;
}

// Broadcast helper for cross-tab updates
const instructorUpdatesChannel = (typeof BroadcastChannel !== 'undefined') ? new BroadcastChannel('ac-instructor-updates') : null;

function notifySubjectUpdate(subjectId) {
    try {
        if (instructorUpdatesChannel) {
            instructorUpdatesChannel.postMessage({ subjectId });
        }
        try {
            localStorage.setItem('ac-instructors-updated', JSON.stringify({ subjectId, ts: Date.now() }));
        } catch (e) {
            // ignore if localStorage unavailable
        }
    } catch (e) {
        // ignore
    }
}

// Global function to refresh assigned instructor counts for a subject across the page
export function refreshSubjectInstructorCount(subjectId) {
    if (!subjectId) return;
    fetch(`/gecoordinator/subjects/${subjectId}/instructors`)
        .then(resp => {
            if (!resp.ok) return resp.json().then(err => { throw new Error(err.message || 'Failed to load instructors'); }).catch(() => { throw new Error('Failed to load instructors'); });
            return resp.json();
        })
        .then(list => {
            const count = Array.isArray(list) ? list.length : (list.length || 0);
            document.querySelectorAll(`button.subject-view-btn[data-subject-id="${subjectId}"] .view-count`).forEach(el => {
                el.textContent = count;
            });
            document.querySelectorAll(`.subject-view-badge[data-subject-id="${subjectId}"]`).forEach(el => {
                el.textContent = count;
            });
        })
        .catch(err => {
            console.error('Error updating instructor count:', err);
        });
}

// Open a simple read-only modal to view assigned instructors
export function openViewInstructorsModal(subjectId, subjectName) {
    if (typeof window.modal !== 'undefined' && window.modal.open) {
        window.modal.open('viewInstructorsModal', { subjectId, subjectName });
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalEl = document.getElementById('viewInstructorsModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    }
    
    document.getElementById('viewSubjectName').textContent = subjectName;
    
    const listContainer = document.getElementById('viewInstructorList');
    listContainer.innerHTML = `
        <div class="text-center text-muted py-3">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 small">Loading instructors...</div>
        </div>
    `;
    
    fetch(`/gecoordinator/subjects/${subjectId}/instructors`)
        .then(response => response.json())
        .then(data => {
            const countEl = document.getElementById('viewInstructorCount');
            const instructors = Array.isArray(data) ? data : [];
            const count = instructors.length;
            
            if (countEl) {
                countEl.textContent = count === 0 ? 'No instructors assigned' : 
                    `${count} instructor${count !== 1 ? 's' : ''} assigned`;
            }
            
            if (count === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <div>No instructors assigned to this subject yet.</div>
                    </div>
                `;
            } else {
                listContainer.innerHTML = '';
                instructors.forEach(instructor => {
                    const div = document.createElement('div');
                    div.className = 'd-flex align-items-center';
                    div.innerHTML = `
                        <i class="bi bi-person-circle text-success me-2"></i>
                        <span>${instructor.name}</span>
                    `;
                    listContainer.appendChild(div);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching instructors:', error);
            listContainer.innerHTML = `
                <div class="text-center text-danger py-3">
                    <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
                    <div>Failed to load instructors</div>
                </div>
            `;
        });
}

export function openInstructorListModal(subjectId, subjectName, mode = 'view') {
    currentSubjectId = subjectId;
    currentModalMode = mode;
    document.getElementById('instructorListSubjectName').textContent = subjectName;
    
    const modalTitle = document.getElementById('instructorListModalTitle');
    if (modalTitle) modalTitle.textContent = 'Manage Instructors';
    
    if (typeof window.modal !== 'undefined' && window.modal.open) {
        window.modal.open('instructorListModal', { subjectId, subjectName, mode });
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalEl = document.getElementById('instructorListModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    }
    
    Promise.all([
        fetch(`/gecoordinator/subjects/${subjectId}/instructors`),
        fetch('/gecoordinator/available-instructors')
    ])
    .then(([assignedResp, availableResp]) => {
        if (!assignedResp.ok) {
            return assignedResp.json().then(err => { throw new Error(err.message || 'Failed to load assigned instructors'); }).catch(() => { throw new Error('Failed to load assigned instructors'); });
        }
        if (!availableResp.ok) {
            return availableResp.json().then(err => { throw new Error(err.message || 'Failed to load available instructors'); }).catch(() => { throw new Error('Failed to load available instructors'); });
        }
        return Promise.all([assignedResp.json(), availableResp.json()]);
    })
    .then(([assignedInstructors, availableInstructors]) => {
        renderSplitPaneInstructorList(assignedInstructors, availableInstructors);
    })
    .catch(error => {
        console.error('Error loading instructors:', error);
        const message = error.message || 'Failed to load instructors. Please try again.';
        const assignedList = document.getElementById('assignedInstructorsList');
        const availableList = document.getElementById('availableInstructorsList');
        if (assignedList) {
            assignedList.innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    ${message}
                </div>`;
        }
        if (availableList) availableList.innerHTML = '';
    });
}

function renderSplitPaneInstructorList(assignedInstructors, availableInstructors) {
    const assignedIds = assignedInstructors.map(i => i.id);
    assignedInstructorsData = assignedInstructors;
    availableInstructorsData = availableInstructors.filter(i => !assignedIds.includes(i.id));
    
    const assignBadge = document.getElementById('assignTabCount');
    const unassignBadge = document.getElementById('unassignTabCount');
    if (assignBadge) assignBadge.textContent = availableInstructorsData.length;
    if (unassignBadge) unassignBadge.textContent = assignedInstructorsData.length;
    
    renderAssignedListTab(assignedInstructorsData);
    renderAvailableListTab(availableInstructorsData);
    
    setupTabEventListeners();
}

function renderAssignedListTab(instructors) {
    const container = document.getElementById('assignedInstructorsListTab');
    if (!container) return;
    
    if (instructors.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                <p class="fw-semibold mb-1">No Instructors Assigned Yet</p>
                <p class="small text-muted mb-2">This subject doesn't have any instructors teaching it</p>
                <p class="small text-primary mb-0">
                    <i class="bi bi-arrow-left me-1"></i> 
                    <strong>Switch to "Assign Instructors" tab</strong> to add instructors
                </p>
            </div>`;
        const btn = document.getElementById('unassignSelectedBtnTab');
        if (btn) btn.disabled = true;
        return;
    }
    
    container.innerHTML = `
        <div class="alert alert-success border-0 py-2 px-3 mb-3" role="alert">
            <small><i class="bi bi-hand-index-thumb me-1"></i> <strong>Check boxes</strong> to select instructors, then click the button above to remove them</small>
        </div>`;
    instructors.forEach(instructor => {
        const item = document.createElement('div');
        item.className = 'form-check mb-2 p-3 rounded hover-bg';
        item.dataset.instructorId = instructor.id;
        item.dataset.instructorName = instructor.name.toLowerCase();
        item.innerHTML = `
            <div class="d-flex align-items-center">
                <input class="form-check-input assigned-checkbox-tab me-2" type="checkbox" value="${instructor.id}" id="assigned-tab-${instructor.id}" title="Check to select" style="transform: scale(1.2);">
                <label class="form-check-label d-flex align-items-center mb-0" for="assigned-tab-${instructor.id}" style="cursor: pointer;">
                    <i class="bi bi-person-fill text-success me-2"></i>
                    <span>${instructor.name}</span>
                </label>
            </div>`;
        container.appendChild(item);
    });
    
    const btn = document.getElementById('unassignSelectedBtnTab');
    if (btn) btn.disabled = false;
}

function renderAvailableListTab(instructors) {
    const container = document.getElementById('availableInstructorsListTab');
    if (!container) return;
    
    if (instructors.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-check-circle fs-1 d-block mb-3 opacity-25 text-success"></i>
                <p class="fw-semibold mb-1">All Instructors Assigned!</p>
                <p class="small mb-0">All available instructors are assigned to this subject</p>
            </div>`;
        const btn = document.getElementById('assignSelectedBtnTab');
        if (btn) btn.disabled = true;
        return;
    }
    
    container.innerHTML = `
        <div class="alert alert-primary border-0 py-2 px-3 mb-3" role="alert">
            <small><i class="bi bi-hand-index-thumb me-1"></i> <strong>Check boxes</strong> to select instructors, then click the button above to add them</small>
        </div>`;
    instructors.forEach(instructor => {
        const item = document.createElement('div');
        item.className = 'form-check mb-2 p-3 rounded hover-bg';
        item.dataset.instructorId = instructor.id;
        item.dataset.instructorName = instructor.name.toLowerCase();
        item.innerHTML = `
            <div class="d-flex align-items-center">
                <input class="form-check-input available-checkbox-tab me-2" type="checkbox" value="${instructor.id}" id="available-tab-${instructor.id}" title="Check to select" style="transform: scale(1.2);">
                <label class="form-check-label d-flex align-items-center mb-0" for="available-tab-${instructor.id}" style="cursor: pointer;">
                    <i class="bi bi-person-plus text-primary me-2"></i>
                    <span>${instructor.name}</span>
                </label>
            </div>`;
        container.appendChild(item);
    });
    
    const btn = document.getElementById('assignSelectedBtnTab');
    if (btn) btn.disabled = false;
}

function setupTabEventListeners() {
    // Search assigned tab
    setupSearchHandler('searchAssignedTab', '#assignedInstructorsListTab .form-check');
    
    // Search available tab
    setupSearchHandler('searchAvailableTab', '#availableInstructorsListTab .form-check');
    
    // Sort assigned tab
    setupSortToggle('sortAssignedToggleTab', assignedInstructorsData, renderAssignedListTab);
    
    // Sort available tab
    setupSortToggle('sortAvailableToggleTab', availableInstructorsData, renderAvailableListTab);
    
    // Bulk unassign button
    setupBulkButton('unassignSelectedBtnTab', '.assigned-checkbox-tab', confirmUnassignInstructor);
    
    // Bulk assign button
    setupBulkButton('assignSelectedBtnTab', '.available-checkbox-tab', showBulkAssignModal);
    
    // Enable/disable bulk buttons based on selection
    document.addEventListener('change', handleCheckboxChangeTab);
}

function setupSearchHandler(inputId, itemsSelector) {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;
    
    const newSearchInput = searchInput.cloneNode(true);
    searchInput.parentNode.replaceChild(newSearchInput, searchInput);
    
    newSearchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const items = document.querySelectorAll(itemsSelector);
        items.forEach(item => {
            const name = item.dataset.instructorName;
            item.style.display = name && name.includes(query) ? '' : 'none';
        });
    });
}

function setupSortToggle(buttonId, dataArray, renderFunc) {
    const el = document.getElementById(buttonId);
    if (!el) return;
    
    const newEl = el.cloneNode(true);
    try { 
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(newEl); 
        }
    } catch (e) {}
    el.parentNode.replaceChild(newEl, el);
    
    newEl.addEventListener('click', () => {
        const current = newEl.dataset.sort || 'asc';
        const next = current === 'asc' ? 'desc' : 'asc';
        newEl.dataset.sort = next;
        const icon = newEl.querySelector('i');
        if (icon) icon.className = 'bi ' + (next === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up');
        newEl.title = next === 'asc' ? 'Sort A to Z' : 'Sort Z to A';
        newEl.setAttribute('aria-pressed', next === 'desc');
        
        try { 
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) { 
                const t = bootstrap.Tooltip.getInstance(newEl); 
                if (t) t.dispose(); 
                new bootstrap.Tooltip(newEl); 
            } 
        } catch (e) {}
        
        const sorted = [...dataArray].sort((a, b) => next === 'asc' ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name));
        renderFunc(sorted);
    });
}

function setupBulkButton(buttonId, checkboxSelector, actionFunc) {
    const btn = document.getElementById(buttonId);
    if (!btn) return;
    
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    
    newBtn.addEventListener('click', () => {
        const checkedBoxes = document.querySelectorAll(`${checkboxSelector}:checked`);
        if (checkedBoxes.length === 0) {
            showNotification('error', 'No instructors selected');
            return;
        }
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        const names = Array.from(checkedBoxes).map(cb => {
            const label = document.querySelector(`label[for="${cb.id}"]`);
            return label ? label.textContent.trim() : '';
        });
        actionFunc(ids, names, newBtn);
    });
}

function handleCheckboxChangeTab(e) {
    if (e.target.classList.contains('assigned-checkbox-tab')) {
        const hasChecked = document.querySelectorAll('.assigned-checkbox-tab:checked').length > 0;
        const btn = document.getElementById('unassignSelectedBtnTab');
        if (btn) btn.disabled = !hasChecked;
    }
    if (e.target.classList.contains('available-checkbox-tab')) {
        const hasChecked = document.querySelectorAll('.available-checkbox-tab:checked').length > 0;
        const btn = document.getElementById('assignSelectedBtnTab');
        if (btn) btn.disabled = !hasChecked;
    }
}

export function confirmUnassignInstructor(instructorIdOrArray, instructorNameOrArray = null) {
    if (Array.isArray(instructorIdOrArray)) {
        currentUnassignInstructorIds = instructorIdOrArray;
        currentUnassignInstructorNames = Array.isArray(instructorNameOrArray) ? instructorNameOrArray : [];
    } else {
        currentUnassignInstructorIds = [instructorIdOrArray];
        currentUnassignInstructorNames = [instructorNameOrArray || ''];
    }

    const subjectNameEl = document.getElementById('unassignTargetSubject');
    const subjectName = document.getElementById('instructorListSubjectName')?.textContent || 'Unknown Subject';
    if (subjectNameEl) subjectNameEl.textContent = subjectName;

    const list = document.getElementById('unassignList');
    const countEl = document.getElementById('unassignSelectionCount');
    if (list) {
        list.innerHTML = '';
        currentUnassignInstructorNames.forEach(n => {
            const div = document.createElement('div');
            div.textContent = n;
            list.appendChild(div);
        });
        if (countEl) countEl.textContent = `${currentUnassignInstructorNames.length} instructor(s) will be unassigned`;
    }

    const centerToast = document.getElementById('centerToastContainer');
    if (centerToast) centerToast.style.display = 'none';

    if (typeof window.modal !== 'undefined' && window.modal.open) {
        window.modal.open('confirmUnassignModal');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalEl = document.getElementById('confirmUnassignModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    }
}

export function showBulkAssignModal(ids, names, callingBtn) {
    const subjectNameEl = document.getElementById('assignTargetSubject');
    const subjectName = document.getElementById('instructorListSubjectName')?.textContent || 'Unknown Subject';
    if (subjectNameEl) subjectNameEl.textContent = subjectName;
    
    const list = document.getElementById('assignList');
    const countEl = document.getElementById('assignSelectionCount');
    if (list) {
        list.innerHTML = '';
        names.forEach(n => {
            const div = document.createElement('div');
            div.textContent = n;
            list.appendChild(div);
        });
        if (countEl) countEl.textContent = `${names.length} instructor(s) will be assigned`;
    }

    const centerToast = document.getElementById('centerToastContainer');
    if (centerToast) centerToast.style.display = 'none';

    if (typeof window.modal !== 'undefined' && window.modal.open) {
        window.modal.open('confirmBulkAssignModal');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalEl = document.getElementById('confirmBulkAssignModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    }

    window.bulkAssignInstructorIds = ids;
    window.bulkAssignCallerBtn = callingBtn;
}

export function assignMultipleInstructors(subjectId, instructorIds, button) {
    const pageData = getPageData();
    const assignUrl = pageData.assignInstructorUrl || '/gecoordinator/assign-instructor';
    const csrfToken = getCsrfToken();
    
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Assigning...';
    }

    Promise.all(instructorIds.map(id => {
        const formData = new FormData();
        formData.append('subject_id', subjectId);
        formData.append('instructor_id', id);
        return fetch(assignUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        }).then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err; });
            }
            return res.json();
        });
    }))
    .then(results => {
        const successCount = results.filter(r => r && r.success).length;
        if (successCount === 0) throw new Error('No instructors were assigned');
        showNotification('success', `${successCount} instructor(s) assigned successfully!`);
        setTimeout(() => {
            openInstructorListModal(subjectId, document.getElementById('instructorListSubjectName')?.textContent || '', 'view');
            refreshSubjectInstructorCount(subjectId);
            notifySubjectUpdate(subjectId);
        }, 400);
    })
    .catch(error => {
        console.error('Error assigning multiple instructors:', error);
        showNotification('error', error.message || 'Failed to assign selected instructors');
    })
    .finally(() => {
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-person-plus me-1"></i>Add Selected';
        }
    });
}

export function toggleViewMode() {
    const mode = document.getElementById('viewMode')?.value;
    const yearView = document.getElementById('yearView');
    const fullView = document.getElementById('fullView');

    if (mode === 'full') {
        yearView?.classList.add('d-none');
        fullView?.classList.remove('d-none');
    } else {
        yearView?.classList.remove('d-none');
        fullView?.classList.add('d-none');
    }
}

export function quickUnassign(instructorId, instructorName) {
    confirmUnassignInstructor([instructorId], [instructorName]);
}

export function quickAssign(instructorId, instructorName) {
    const btn = event?.target?.closest('button');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    assignMultipleInstructors(currentSubjectId, [instructorId], btn);
}

export function initAssignSubjectsPage() {
    const pageData = getPageData();
    const unassignUrl = pageData.unassignInstructorUrl || '/gecoordinator/unassign-instructor';
    const assignUrl = pageData.assignInstructorUrl || '/gecoordinator/assign-instructor';
    const csrfToken = getCsrfToken();
    
    // Listen for broadcast updates
    if (instructorUpdatesChannel) {
        instructorUpdatesChannel.addEventListener('message', e => {
            if (e && e.data && e.data.subjectId) {
                refreshSubjectInstructorCount(e.data.subjectId);
                if (currentSubjectId && e.data.subjectId === currentSubjectId) {
                    openInstructorListModal(currentSubjectId, document.getElementById('instructorListSubjectName')?.textContent || '', currentModalMode);
                }
            }
        });
    }

    window.addEventListener('storage', (ev) => {
        if (ev.key === 'ac-instructors-updated' && ev.newValue) {
            try {
                const payload = JSON.parse(ev.newValue);
                if (payload && payload.subjectId) {
                    refreshSubjectInstructorCount(payload.subjectId);
                    if (currentSubjectId && payload.subjectId === currentSubjectId) {
                        openInstructorListModal(currentSubjectId, document.getElementById('instructorListSubjectName')?.textContent || '', currentModalMode);
                    }
                }
            } catch (err) {
                // ignore
            }
        }
    });

    // Confirm unassign button handler
    const confirmUnassignBtn = document.getElementById('confirmUnassignBtn');
    if (confirmUnassignBtn) {
        confirmUnassignBtn.addEventListener('click', function() {
            if (!currentUnassignInstructorIds || currentUnassignInstructorIds.length === 0 || !currentSubjectId) {
                showNotification('error', 'Missing instructor or subject information');
                return;
            }

            this.disabled = true;
            const origHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';

            if (typeof window.modal !== 'undefined' && window.modal.close) {
                window.modal.close('confirmUnassignModal');
            }
            
            Promise.all(currentUnassignInstructorIds.map(id => {
                return fetch(unassignUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ subject_id: currentSubjectId, instructor_id: id })
                }).then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw new Error(err.message || 'Failed to unassign instructor'); }).catch(() => { throw new Error('Failed to unassign instructor'); });
                    }
                    return res.json();
                });
            }))
            .then(results => {
                const successCount = results.filter(r => r && r.success).length;
                if (successCount === 0) throw new Error('No instructor was unassigned');
                showNotification('success', `${successCount} instructor(s) unassigned successfully.`);
                setTimeout(() => {
                    openInstructorListModal(currentSubjectId, document.getElementById('instructorListSubjectName')?.textContent || '', 'view');
                    refreshSubjectInstructorCount(currentSubjectId);
                    notifySubjectUpdate(currentSubjectId);
                }, 400);
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', error.message || 'Failed to unassign instructor');
                setTimeout(() => {
                    openInstructorListModal(currentSubjectId, document.getElementById('instructorListSubjectName')?.textContent || '');
                }, 1000);
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = origHtml || '<i class="bi bi-person-dash me-1"></i> Yes, unassign';
                currentUnassignInstructorIds = [];
                currentUnassignInstructorNames = [];
            });
        });
    }

    // Clear selection display when the unassign modal is closed
    const unassignModalEl = document.getElementById('confirmUnassignModal');
    if (unassignModalEl) {
        unassignModalEl.addEventListener('hidden.bs.modal', () => {
            const list = document.getElementById('unassignList');
            const countEl = document.getElementById('unassignSelectionCount');
            if (list) list.innerHTML = '';
            if (countEl) countEl.textContent = '';
            currentUnassignInstructorIds = [];
            currentUnassignInstructorNames = [];
        });
    }

    // Confirm bulk assign button handler
    const confirmBulkAssignBtn = document.getElementById('confirmBulkAssignBtn');
    if (confirmBulkAssignBtn) {
        confirmBulkAssignBtn.addEventListener('click', function() {
            if (!window.bulkAssignInstructorIds || window.bulkAssignInstructorIds.length === 0 || !currentSubjectId) {
                showNotification('error', 'Missing instructor or subject information');
                return;
            }

            if (typeof window.loading !== 'undefined' && window.loading.start) {
                window.loading.start('bulkAssign');
            }
            this.disabled = true;

            if (typeof window.modal !== 'undefined' && window.modal.close) {
                window.modal.close('confirmBulkAssignModal');
            }

            const callerBtn = window.bulkAssignCallerBtn || this;
            assignMultipleInstructors(currentSubjectId, window.bulkAssignInstructorIds, callerBtn);

            setTimeout(() => {
                if (typeof window.loading !== 'undefined' && window.loading.stop) {
                    window.loading.stop('bulkAssign');
                }
                this.disabled = false;
            }, 800);
        });
    }

    // Clear selection display when the assign modal is closed
    const bulkAssignModalEl = document.getElementById('confirmBulkAssignModal');
    if (bulkAssignModalEl) {
        bulkAssignModalEl.addEventListener('hidden.bs.modal', () => {
            const list = document.getElementById('assignList');
            const countEl = document.getElementById('assignSelectionCount');
            if (list) list.innerHTML = '';
            if (countEl) countEl.textContent = '';
            const centerToast = document.getElementById('centerToastContainer');
            if (centerToast) centerToast.style.display = '';
            window.bulkAssignInstructorIds = [];
            window.bulkAssignCallerBtn = null;
        });
    }

    // Assign instructor form submission
    const form = document.getElementById('assignInstructorForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = form.querySelector('button[type="submit"]');
            
            if (typeof window.loading !== 'undefined' && window.loading.start) {
                window.loading.start('assignInstructor');
            }
            if (submitButton) {
                submitButton.disabled = true;
            }
            
            const formData = new FormData(form);
            
            fetch(assignUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof window.modal !== 'undefined' && window.modal.close) {
                        window.modal.close('confirmAssignModal');
                    }
                    
                    if (typeof window.notify !== 'undefined' && window.notify.success) {
                        window.notify.success(data.message || 'Instructor assigned successfully!');
                    } else {
                        showNotification('success', data.message || 'Instructor assigned successfully!');
                    }
                    
                    setTimeout(() => {
                        const sid = document.getElementById('assign_subject_id')?.value || '';
                        if (sid) {
                            openInstructorListModal(sid, document.getElementById('instructorListSubjectName')?.textContent || '', 'edit');
                            refreshSubjectInstructorCount(sid);
                            notifySubjectUpdate(sid);
                        } else {
                            window.location.reload();
                        }
                    }, 800);
                } else {
                    throw new Error(data.message || 'Failed to assign instructor');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.notify !== 'undefined' && window.notify.error) {
                    window.notify.error(error.message || 'Failed to assign instructor');
                } else {
                    showNotification('error', error.message || 'Failed to assign instructor');
                }
            })
            .finally(() => {
                if (typeof window.loading !== 'undefined' && window.loading.stop) {
                    window.loading.stop('assignInstructor');
                }
                if (submitButton) {
                    submitButton.disabled = false;
                }
            });
        });
    }

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        }
        return null;
    });

    // Render server-side flash messages as toasts
    if (pageData.successMessage) {
        if (typeof window.notify !== 'undefined' && window.notify.success) {
            window.notify.success(pageData.successMessage);
        } else {
            showNotification('success', pageData.successMessage);
        }
    }
    if (pageData.errorMessage) {
        if (typeof window.notify !== 'undefined' && window.notify.error) {
            window.notify.error(pageData.errorMessage);
        } else {
            showNotification('error', pageData.errorMessage);
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('yearTabs') || document.getElementById('instructorListModal')) {
        initAssignSubjectsPage();
    }
});

// Export for global use
window.showNotification = showNotification;
window.refreshSubjectInstructorCount = refreshSubjectInstructorCount;
window.openViewInstructorsModal = openViewInstructorsModal;
window.openInstructorListModal = openInstructorListModal;
window.confirmUnassignInstructor = confirmUnassignInstructor;
window.showBulkAssignModal = showBulkAssignModal;
window.assignMultipleInstructors = assignMultipleInstructors;
window.toggleViewMode = toggleViewMode;
window.quickUnassign = quickUnassign;
window.quickAssign = quickAssign;
window.initAssignSubjectsPage = initAssignSubjectsPage;
