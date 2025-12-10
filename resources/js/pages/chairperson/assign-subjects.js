/**
 * Chairperson Assign Subjects Page JavaScript
 * Handles assign/unassign modals and view mode toggle
 */

export function initChairpersonAssignSubjectsPage() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Open confirm unassign modal
 * @param {number} subjectId - Subject ID
 * @param {string} subjectName - Subject name
 */
window.openConfirmUnassignModal = function(subjectId, subjectName) {
    document.getElementById('unassign_subject_id').value = subjectId;
    const modal = document.getElementById('confirmUnassignModal');
    modal.classList.remove('hidden');
    modal.classList.add('d-flex');
};

/**
 * Close confirm unassign modal
 */
window.closeConfirmUnassignModal = function() {
    const modal = document.getElementById('confirmUnassignModal');
    modal.classList.add('hidden');
    modal.classList.remove('d-flex');
};

/**
 * Open confirm assign modal
 * @param {number} subjectId - Subject ID
 * @param {string} subjectName - Subject name
 */
window.openConfirmAssignModal = function(subjectId, subjectName) {
    document.getElementById('assign_subject_id').value = subjectId;
    const modal = document.getElementById('confirmAssignModal');
    modal.classList.remove('hidden');
    modal.classList.add('d-flex');
};

/**
 * Close confirm assign modal
 */
window.closeConfirmAssignModal = function() {
    const modal = document.getElementById('confirmAssignModal');
    modal.classList.add('hidden');
    modal.classList.remove('d-flex');
};

/**
 * Toggle between year view and full view
 */
window.toggleViewMode = function() {
    const mode = document.getElementById('viewMode').value;
    const yearView = document.getElementById('yearView');
    const fullView = document.getElementById('fullView');

    if (mode === 'full') {
        yearView.classList.add('d-none');
        fullView.classList.remove('d-none');
    } else {
        yearView.classList.remove('d-none');
        fullView.classList.add('d-none');
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="chairperson-assign-subjects"]') || 
        window.location.pathname.includes('/chairperson/assign-subjects')) {
        initChairpersonAssignSubjectsPage();
    }
});

window.initChairpersonAssignSubjectsPage = initChairpersonAssignSubjectsPage;
