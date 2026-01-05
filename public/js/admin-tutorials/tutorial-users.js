/**
 * Admin Tutorial - Users Management
 * Tutorial for the User Management page
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Users tutorial registration deferred.');
        return;
    }

    // Register the users tutorial
    window.AdminTutorial.registerTutorial('admin-users', {
        title: 'User Management',
        description: 'Learn how to add users, manage roles, disable accounts, reset 2FA, and force logout',
        steps: [
            {
                target: 'button.btn-success',
                title: 'Add New User',
                content: 'Click this button to add a new system user. You can create Instructors, Chairpersons, Deans, Admins, GE Coordinators, or VPAA accounts. Each role has different permissions.',
                position: 'left'
            },
            {
                target: '#usersTable thead',
                title: 'Users Table Overview',
                content: 'The table displays all users with: Name, Email, Role (color-coded badge), Account Status (Active/Disabled), 2FA Status (enabled/disabled), and available Actions.',
                position: 'bottom'
            },
            {
                target: '#usersTable_filter input, .dataTables_filter input',
                title: 'Search Users',
                content: 'Use the search box to quickly find users by name, email, or any visible field. Results filter in real-time as you type.',
                position: 'left',
                optional: true
            },
            {
                target: '.role-badge, .badge',
                title: 'User Roles',
                content: 'Each user has a role badge: Instructor (blue), Chairperson (teal), Dean (purple), Admin (red), GE Coordinator (orange), or VPAA (indigo). Roles determine system permissions.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.action-btn.btn-disable, button[onclick*="disable"]',
                title: 'Disable Account',
                content: 'Click the ban icon to temporarily disable a user account. Choose duration: 1 Week, 1 Month, Indefinite, or Custom date/time. The user will be logged out immediately.',
                position: 'left',
                optional: true
            },
            {
                target: '.action-btn.btn-enable, button[onclick*="enable"]',
                title: 'Enable Account',
                content: 'For disabled accounts, click the checkmark to re-enable access. The user can log in again immediately after enabling.',
                position: 'left',
                optional: true
            },
            {
                target: '.action-btn.btn-reset-2fa, button[onclick*="Reset2FA"]',
                title: 'Reset Two-Factor Authentication',
                content: 'If a user loses access to their authenticator app, click the shield icon to reset their 2FA. They will need to set up 2FA again on next login.',
                position: 'left',
                optional: true
            },
            {
                target: '.your-session-badge',
                title: 'Your Account',
                content: 'Your own account is marked with "You" badge. You cannot disable or modify your own account from this interface for security reasons.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
