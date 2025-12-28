/**
 * Admin - Departments Page JavaScript
 *
 * Handles:
 * - DataTable initialization for departments list
 * - Department modal handling
 */

/**
 * Show the department modal
 */
function showDepartmentModal() {
  const modalEl = document.getElementById('departmentModal');
  if (modalEl) {
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
  }
}

/**
 * Initialize the admin departments page
 */
function initAdminDepartmentsPage() {
  // Initialize DataTable
  if ($.fn.DataTable && $('#departmentsTable').length) {
    $('#departmentsTable').DataTable({
      order: [[1, 'asc']], // Sort by Code by default
      language: {
        search: '_INPUT_',
        searchPlaceholder: 'Search departments...',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ departments',
        emptyTable: 'No departments found',
      },
    });
  }
}

// Export for global access
window.showDepartmentModal = showDepartmentModal;
window.showModal = showDepartmentModal; // Alias for onclick handlers
window.initAdminDepartmentsPage = initAdminDepartmentsPage;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('departmentsTable')) {
    initAdminDepartmentsPage();
  }
});
