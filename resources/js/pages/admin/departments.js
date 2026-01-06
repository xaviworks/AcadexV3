/**
 * Admin - Departments Page JavaScript
 *
 * Note: The main functionality has been moved inline to the Blade template
 * for better integration with Laravel routes and CSRF tokens.
 *
 * This file is kept for backwards compatibility and DataTable initialization fallback.
 */

/**
 * Initialize the admin departments page
 * @deprecated Use inline script in departments.blade.php instead
 */
function initAdminDepartmentsPage() {
  // DataTable initialization is now handled in the Blade template
  // This function is kept for backwards compatibility
  console.log('[departments.js] Page initialization delegated to inline script.');
}

// Export for global access (backwards compatibility)
window.initAdminDepartmentsPage = initAdminDepartmentsPage;
