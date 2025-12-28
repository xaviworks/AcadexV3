/**
 * Admin - Courses (Programs) Page JavaScript
 *
 * Handles:
 * - DataTable initialization for courses/programs list
 * - Course modal handling
 */

/**
 * Show the course modal
 */
function showCourseModal() {
  const modalEl = document.getElementById('courseModal');
  if (modalEl) {
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
  }
}

/**
 * Initialize the admin courses page
 */
function initAdminCoursesPage() {
  // Initialize DataTable
  if ($.fn.DataTable && $('#coursesTable').length) {
    $('#coursesTable').DataTable({
      order: [
        [2, 'asc'],
        [0, 'asc'],
      ], // Sort by Department then Code
      language: {
        search: '_INPUT_',
        searchPlaceholder: 'Search programs...',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ programs',
        emptyTable: 'No programs found',
      },
    });
  }
}

// Export for global access
window.showCourseModal = showCourseModal;
window.showModal = showCourseModal; // Alias for onclick handlers
window.initAdminCoursesPage = initAdminCoursesPage;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('coursesTable')) {
    initAdminCoursesPage();
  }
});
