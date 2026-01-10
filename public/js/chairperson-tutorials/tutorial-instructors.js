/**
 * Chairperson Tutorial - Instructor Management
 * Tutorial for the Manage Instructors page
 */

(function() {
    'use strict';

    // Wait for ChairpersonTutorial to be available
    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Instructors tutorial registration deferred.');
        return;
    }

    // Register the instructors tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-instructors', {
        title: 'Instructor Management',
        description: 'Learn how to manage instructor accounts in your department',
        tableDataCheck: {
            selector: '#active-instructors tbody tr',
            emptySelectors: ['.alert-warning'],
            entityName: 'instructors',
            noAddButton: true
        },
        steps: [
            {
                target: '.page-title h1, h1:has(.bi-person-lines-fill)',
                title: 'Instructor Account Management',
                content: 'Welcome to the Instructor Management page! Here you can view, activate, deactivate, and manage GE assignment requests for instructors in your department.',
                position: 'bottom'
            },
            {
                target: '#instructorTabs, .nav-tabs',
                title: 'Instructor Status Tabs',
                content: 'Use these tabs to switch between Active and Inactive instructors. Each tab shows instructors filtered by their current status in the system.',
                position: 'bottom'
            },
            {
                target: '#active-instructors-tab',
                title: 'Active Instructors Tab',
                content: 'This tab shows all currently active instructors who can teach courses. These faculty members have full system access.',
                position: 'bottom'
            },
            {
                target: '#inactive-instructors-tab',
                title: 'Inactive Instructors Tab',
                content: 'Click here to view deactivated instructors. You can reactivate them when needed to restore their system access.',
                position: 'bottom'
            },
            {
                target: '#active-instructors table, .table',
                title: 'Instructors Table',
                content: 'This table displays instructor details including name, email, status, and available actions. Each row represents one instructor.',
                position: 'top',
                requiresData: true
            },
            {
                target: '#active-instructors tbody tr:first-child td:nth-child(1)',
                title: 'Instructor Name',
                content: 'The instructor\'s full name is displayed here in "Last Name, First Name Middle Name" format for easy identification.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#active-instructors tbody tr:first-child td:nth-child(3) .badge',
                title: 'Status Badge',
                content: 'The status badge indicates whether the instructor is Active (can teach) or Inactive (temporarily disabled). Active instructors have full system access.',
                position: 'left',
                optional: true
            },
            {
                target: '#active-instructors tbody tr:first-child .btn-group, #active-instructors tbody tr:first-child td:last-child',
                title: 'Action Buttons',
                content: 'Use these action buttons to manage each instructor. You can request GE subject assignment, deactivate accounts, or view more options depending on their current status.',
                position: 'left',
                optional: true
            },
            {
                target: 'button[data-bs-target="#requestGEAssignmentModal"], .btn-primary:has(.bi-journal-plus)',
                title: 'Request GE Assignment',
                content: 'Click this button to request that an instructor be allowed to teach General Education subjects. The request will be sent to the GE Coordinator for approval.',
                position: 'left',
                optional: true
            },
            {
                target: 'button[data-bs-target="#confirmDeactivateModal"], .btn-danger',
                title: 'Deactivate Instructor',
                content: 'Click this button to deactivate an instructor\'s account. Deactivated instructors cannot access the system but their data is preserved.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register inactive instructors tab tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-instructors-inactive', {
        title: 'Inactive Instructors Management',
        description: 'Learn how to manage inactive instructor accounts',
        steps: [
            {
                target: '.page-title h1',
                title: 'Inactive Instructors View',
                content: 'You are viewing inactive instructors. These are faculty members who have been temporarily deactivated from the system.',
                position: 'bottom'
            },
            {
                target: '#inactive-instructors-tab',
                title: 'Inactive Tab Selected',
                content: 'The Inactive Instructors tab is currently active. Instructors listed here cannot access the system until reactivated.',
                position: 'bottom'
            },
            {
                target: '#inactive-instructors table, #inactive-instructors .alert-warning',
                title: 'Inactive Instructors List',
                content: 'This section shows all deactivated instructors. You can reactivate any of them by clicking the Activate button in the Action column.',
                position: 'top'
            },
            {
                target: '.btn-success:has(.bi-check-circle), button[data-bs-target="#confirmActivateModal"]',
                title: 'Reactivate Instructor',
                content: 'Click the Activate button to restore an instructor\'s access to the system. They will immediately be able to log in and access their courses.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register create instructor tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-instructors-create', {
        title: 'Add New Instructor',
        description: 'Learn how to add a new instructor to your department',
        steps: [
            {
                target: 'h1:has(.bi-person-plus-fill), h1',
                title: 'Add New Instructor',
                content: 'Use this form to create a new instructor account for your department. Fill in all required fields to register the instructor.',
                position: 'bottom'
            },
            {
                target: 'input[name="first_name"]',
                title: 'Instructor Name',
                content: 'Enter the instructor\'s first name, middle name (optional), and last name. These will be used throughout the system.',
                position: 'right',
                optional: true
            },
            {
                target: 'input[name="email"]',
                title: 'Email Username',
                content: 'Enter only the username part of the email. The @brokenshire.edu.ph domain will be added automatically.',
                position: 'right',
                optional: true
            },
            {
                target: 'select[name="department_id"]',
                title: 'Department Selection',
                content: 'Select the department this instructor will belong to. This determines which courses they can be assigned.',
                position: 'right',
                optional: true
            },
            {
                target: 'select[name="course_id"]',
                title: 'Course Selection',
                content: 'Select the course/program the instructor will be primarily associated with.',
                position: 'right',
                optional: true
            },
            {
                target: 'button[type="submit"], input[type="submit"]',
                title: 'Create Account',
                content: 'Click to create the instructor account. A temporary password will be generated and can be shared with the instructor.',
                position: 'top',
                optional: true
            }
        ]
    });
})();
