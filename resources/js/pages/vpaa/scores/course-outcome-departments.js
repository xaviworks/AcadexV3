/**
 * VPAA Course Outcome Departments Page JavaScript
 * Handles department card click navigation
 */

export function initVpaaCourseOutcomeDepartmentsPage() {
  document.querySelectorAll('#department-selection .subject-card[data-url]').forEach((card) => {
    card.addEventListener('click', () => {
      window.location.href = card.dataset.url;
    });
  });
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initVpaaCourseOutcomeDepartmentsPage);

// Expose function globally
window.initVpaaCourseOutcomeDepartmentsPage = initVpaaCourseOutcomeDepartmentsPage;
