/**
 * Admin - Subjects Page JavaScript
 *
 * Handles:
 * - DataTable initialization for subjects list
 * - Department-Course filtering cascade
 */

/**
 * Initialize the admin subjects page
 */
function initAdminSubjectsPage() {
  // Initialize DataTable
  if ($.fn.DataTable && $('#subjectsTable').length) {
    $('#subjectsTable').DataTable({
      order: [[1, 'asc']], // Sort by Code
      language: {
        search: '_INPUT_',
        searchPlaceholder: 'Search courses...',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ courses',
        emptyTable: 'No courses found',
      },
    });
  }

  // Filter courses based on selected department
  $('#department-select').on('change', function () {
    const departmentId = $(this).val();
    const courseSelect = $('#course-select');

    // Reset course selection
    courseSelect.val('');

    // Show/hide courses based on department
    courseSelect.find('option').each(function () {
      const $option = $(this);
      if (!departmentId || $option.val() === '' || $option.data('department') == departmentId) {
        $option.show();
      } else {
        $option.hide();
      }
    });
  });
}

// Export for global access
window.initAdminSubjectsPage = initAdminSubjectsPage;

// Auto-initialize when DOM is ready (use standard addEventListener to avoid jQuery timing issues)
document.addEventListener('DOMContentLoaded', initAdminSubjectsPage);
