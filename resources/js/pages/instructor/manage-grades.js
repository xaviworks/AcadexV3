/**
 * Instructor Manage Grades Page JavaScript
 * Handles subject card navigation, unsaved changes modal, and live updates
 *
 * Live Updates Feature:
 * - Automatically detects new subjects assigned by chairperson
 * - Adds new subject cards with smooth animation
 * - Updates existing card data (student count, status)
 * - Uses polling fallback when WebSockets unavailable
 */

import liveUpdateService from '../../services/LiveUpdateService';

// Track current subjects for change detection
let currentSubjects = new Map();
let liveUpdateInitialized = false;

export function initManageGradesPage() {
  const cards = document.querySelectorAll('#subject-selection .subject-card[data-url]');
  if (!cards.length) {
    return;
  }

  // Initialize current subjects map from DOM
  cards.forEach((card) => {
    const subjectId = extractSubjectIdFromUrl(card.dataset.url);
    if (subjectId) {
      currentSubjects.set(subjectId, {
        element: card,
        url: card.dataset.url,
      });
    }
  });

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

  // Initialize live updates
  initLiveUpdates();
}

/**
 * Extract subject ID from URL query string
 */
function extractSubjectIdFromUrl(url) {
  if (!url) return null;
  const match = url.match(/subject_id=(\d+)/);
  return match ? parseInt(match[1], 10) : null;
}

/**
 * Initialize live updates for subject cards
 */
function initLiveUpdates() {
  if (liveUpdateInitialized) return;

  const container = document.getElementById('subject-selection');
  if (!container) return;

  liveUpdateInitialized = true;

  liveUpdateService.subscribe('manage-grades-subjects', {
    endpoint: '/instructor/grades/subjects',
    channels: ['table.subjects'],
    events: ['.row.created', '.row.updated', '.row.deleted'],
    pollingInterval: 5000, // 5 seconds for faster updates
    onUpdate: (data, previousData) => {
      handleSubjectsUpdate(data, previousData);
    },
  });

}

/**
 * Handle subjects data update
 */
function handleSubjectsUpdate(data, previousData) {
  if (!data.subjects || !Array.isArray(data.subjects)) return;

  const container = document.getElementById('subject-selection');
  if (!container) return;

  const newSubjectsMap = new Map();
  data.subjects.forEach((subject) => {
    newSubjectsMap.set(subject.id, subject);
  });

  // Find new subjects (not in currentSubjects)
  const newSubjects = [];
  newSubjectsMap.forEach((subject, id) => {
    if (!currentSubjects.has(id)) {
      newSubjects.push(subject);
    }
  });

  // Find removed subjects (in currentSubjects but not in new data)
  const removedSubjects = [];
  currentSubjects.forEach((_, id) => {
    if (!newSubjectsMap.has(id)) {
      removedSubjects.push(id);
    }
  });

  // Find updated subjects (in both, check for changes)
  const updatedSubjects = [];
  newSubjectsMap.forEach((subject, id) => {
    if (currentSubjects.has(id)) {
      // Check if data changed
      const existingCard = currentSubjects.get(id).element;
      const existingCount = existingCard.querySelector('.badge')?.textContent?.match(/\d+/)?.[0];
      if (existingCount !== String(subject.students_count)) {
        updatedSubjects.push(subject);
      }
    }
  });

  // Add new subject cards with animation
  newSubjects.forEach((subject) => {
    addSubjectCard(container, subject);
  });

  // Remove deleted subject cards with animation
  removedSubjects.forEach((id) => {
    removeSubjectCard(id);
  });

  // Update existing cards
  updatedSubjects.forEach((subject) => {
    updateSubjectCard(subject);
  });

  // Update current subjects map
  currentSubjects.clear();
  newSubjectsMap.forEach((subject, id) => {
    const element = container.querySelector(`[data-subject-id="${id}"]`);
    if (element) {
      currentSubjects.set(id, { element, url: subject.url });
    }
  });

  // Show notification if new subjects were added
  if (newSubjects.length > 0) {
    showNewSubjectNotification(newSubjects);
  }
}

/**
 * Create and add a new subject card with animation
 */
function addSubjectCard(container, subject) {
  const colWrapper = document.createElement('div');
  colWrapper.className = 'col-md-4';
  colWrapper.style.opacity = '0';
  colWrapper.style.transform = 'translateY(20px) scale(0.95)';

  const statusBadge = getStatusBadge(subject.grade_status);

  colWrapper.innerHTML = `
    <div
      class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden subject-card-new"
      data-url="${subject.url}"
      data-subject-id="${subject.id}"
      style="cursor: pointer;"
      role="button"
      tabindex="0"
    >
      <div class="position-relative" style="height: 80px;">
        <div class="subject-circle position-absolute start-50 translate-middle"
          style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
          <h5 class="mb-0 text-white fw-bold">${escapeHtml(subject.subject_code)}</h5>
        </div>
      </div>

      <div class="card-body pt-5 text-center">
        <h6 class="fw-semibold mt-4 text-dark text-truncate" title="${escapeHtml(subject.subject_description)}">
          ${escapeHtml(subject.subject_description)}
        </h6>

        <div class="d-flex justify-content-between align-items-center mt-4 px-2">
          <span class="badge bg-light border text-secondary px-3 py-2 rounded-pill">
            👥 ${subject.students_count} Students
          </span>
          ${statusBadge}
        </div>
      </div>
    </div>
  `;

  container.appendChild(colWrapper);

  // Bind click events to the new card
  const card = colWrapper.querySelector('.subject-card');
  bindCardEvents(card);

  // Trigger animation after a brief delay (for CSS transition)
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      colWrapper.style.transition = 'opacity 0.4s ease-out, transform 0.4s ease-out';
      colWrapper.style.opacity = '1';
      colWrapper.style.transform = 'translateY(0) scale(1)';

      // Add glow effect
      card.classList.add('subject-card-glow');
      setTimeout(() => {
        card.classList.remove('subject-card-new', 'subject-card-glow');
      }, 3000);
    });
  });

  // Update tracking
  currentSubjects.set(subject.id, { element: card, url: subject.url });

}

/**
 * Remove a subject card with animation
 */
function removeSubjectCard(subjectId) {
  const cardData = currentSubjects.get(subjectId);
  if (!cardData || !cardData.element) return;

  const colWrapper = cardData.element.closest('.col-md-4');
  if (!colWrapper) return;

  // Animate out
  colWrapper.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
  colWrapper.style.opacity = '0';
  colWrapper.style.transform = 'translateY(-10px) scale(0.95)';

  setTimeout(() => {
    colWrapper.remove();
  }, 300);

  currentSubjects.delete(subjectId);
} 

/**
 * Update an existing subject card
 */
function updateSubjectCard(subject) {
  const cardData = currentSubjects.get(subject.id);
  if (!cardData || !cardData.element) return;

  const card = cardData.element;

  // Update student count
  const countBadge = card.querySelector('.badge.bg-light');
  if (countBadge) {
    const oldCount = countBadge.textContent.match(/\d+/)?.[0];
    if (oldCount !== String(subject.students_count)) {
      countBadge.innerHTML = `👥 ${subject.students_count} Students`;
      // Flash animation
      countBadge.classList.add('badge-updated');
      setTimeout(() => countBadge.classList.remove('badge-updated'), 1000);
    }
  }

  // Update status badge
  const statusBadgeContainer = card.querySelector('.d-flex.justify-content-between');
  if (statusBadgeContainer) {
    const existingStatusBadge = statusBadgeContainer.querySelector(
      '.badge:not(.bg-light)'
    );
    const newStatusHtml = getStatusBadge(subject.grade_status);
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = newStatusHtml;
    const newBadge = tempDiv.firstElementChild;

    if (existingStatusBadge && newBadge) {
      if (existingStatusBadge.textContent.trim() !== newBadge.textContent.trim()) {
        existingStatusBadge.replaceWith(newBadge);
        newBadge.classList.add('badge-updated');
        setTimeout(() => newBadge.classList.remove('badge-updated'), 1000);
      }
    }
  }
}

/**
 * Get status badge HTML based on grade status
 */
function getStatusBadge(status) {
  const badges = {
    completed: `<span class="badge px-3 py-2 fw-semibold text-uppercase rounded-pill bg-success">✔ Completed</span>`,
    pending: `<span class="badge px-3 py-2 fw-semibold text-uppercase rounded-pill bg-warning text-dark">⏳ Pending</span>`,
    not_started: `<span class="badge px-3 py-2 fw-semibold text-uppercase rounded-pill bg-secondary">⭕ Not Started</span>`,
  };
  return badges[status] || badges.not_started;
}

/**
 * Bind click/keyboard events to a subject card
 */
function bindCardEvents(card) {
  card.dataset.clickBound = 'true';

  const navigate = () => {
    const url = card.dataset.url;
    if (url) {
      window.location.href = url;
    }
  };

  card.addEventListener('click', (event) => {
    if (event.defaultPrevented) return;
    if (event.target.closest('a, button, input, label, select, textarea')) return;
    navigate();
  });

  card.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      navigate();
    }
  });
}

/**
 * Show notification for new subjects
 */
function showNewSubjectNotification(subjects) {
}


/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Cleanup live updates (call when leaving page)
 */
export function destroyManageGradesPage() {
  liveUpdateService.unsubscribe('manage-grades-subjects');
  currentSubjects.clear();
  liveUpdateInitialized = false;
}

/**
 * Show unsaved changes modal
 * @param {Function} onConfirm - Callback when user confirms leaving
 * @param {Function|null} onCancel - Optional callback when user cancels
 */
window.showUnsavedChangesModal = function (onConfirm, onCancel = null) {
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
    document.querySelector('#subject-selection')
  ) {
    initManageGradesPage();
  }
});

window.initManageGradesPage = initManageGradesPage;
