/**
 * VPAA Tutorial - Instructors
 * Tutorial for the VPAA Instructor Management page
 */

(function() {
    'use strict';

    // Wait for VPAATutorial to be available
    if (typeof window.VPAATutorial === 'undefined') {
        console.warn('VPAATutorial core not loaded. Instructors tutorial registration deferred.');
        return;
    }

    // Register the instructors tutorial
    window.VPAATutorial.registerTutorial('vpaa-instructors', {
        title: 'Instructor Management',
        description: 'Learn how to view, filter, and manage instructors across departments',
        steps: [
            {
                target: '.container-fluid h1, h1.h3',
                title: 'Instructor Management',
                content: 'This page allows you to view and manage all instructors across the institution. You can filter by department and view instructor details.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb',
                title: 'Navigation Breadcrumb',
                content: 'The breadcrumb shows your current location. Click "Departments" to go back to the departments overview.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'a.btn-outline-secondary[href*="departments"]',
                title: 'Back to Departments',
                content: 'Click this button to return to the departments overview page.',
                position: 'left',
                optional: true
            },
            {
                target: '.card.border-0.shadow-sm.rounded-4.mb-4, form select#department_id',
                title: 'Department Filter',
                content: 'Use this dropdown to filter instructors by department. Select a department to see only its faculty members, or choose "All Departments" to see everyone.',
                position: 'bottom'
            },
            {
                target: '.table thead, table.table-hover thead',
                title: 'Instructors Table',
                content: 'The table displays instructor information including: Name, Role, Department, Email, and Status. Each row represents one instructor.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-primary, .badge.bg-warning, .badge.bg-info',
                title: 'Role Badges',
                content: 'Each instructor has a role badge: Instructor (blue), Chairperson (yellow), Dean (cyan), Admin (red), GE Coordinator (green), or VPAA (dark). Roles determine their system permissions.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success, .badge.bg-danger',
                title: 'Status Indicator',
                content: 'The status badge shows whether the instructor account is Active (green) or Inactive (red).',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'instructors',
            noAddButton: true
        }
    });

    // Register the instructor edit tutorial
    window.VPAATutorial.registerTutorial('vpaa-instructors-edit', {
        title: 'Edit Instructor',
        description: 'Learn how to update instructor information',
        steps: [
            {
                target: '.bg-white.shadow, form',
                title: 'Edit Instructor Form',
                content: 'This form allows you to update instructor information including name, email, department assignment, and active status.',
                position: 'bottom'
            },
            {
                target: 'input[name="first_name"], input[name="last_name"]',
                title: 'Name Fields',
                content: 'Update the instructor\'s first and last name here. These fields are required.',
                position: 'bottom'
            },
            {
                target: 'input[name="email"]',
                title: 'Email Address',
                content: 'The instructor\'s email address is used for system login and notifications.',
                position: 'bottom'
            },
            {
                target: 'select[name="department_id"]',
                title: 'Department Assignment',
                content: 'Select which department this instructor belongs to. This affects their access to department-specific resources.',
                position: 'bottom'
            },
            {
                target: 'input[name="is_active"]',
                title: 'Active Status',
                content: 'Toggle this checkbox to activate or deactivate the instructor account. Inactive accounts cannot log in.',
                position: 'bottom'
            },
            {
                target: 'button[type="submit"]',
                title: 'Save Changes',
                content: 'Click "Update Instructor" to save your changes. You\'ll be redirected back to the instructors list.',
                position: 'top'
            }
        ]
    });
})();
