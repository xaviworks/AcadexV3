/**
 * Instructor Manage Grades Page JavaScript
 * Handles subject card navigation and unsaved changes modal
 */

export function initManageGradesPage() {
  const cards = document.querySelectorAll(
    '#instructor-subject-selection .subject-card[data-url], #subject-selection .subject-card[data-url]'
  );
  if (!cards.length) {
    return;
  }

  cards.forEach((card) => {
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
window.showUnsavedChangesModal = function (onConfirm, onCancel = null) {
  const modalElement = document.getElementById('unsavedChangesModal');
  if (!modalElement) {
    if (onConfirm) onConfirm();
    return;
  }

  // Use Bootstrap Modal API
  const modalInstance = new bootstrap.Modal(modalElement);
  const confirmBtn = document.getElementById('confirmLeaveBtn');

  // Remove any existing event listeners by cloning
  const newConfirmBtn = confirmBtn.cloneNode(true);
  confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

  // Add new event listener
  newConfirmBtn.addEventListener('click', function () {
    modalInstance.hide();
    if (onConfirm) onConfirm();
  });

  // Handle cancel
  modalElement.addEventListener(
    'hidden.bs.modal',
    function () {
      if (onCancel) onCancel();
    },
    { once: true }
  );

  modalInstance.show();
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (
    document.querySelector('[data-page="instructor-manage-grades"]') ||
    document.querySelector('#instructor-subject-selection') ||
    document.querySelector('#subject-selection')
  ) {
    initManageGradesPage();
  }
});

window.initManageGradesPage = initManageGradesPage;
