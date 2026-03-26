/**
 * GE Coordinator Tutorial - Accounts/Users
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Instructors tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-instructors', {
        title: 'Instructor Account Management',
        description: 'Manage active, inactive, pending, and GE request workflows.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Accounts/Users Overview',
                content: 'This page is where GE Coordinator manages instructor accounts and GE course requests.',
                position: 'bottom'
            },
            {
                target: '#instructorTabs',
                title: 'Management Tabs',
                content: 'Switch between Active, Inactive, Pending Approvals, and GE Course Requests tabs.',
                position: 'bottom'
            },
            {
                target: '#active-instructors .table thead',
                title: 'Active Instructors',
                content: 'Use action buttons here to deactivate GE department users or remove GE access.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#pending-approvals .table thead',
                title: 'Pending Approvals',
                content: 'Review instructor registration requests and approve or reject entries.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#ge-requests .table thead, #ge-requests .table',
                title: 'GE Course Requests',
                content: 'Process requests from instructors asking for GE subject handling access.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-instructors-create', {
        title: 'Create Instructor Account',
        description: 'Create a new instructor account for GE workflows.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Add New Instructor',
                content: 'Use this form to create a new instructor account.',
                position: 'bottom'
            },
            {
                target: 'form[action*="/gecoordinator/instructors"]',
                title: 'Instructor Form',
                content: 'Complete required identity, department/course, and password fields before submitting.',
                position: 'bottom'
            },
            {
                target: 'button[type="submit"]',
                title: 'Submit for Approval',
                content: 'Submit the form to create the instructor account request.',
                position: 'top',
                optional: true
            }
        ]
    });
})();
