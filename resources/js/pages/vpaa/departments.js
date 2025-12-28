/**
 * VPAA Departments Page JavaScript
 * Handles tooltip initialization
 */

export function initVpaaDepartmentsPage() {
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  // Only initialize if we're on the VPAA departments page
  if (
    document.querySelector('[data-page="vpaa-departments"]') ||
    window.location.pathname.includes('/vpaa/departments')
  ) {
    initVpaaDepartmentsPage();
  }
});

window.initVpaaDepartmentsPage = initVpaaDepartmentsPage;
