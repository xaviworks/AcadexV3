/**
 * Instructor Manage Grades Page JavaScript
 * Handles subject card navigation and unsaved changes modal
 */

export function initManageGradesPage() {
    const cards = document.querySelectorAll('#subject-selection .subject-card[data-url]');
    if (!cards.length) {
        return;
    }

    cards.forEach(card => {
        if (card.dataset.clickBound === 'true') {
            return;
        }

        card.dataset.clickBound = 'true';
        card.setAttribute('role', 'button');
        card.tabIndex = 0;

        const navigate = () => {
            const url = card.dataset.url;
            if (url) {
                window.location.href = url;
            }
        };

        card.addEventListener('click', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (event.target.closest('a, button, input, label, select, textarea')) {
                return;
            }

            navigate();
        });

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                navigate();
            }
        });
    });
}

/**
 * Show unsaved changes modal
 * @param {Function} onConfirm - Callback when user confirms leaving
 * @param {Function|null} onCancel - Optional callback when user cancels
 */
window.showUnsavedChangesModal = function(onConfirm, onCancel = null) {
    // Create modal if it doesn't exist
    let modalElement = document.getElementById('unsavedChangesModal');
    if (!modalElement) {
        const modalWrapper = document.createElement('div');
        modalWrapper.innerHTML = `
            <div class="modal fade" id="unsavedChangesModal" tabindex="-1" aria-labelledby="unsavedChangesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-warning text-dark border-0">
                            <h5 class="modal-title d-flex align-items-center" id="unsavedChangesModalLabel">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Unsaved Changes
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">You have unsaved changes that will be lost if you continue.</p>
                            <p class="mb-0 text-muted">Are you sure you want to leave without saving?</p>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-warning" id="confirmLeaveBtn">Leave Without Saving</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalWrapper.firstElementChild);
        modalElement = document.getElementById('unsavedChangesModal');
    }

    // Use Bootstrap Modal API
    const modalInstance = new bootstrap.Modal(modalElement);
    const confirmBtn = document.getElementById('confirmLeaveBtn');

    // Remove any existing event listeners by cloning
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    // Add new event listener
    newConfirmBtn.addEventListener('click', function() {
        modalInstance.hide();
        if (onConfirm) onConfirm();
    });

    // Handle cancel
    modalElement.addEventListener('hidden.bs.modal', function() {
        if (onCancel) onCancel();
    }, { once: true });

    modalInstance.show();
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="instructor-manage-grades"]') || 
        document.querySelector('#subject-selection')) {
        initManageGradesPage();
    }
});

window.initManageGradesPage = initManageGradesPage;
