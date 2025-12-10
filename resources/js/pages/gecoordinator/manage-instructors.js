/**
 * GE Coordinator - Manage Instructors Page JavaScript
 * 
 * Handles modal interactions for instructor management:
 * - Approve/Reject pending instructor approvals
 * - Activate/Deactivate instructors
 * - Approve/Reject GE subject requests
 */

/**
 * Sets up a modal with data from the trigger button
 * @param {HTMLElement} modal - The modal element
 * @param {Function} dataHandler - Function to handle data extraction and DOM updates
 */
function setupModalHandler(modal, dataHandler) {
    if (!modal) return;

    // Primary handler using Bootstrap's show.bs.modal event
    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (!button) return;
        dataHandler(button);
    });

    // Fallback click listeners for trigger buttons
    const modalId = modal.getAttribute('id');
    document.querySelectorAll(`[data-bs-target="#${modalId}"]`).forEach(btn => {
        btn.addEventListener('click', () => dataHandler(btn));
    });
}

/**
 * Extracts instructor ID from button attributes
 * @param {HTMLElement} button - The trigger button
 * @returns {string|null} The instructor ID
 */
function getInstructorId(button) {
    return button.getAttribute('data-id') || 
           button.getAttribute('data-instructor-id') || 
           button.dataset.id || 
           button.dataset.instructorId;
}

/**
 * Extracts instructor name from button attributes
 * @param {HTMLElement} button - The trigger button
 * @returns {string|null} The instructor name
 */
function getInstructorName(button) {
    return button.getAttribute('data-name') || 
           button.getAttribute('data-instructor-name') || 
           button.dataset.name || 
           button.dataset.instructorName;
}

/**
 * Extracts GE request ID from button attributes
 * @param {HTMLElement} button - The trigger button
 * @returns {string|null} The request ID
 */
function getRequestId(button) {
    return button.getAttribute('data-request-id') || 
           button.dataset.requestId;
}

/**
 * Initialize all modal handlers for the manage instructors page
 */
function initGECoordinatorManageInstructorsPage() {
    // Approve instructor modal
    const approveModal = document.getElementById('confirmApproveModal');
    setupModalHandler(approveModal, (button) => {
        const id = getInstructorId(button);
        const name = getInstructorName(button);
        
        const form = document.getElementById('approveForm');
        const nameEl = document.getElementById('approveName');
        
        if (id && form) form.action = `/gecoordinator/approvals/${id}/approve`;
        if (name && nameEl) nameEl.textContent = name;
    });

    // Reject instructor modal
    const rejectModal = document.getElementById('confirmRejectModal');
    setupModalHandler(rejectModal, (button) => {
        const id = getInstructorId(button);
        const name = getInstructorName(button);
        
        const form = document.getElementById('rejectForm');
        const nameEl = document.getElementById('rejectName');
        
        if (id && form) form.action = `/gecoordinator/approvals/${id}/reject`;
        if (name && nameEl) nameEl.textContent = name;
    });

    // Deactivate instructor modal
    const deactivateModal = document.getElementById('confirmDeactivateModal');
    setupModalHandler(deactivateModal, (button) => {
        const id = getInstructorId(button);
        const name = getInstructorName(button);
        
        const form = document.getElementById('deactivateForm');
        const nameEl = document.getElementById('instructorName');
        
        if (id && form) form.action = `/gecoordinator/instructors/${id}/deactivate`;
        if (name && nameEl) nameEl.textContent = name;
    });

    // Activate instructor modal
    const activateModal = document.getElementById('confirmActivateModal');
    setupModalHandler(activateModal, (button) => {
        const id = getInstructorId(button);
        const name = getInstructorName(button);
        
        const form = document.getElementById('activateForm');
        const nameEl = document.getElementById('activateName');
        
        if (id && form) form.action = `/gecoordinator/instructors/${id}/activate`;
        if (name && nameEl) nameEl.textContent = name;
    });

    // Approve GE subject request modal
    const approveGERequestModal = document.getElementById('approveGERequestModal');
    setupModalHandler(approveGERequestModal, (button) => {
        const requestId = getRequestId(button);
        const instructorName = getInstructorName(button);
        
        const form = document.getElementById('approveGERequestForm');
        const nameEl = document.getElementById('approveGERequestName');
        
        if (requestId && form) form.action = `/gecoordinator/ge-requests/${requestId}/approve`;
        if (instructorName && nameEl) nameEl.textContent = instructorName;
    });

    // Reject GE subject request modal
    const rejectGERequestModal = document.getElementById('rejectGERequestModal');
    setupModalHandler(rejectGERequestModal, (button) => {
        const requestId = getRequestId(button);
        const instructorName = getInstructorName(button);
        
        const form = document.getElementById('rejectGERequestForm');
        const nameEl = document.getElementById('rejectGERequestName');
        
        if (requestId && form) form.action = `/gecoordinator/ge-requests/${requestId}/reject`;
        if (instructorName && nameEl) nameEl.textContent = instructorName;
    });
}

// Export functions for use
window.initGECoordinatorManageInstructorsPage = initGECoordinatorManageInstructorsPage;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initGECoordinatorManageInstructorsPage);
