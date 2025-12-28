/**
 * Instructor Activities Create Page JavaScript
 * Handles tooltips, delete modal, and form validation
 */

export function initActivitiesCreatePage() {
  // Initialize Bootstrap tooltips
  const bootstrapLib = window.bootstrap ?? null;
  if (bootstrapLib) {
    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach((triggerEl) => new bootstrapLib.Tooltip(triggerEl));
  }

  // Delete modal handler
  const deleteModal = document.getElementById('confirmDeleteModal');
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const activityId = button?.getAttribute('data-activity-id');
      const activityTitle = button?.getAttribute('data-activity-title') ?? 'this activity';

      const form = deleteModal.querySelector('#deleteActivityForm');
      if (form && activityId) {
        form.action = `/instructor/activities/${activityId}`;
      }

      const placeholder = deleteModal.querySelector('#activityTitlePlaceholder');
      if (placeholder) {
        placeholder.textContent = activityTitle;
      }
    });
  }

  // Create modal auto-focus
  const createActivityModal = document.getElementById('createActivityModal');
  if (createActivityModal) {
    createActivityModal.addEventListener('shown.bs.modal', () => {
      const firstInput = createActivityModal.querySelector('input[name="title"]');
      if (firstInput) {
        firstInput.focus();
      }
    });
  }

  // Form validation
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach((form) => {
    form.addEventListener(
      'submit',
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      },
      false
    );
  });

  // Add hover effects to table rows
  const tableRows = document.querySelectorAll('table tbody tr');
  tableRows.forEach((row) => {
    row.addEventListener('mouseenter', () => {
      row.style.backgroundColor = '#f8f9fa';
    });
    row.addEventListener('mouseleave', () => {
      row.style.backgroundColor = '';
    });
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (
    document.querySelector('[data-page="instructor-activities-create"]') ||
    window.location.pathname.includes('/instructor/activities')
  ) {
    initActivitiesCreatePage();
  }
});

window.initActivitiesCreatePage = initActivitiesCreatePage;
