/**
 * Admin Tutorial - Sessions Management
 * Tutorial for the Session & Activity Monitor page
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Sessions tutorial registration deferred.');
        return;
    }

    // Register the sessions tutorial
    window.AdminTutorial.registerTutorial('admin-sessions', {
        title: 'Session & Activity Monitor',
        description: 'Learn how to monitor active sessions, view logs, revoke sessions, and reset 2FA',
        steps: [
            {
                target: 'button.btn-danger.btn-sm',
                title: 'Revoke All Sessions (Emergency)',
                content: 'CAUTION: This emergency button terminates ALL active sessions except your own. Use only for security incidents like suspected breaches. Requires password confirmation.',
                position: 'left'
            },
            {
                target: '#sessions-tab',
                title: 'Active Sessions Tab',
                content: 'This tab shows all currently logged-in users with their session details: device type, browser, platform, IP address, and last activity timestamp.',
                position: 'bottom'
            },
            {
                target: '#logs-tab',
                title: 'User Logs Tab',
                content: 'Switch here to view the authentication history log. See login attempts, logouts, failed logins, and session revocations with timestamps.',
                position: 'bottom'
            },
            {
                target: '.session-status-badge, .session-status-current',
                title: 'Session Status',
                content: 'Sessions are marked as: Current (your session, highlighted), Active (online users), or Expired. Current sessions cannot be revoked.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.device-icon-wrapper, .device-icon',
                title: 'Device Information',
                content: 'See what device each user is on: Desktop, Tablet, or Mobile. This helps identify suspicious logins from unexpected devices.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'code.text-muted, .device-fingerprint',
                title: 'Device Fingerprint',
                content: 'Each device has a unique fingerprint for security tracking. Hover to see the full fingerprint. Mismatched fingerprints may indicate session hijacking.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.action-btn.btn-revoke, button[onclick*="confirmRevoke"]',
                title: 'Revoke Individual Session',
                content: 'Click the user-slash icon to force logout a specific user session. Requires password confirmation. The user will need to log in again.',
                position: 'left',
                optional: true
            },
            {
                target: '#logs-pane form, form[action*="logs"]',
                title: 'Filter Logs by Date',
                content: 'In the User Logs tab, use the date filter to view authentication events for a specific time period. Helpful for security audits.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge[class*="login"], .badge[class*="logout"]',
                title: 'Event Types',
                content: 'Log entries show event types: login (successful), logout (user logout), failed_login (wrong password), session_revoked (admin action), all_sessions_revoked, bulk_sessions_revoked.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
