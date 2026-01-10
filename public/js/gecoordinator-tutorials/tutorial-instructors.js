/**
 * GE Coordinator Tutorial - Instructor Management
 * Tutorial for the Manage Instructors page
 */

(function() {
    'use strict';

    // Wait for GECoordinatorTutorial to be available
    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Instructors tutorial registration deferred.');
        return;
    }

    // Register the main instructors tutorial (Active tab)
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-instructors', {
        title: 'Instructor Account Management',
        description: 'Learn how to manage GE instructor accounts, activations, and requests',
        tableDataCheck: {
            selector: '#active-instructors tbody tr',
            emptySelectors: ['.alert-warning'],
            entityName: 'instructors',
            noAddButton: true
        },
        steps: [
            {
                target: '.page-title h1, h1.text-3xl',
                title: 'Instructor Management Hub',
                content: 'Welcome to the Instructor Account Management page. Here you manage all GE faculty accounts, including approvals, activations, deactivations, and GE course access requests.',
                position: 'bottom'
            },
            {
                target: '#instructorTabs, .nav-tabs',
                title: 'Management Tabs',
                content: 'Four tabs organize your instructor management tasks: Active Instructors (currently teaching), Inactive Instructors (on leave/deactivated), Pending Approvals (awaiting verification), and GE Courses Requests (access requests).',
                position: 'bottom'
            },
            {
                target: '#active-instructors-tab',
                title: 'Active Instructors Tab',
                content: 'This tab shows all currently active GE faculty members who can teach courses. The badge shows the total count of active instructors.',
                position: 'bottom'
            },
            {
                target: '#active-instructors .table-responsive, #active-instructors table',
                title: 'Active Instructors Table',
                content: 'Lists all active instructors with their names, email addresses, and available actions. Instructors with "GE Access" badge are from other departments but have permission to teach GE courses.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#active-instructors tbody tr:first-child',
                title: 'Instructor Entry',
                content: 'Each row shows the instructor\'s full name, email, and action buttons. GE department instructors can be deactivated, while instructors from other departments have "Remove GE Access" option.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: '#active-instructors tbody tr:first-child .btn-danger:contains("Remove GE Access"), #active-instructors tbody tr:first-child button[data-bs-target*="RemoveGEAccess"]',
                title: 'Remove GE Access Action',
                content: 'Click this button to remove GE course access from an instructor who is primarily from another department. This will revoke their ability to teach GE courses while keeping their main department role unaffected.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '#inactive-instructors-tab',
                title: 'Inactive Instructors Tab',
                content: 'Switch to this tab to view and manage instructors who are on leave or have been deactivated. You can reactivate accounts from here.',
                position: 'bottom'
            },
            {
                target: '#pending-approvals-tab',
                title: 'Pending Approvals Tab',
                content: 'This tab shows unverified instructor accounts awaiting your approval. Review and approve or reject instructor registration requests here.',
                position: 'bottom'
            },
            {
                target: '#ge-requests-tab',
                title: 'GE Courses Requests Tab',
                content: 'View and manage requests from instructors in other departments who want access to teach GE courses. Approve or deny these cross-department teaching requests.',
                position: 'bottom'
            }
        ]
    });

    // Register the inactive instructors tutorial
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-instructors-inactive', {
        title: 'Inactive Instructors Management',
        description: 'Learn how to manage and reactivate inactive instructor accounts',
        tableDataCheck: {
            selector: '#inactive-instructors tbody tr',
            emptySelectors: ['.alert-warning'],
            entityName: 'inactive instructors',
            noAddButton: true
        },
        steps: [
            {
                target: '#inactive-instructors-tab',
                title: 'Inactive Instructors Tab',
                content: 'You are viewing the Inactive Instructors tab. This shows all GE faculty members who are currently on leave or have been deactivated.',
                position: 'bottom'
            },
            {
                target: '#inactive-instructors .table-responsive, #inactive-instructors table',
                title: 'Inactive Instructors Table',
                content: 'This table lists all inactive instructors with their name, email, status badge, and reactivation actions.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#inactive-instructors .badge:contains("Inactive")',
                title: 'Inactive Status Badge',
                content: 'The "Inactive" badge indicates that this instructor account is currently deactivated and cannot access the system.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-success:contains("Activate"), button[data-bs-target*="Activate"]',
                title: 'Activate Button',
                content: 'Click this button to reactivate an instructor account. Once reactivated, the instructor will be able to log in and access their subjects again.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ]
    });

    // Register the pending approvals tutorial
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-instructors-pending', {
        title: 'Pending Account Approvals',
        description: 'Learn how to review and approve instructor registration requests',
        tableDataCheck: {
            selector: '#pending-approvals tbody tr',
            emptySelectors: ['.alert-info', '.alert-warning'],
            entityName: 'pending accounts',
            noAddButton: true
        },
        steps: [
            {
                target: '#pending-approvals-tab',
                title: 'Pending Approvals Tab',
                content: 'This tab shows all instructor accounts awaiting your verification and approval. Review each registration carefully before approving.',
                position: 'bottom'
            },
            {
                target: '#pending-approvals .table-responsive, #pending-approvals table',
                title: 'Pending Accounts Table',
                content: 'Lists all unverified instructor accounts with their registration information and approval actions. Review each account carefully.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#pending-approvals tbody tr:first-child',
                title: 'Pending Account Entry',
                content: 'Each row shows the instructor\'s name, email, employee ID, registration date, and approval actions. Verify the information matches official records.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-success:contains("Approve"), button.btn-success',
                title: 'Approve Button',
                content: 'Click to approve this instructor account. Once approved, the instructor can log in and access the system immediately.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-danger:contains("Reject"), button.btn-danger',
                title: 'Reject Button',
                content: 'Click to reject an instructor registration if the information is invalid or the person should not have GE teaching access. Provide a clear rejection reason.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ]
    });

    // Register the GE requests tutorial
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-instructors-ge-requests', {
        title: 'GE Courses Access Requests',
        description: 'Learn how to manage requests from instructors to teach GE courses',
        tableDataCheck: {
            selector: '#ge-requests tbody tr',
            emptySelectors: ['.alert-info', '.alert-warning'],
            entityName: 'GE requests',
            noAddButton: true
        },
        steps: [
            {
                target: '#ge-requests-tab',
                title: 'GE Courses Requests Tab',
                content: 'This tab shows requests from instructors in other departments who want permission to teach General Education courses.',
                position: 'bottom'
            },
            {
                target: '#ge-requests .table-responsive, #ge-requests table',
                title: 'GE Requests Table',
                content: 'Lists all pending requests with instructor information, their home department, request date, and approval actions.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#ge-requests tbody tr:first-child',
                title: 'Request Entry',
                content: 'Each row shows the requesting instructor\'s name, their current department, email, when they requested access, and available actions.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: '#ge-requests .btn-success',
                title: 'Approve GE Access',
                content: 'Click to grant this instructor access to teach GE courses. They will be able to be assigned GE subjects while maintaining their primary department affiliation.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '#ge-requests .btn-danger',
                title: 'Deny GE Access',
                content: 'Click to deny the GE teaching request. Provide a clear reason for denial so the instructor and their department are informed.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ]
    });
})();
