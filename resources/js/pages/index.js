/**
 * Page Scripts Index
 * 
 * This file provides a registry of page-specific initialization functions.
 * Each page script should register itself via window exports.
 * 
 * Usage in Blade templates:
 * - Include the relevant page script via @vite or via script tag
 * - The script will auto-initialize when DOM is ready
 */

// Import all page scripts for bundling
// Admin pages
import './admin/users.js';
import './admin/sessions.js';
import './admin/subjects.js';
import './admin/departments.js';
import './admin/courses.js';

// Dashboard pages
import './dashboard/instructor.js';

// Instructor pages
import './instructor/manage-students.js';

// Chairperson pages
import './chairperson/manage-instructors.js';

// GE Coordinator pages
import './gecoordinator/manage-instructors.js';

/**
 * Initialize a page by name
 * This can be called from Blade templates to manually trigger initialization
 * @param {string} pageName - The page identifier
 */
export function initPage(pageName) {
    const initFunctions = {
        // Admin
        'admin.users': window.initAdminUsersPage,
        'admin.sessions': window.initSessionsPage,
        'admin.subjects': window.initAdminSubjectsPage,
        'admin.departments': window.initAdminDepartmentsPage,
        'admin.courses': window.initAdminCoursesPage,
        
        // Dashboard
        'dashboard.instructor': window.initSubjectPerformanceChart,
        
        // Instructor
        'instructor.manage-students': window.initManageStudentsPage,
        
        // Chairperson
        'chairperson.manage-instructors': window.initChairpersonManageInstructorsPage,
        
        // GE Coordinator
        'gecoordinator.manage-instructors': window.initGECoordinatorManageInstructorsPage,
    };

    const initFn = initFunctions[pageName];
    if (typeof initFn === 'function') {
        initFn();
    } else {
        console.warn(`No initialization function found for page: ${pageName}`);
    }
}

// Export for global access
window.initPage = initPage;
