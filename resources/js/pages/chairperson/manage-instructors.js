/**
 * Chairperson Manage Instructors Page JavaScript
 * Handles instructor management modals and form submissions
 */

/**
 * Initialize manage instructors page functionality
 */
function initManageInstructorsPage() {
    const approveModal = document.getElementById('confirmApproveModal');
    const rejectModal = document.getElementById('confirmRejectModal');
    const deactivateModal = document.getElementById('confirmDeactivateModal');
    const activateModal = document.getElementById('confirmActivateModal');
    const requestGEModal = document.getElementById('requestGEAssignmentModal');

    // Handling the approve modal
    if (approveModal) {
        approveModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            const form = document.getElementById('approveForm');
            if (form) {
                form.action = `/chairperson/approvals/${button.getAttribute('data-id')}/approve`;
            }
            const nameEl = document.getElementById('approveName');
            if (nameEl) {
                nameEl.textContent = button.getAttribute('data-name') || '';
            }
        });
    }

    // Click fallback for approve buttons
    document.querySelectorAll('button[data-bs-target="#confirmApproveModal"]').forEach(function(btn) {
        btn.addEventListener('click', function (e) {
            const form = document.getElementById('approveForm');
            const url = btn.getAttribute('data-approve-url') || (`/chairperson/approvals/${btn.getAttribute('data-id')}/approve`);
            if (form) {
                form.action = url;
                const nameEl = document.getElementById('approveName');
                if (nameEl) nameEl.textContent = btn.getAttribute('data-name') || '';
            }
        });
    });

    // Handling the reject modal
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            const form = document.getElementById('rejectForm');
            if (form) {
                form.action = `/chairperson/approvals/${button.getAttribute('data-id')}/reject`;
            }
            const nameEl = document.getElementById('rejectName');
            if (nameEl) {
                nameEl.textContent = button.getAttribute('data-name') || '';
            }
        });
    }

    // Click fallback for reject buttons
    document.querySelectorAll('button[data-bs-target="#confirmRejectModal"]').forEach(function(btn) {
        btn.addEventListener('click', function (e) {
            const form = document.getElementById('rejectForm');
            const url = btn.getAttribute('data-reject-url') || (`/chairperson/approvals/${btn.getAttribute('data-id')}/reject`);
            if (form) {
                form.action = url;
                const nameEl = document.getElementById('rejectName');
                if (nameEl) nameEl.textContent = btn.getAttribute('data-name') || '';
            }
        });
    });

    // Handling the deactivate modal
    if (deactivateModal) {
        deactivateModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            const deactivateUrl = button.getAttribute('data-deactivate-url') || 
                `/chairperson/instructors/${button.getAttribute('data-instructor-id')}/deactivate`;
            const form = document.getElementById('deactivateForm');
            if (form) {
                form.action = deactivateUrl;
            }
            const nameEl = document.getElementById('instructorName');
            if (nameEl) {
                nameEl.textContent = button.getAttribute('data-instructor-name') || '';
            }
        });
    }

    // Guard deactivate form submit
    const deactivateFormEl = document.getElementById('deactivateForm');
    if (deactivateFormEl) {
        deactivateFormEl.addEventListener('submit', function(e) {
            const action = this.getAttribute('action') || '';
            if (!action || !action.includes('/deactivate')) {
                e.preventDefault();
                console.warn('Deactivate form action invalid:', action);
                alert('Unable to determine the instructor to deactivate. Please re-open the dialog and try again.');
                return false;
            }
        });
    }

    // Click fallback for deactivate buttons
    document.querySelectorAll('button[data-bs-target="#confirmDeactivateModal"]').forEach(function(btn) {
        btn.addEventListener('click', function (e) {
            const form = document.getElementById('deactivateForm');
            const url = btn.getAttribute('data-deactivate-url') || 
                (`/chairperson/instructors/${btn.getAttribute('data-instructor-id')}/deactivate`);
            if (form) {
                form.action = url;
                const nameEl = document.getElementById('instructorName');
                if (nameEl) nameEl.textContent = btn.getAttribute('data-instructor-name') || '';
            }
        });
    });

    // Handling the activate modal
    if (activateModal) {
        activateModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            const activateUrl = button.getAttribute('data-activate-url') || 
                `/chairperson/instructors/${button.getAttribute('data-id')}/activate`;
            const form = document.getElementById('activateForm');
            if (form) {
                form.action = activateUrl;
            }
            const nameEl = document.getElementById('activateName');
            if (nameEl) {
                nameEl.textContent = button.getAttribute('data-name') || '';
            }
        });
    }

    // Guard activate form submit
    const activateFormEl = document.getElementById('activateForm');
    if (activateFormEl) {
        activateFormEl.addEventListener('submit', function(e) {
            const action = this.getAttribute('action') || '';
            if (!action || !action.includes('/activate')) {
                e.preventDefault();
                console.warn('Activate form action invalid:', action);
                alert('Unable to determine the instructor to activate. Please re-open the dialog and try again.');
                return false;
            }
        });
    }

    // Click fallback for activate buttons
    document.querySelectorAll('button[data-bs-target="#confirmActivateModal"]').forEach(function(btn) {
        btn.addEventListener('click', function (e) {
            const form = document.getElementById('activateForm');
            const url = btn.getAttribute('data-activate-url') || 
                (`/chairperson/instructors/${btn.getAttribute('data-id')}/activate`);
            if (form) {
                form.action = url;
                const nameEl = document.getElementById('activateName');
                if (nameEl) nameEl.textContent = btn.getAttribute('data-name') || '';
            }
        });
    });

    // Handling the GE request modal
    if (requestGEModal) {
        requestGEModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            const reqUrl = button.getAttribute('data-request-ge-url') || 
                `/chairperson/instructors/${button.getAttribute('data-instructor-id')}/request-ge-assignment`;
            const form = document.getElementById('requestGEForm');
            if (form) {
                form.action = reqUrl;
            }
            const nameEl = document.getElementById('requestGEName');
            if (nameEl) {
                nameEl.textContent = button.getAttribute('data-instructor-name') || '';
            }
        });
    }

    // Guard request GE form submit
    const requestGEFormEl = document.getElementById('requestGEForm');
    if (requestGEFormEl) {
        requestGEFormEl.addEventListener('submit', function(e) {
            const action = this.getAttribute('action') || '';
            if (!action || action.indexOf('/request-ge-assignment') === -1) {
                e.preventDefault();
                console.warn('Request GE form action invalid:', action);
                alert('Unable to determine the instructor to request GE assignment for. Please re-open the dialog and try again.');
                return false;
            }
        });
    }

    // Click fallback for request GE buttons
    document.querySelectorAll('button[data-bs-target="#requestGEAssignmentModal"]').forEach(function(btn) {
        btn.addEventListener('click', function (e) {
            const form = document.getElementById('requestGEForm');
            const url = btn.getAttribute('data-request-ge-url') || 
                (`/chairperson/instructors/${btn.getAttribute('data-instructor-id')}/request-ge-assignment`);
            if (form) {
                form.action = url;
                const nameEl = document.getElementById('requestGEName');
                if (nameEl) nameEl.textContent = btn.getAttribute('data-instructor-name') || '';
            }
        });
    });

    // Guard approve form submit
    const approveFormEl = document.getElementById('approveForm');
    if (approveFormEl) {
        approveFormEl.addEventListener('submit', function(e) {
            const action = this.getAttribute('action') || '';
            if (!action || action.indexOf('/approve') === -1) {
                e.preventDefault();
                console.warn('Approve form action invalid:', action);
                alert('Unable to determine the account to approve. Please re-open the dialog and try again.');
                return false;
            }
        });
    }

    // Guard reject form submit
    const rejectFormEl = document.getElementById('rejectForm');
    if (rejectFormEl) {
        rejectFormEl.addEventListener('submit', function(e) {
            const action = this.getAttribute('action') || '';
            if (!action || action.indexOf('/reject') === -1) {
                e.preventDefault();
                console.warn('Reject form action invalid:', action);
                alert('Unable to determine the account to reject. Please re-open the dialog and try again.');
                return false;
            }
        });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initManageInstructorsPage);

// Export for module usage
export { initManageInstructorsPage };
