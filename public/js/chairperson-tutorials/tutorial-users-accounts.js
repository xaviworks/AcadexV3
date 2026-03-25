/**
 * Chairperson Tutorial - Users/Accounts
 * Tutorial for the Chairperson Instructor Account Management page
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Users/Accounts tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-users-accounts', {
        title: 'Instructor Account Management',
        description: 'Learn how to manage instructor accounts and approvals in your department.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Users/Accounts Overview',
                content: 'This page is where you manage instructor user accounts for your department, including active, inactive, and pending accounts.',
                position: 'bottom'
            },
            {
                target: '#instructorTabs',
                title: 'Account Status Tabs',
                content: 'Use these tabs to switch between Active Instructors, Pending Approvals, and Inactive Instructors.',
                position: 'bottom'
            },
            {
                target: '#active-instructors-tab',
                title: 'Active Instructors',
                content: 'This tab lists currently active instructor accounts. You can request GE assignment access or deactivate accounts from here.',
                position: 'bottom'
            },
            {
                target: '#active-instructors .table thead',
                title: 'Active Accounts Table',
                content: 'The table shows instructor name, email, status, and available actions for each active account.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#active-instructors .btn[data-bs-target="#requestGEAssignmentModal"], #active-instructors .btn[data-bs-target="#confirmDeactivateModal"]',
                title: 'Account Actions',
                content: 'Use the action buttons to request GE assignment permissions or deactivate an instructor account.',
                position: 'left',
                optional: true
            },
            {
                target: '#pending-approvals-tab',
                title: 'Pending Approvals',
                content: 'Open this tab to review newly created instructor accounts that require your approval or rejection.',
                position: 'bottom'
            },
            {
                target: '#pending-approvals .table thead',
                title: 'Pending Accounts Table',
                content: 'Pending applications display applicant details such as name, email, department, and course before your decision.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#pending-approvals .btn[data-bs-target="#confirmApproveModal"], #pending-approvals .btn[data-bs-target="#confirmRejectModal"]',
                title: 'Approve or Reject',
                content: 'Use these actions to approve valid account applications or reject submissions that do not meet requirements.',
                position: 'left',
                optional: true
            },
            {
                target: '#inactive-instructors-tab',
                title: 'Inactive Instructors',
                content: 'This tab contains deactivated accounts. You can reactivate instructors whenever needed.',
                position: 'bottom'
            },
            {
                target: '#inactive-instructors .btn[data-bs-target="#confirmActivateModal"]',
                title: 'Reactivate Accounts',
                content: 'Use the Activate action to restore system access for an inactive instructor account.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
