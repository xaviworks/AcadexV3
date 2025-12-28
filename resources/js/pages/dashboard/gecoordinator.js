/**
 * GE Coordinator Dashboard JavaScript
 * Handles tooltip and popover initialization
 */

export function initGECoordinatorDashboard() {
  // Initialize all tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl, {
      trigger: 'hover',
    });
  });

  // Initialize popovers
  const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
  popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (
    document.querySelector('[data-page="gecoordinator-dashboard"]') ||
    window.location.pathname.includes('/gecoordinator/dashboard')
  ) {
    initGECoordinatorDashboard();
  }
});

window.initGECoordinatorDashboard = initGECoordinatorDashboard;
