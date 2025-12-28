/**
 * Chairperson Dashboard JavaScript
 * Handles tooltip initialization and card hover effects
 */

export function initChairpersonDashboard() {
  // Initialize all tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl, {
      trigger: 'hover',
    });
  });

  // Add hover effect to status cards
  document.querySelectorAll('.status-card').forEach(function (card) {
    card.addEventListener('mouseenter', function () {
      this.style.transform = 'translateY(-5px)';
    });

    card.addEventListener('mouseleave', function () {
      this.style.transform = 'translateY(0)';
    });
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (
    document.querySelector('[data-page="chairperson-dashboard"]') ||
    window.location.pathname.includes('/chairperson/dashboard')
  ) {
    initChairpersonDashboard();
  }
});

window.initChairpersonDashboard = initChairpersonDashboard;
