/**
 * Admin Tutorial - Disaster Recovery
 * Tutorials for the Disaster Recovery and Backup Activity pages
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Disaster recovery tutorial registration deferred.');
        return;
    }

    // Register the disaster recovery main tutorial
    window.AdminTutorial.registerTutorial('admin-disaster-recovery', {
        title: 'Disaster Recovery & Backup Management',
        description: 'Learn how to create backups, configure automatic backups, restore data, and manage backup storage',
        steps: [
            {
                target: 'button[data-bs-target="#backupModal"], button.btn-success',
                title: 'Create Manual Backup',
                content: 'Click here to create a new backup immediately. Choose between Full Backup (all database tables and data) or Config Only (settings and configurations only).',
                position: 'left'
            },
            {
                target: 'a[href*="activity"]',
                title: 'Activity Log',
                content: 'View the complete backup history: when backups were created, who created them, downloads, restores, and deletions. Essential for audit trails.',
                position: 'left'
            },
            {
                target: '.col-xl-6:first-child .card',
                title: 'Storage Usage Monitor',
                content: 'Monitor your backup storage capacity. The progress bar shows how much space is used. Green is healthy, yellow is moderate, red means action needed.',
                position: 'right'
            },
            {
                target: '.col-md-6.col-xl-3:first-of-type .card',
                title: 'Total Backups Count',
                content: 'See how many backup files are stored on the server. Regular backups ensure data safety.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-md-6.col-xl-3:last-of-type .card',
                title: 'Last Backup Time',
                content: 'Shows when the most recent backup was created. If this is too old, consider creating a new backup or enabling automatic backups.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'select[name="frequency"]',
                title: 'Automatic Backup Frequency',
                content: 'Configure automatic backups: Disabled (manual only), Daily (every day), Weekly (every week), or Monthly (once per month). Automated backups ensure you never lose data.',
                position: 'right'
            },
            {
                target: 'input[name="time"], input[type="time"]',
                title: 'Backup Schedule Time',
                content: 'Set the time when automatic backups should run. Choose a low-activity period (e.g., early morning) to minimize system impact.',
                position: 'right'
            },
            {
                target: 'button[type="submit"][form*="schedule"], form[action*="schedule"] button',
                title: 'Save Backup Schedule',
                content: 'Click "Save Schedule" to apply your automatic backup settings. Changes take effect immediately.',
                position: 'right',
                optional: true
            },
            {
                target: '#runManualBackupForm button, button[onclick*="confirmRunNow"]',
                title: 'Run Manual Backup Now',
                content: 'Trigger an immediate backup regardless of the schedule. Useful before major changes or updates.',
                position: 'left',
                optional: true
            },
            {
                target: '.table-responsive table',
                title: 'Backups List',
                content: 'All backups are listed with: Type (Full/Config), Date & Time, Size, Number of Tables, and Creator. Newest backups appear first.',
                position: 'top'
            },
            {
                target: '.btn-outline-secondary[href*="download"], a[title="Download"]',
                title: 'Download Backup',
                content: 'Click the download icon to save a backup file to your computer. Store offline copies for extra protection against server failures.',
                position: 'left',
                optional: true
            },
            {
                target: '.btn-outline-warning, button[onclick*="showRestoreModal"]',
                title: 'Restore Backup',
                content: 'Click the restore icon to replace current database with this backup. WARNING: This overwrites all current data! You can optionally create a safety backup first.',
                position: 'left',
                optional: true
            },
            {
                target: '.btn-outline-danger, button[onclick*="showDeleteModal"]',
                title: 'Delete Backup',
                content: 'Remove a backup file permanently. Requires password confirmation. Deleted backups cannot be recovered.',
                position: 'left',
                optional: true
            },
            {
                target: '.list-group, .recent-activity',
                title: 'Recent Activity Panel',
                content: 'Quick view of recent backup activities. Click "View All" to see the complete activity log with more details.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register the disaster recovery activity log tutorial
    window.AdminTutorial.registerTutorial('admin-disaster-recovery-activity', {
        title: 'Backup Activity Log',
        description: 'Learn how to track all backup operations, filter by event type, and rollback changes',
        steps: [
            {
                target: 'select[name="event_type"], form select:first-of-type',
                title: 'Filter by Event Type',
                content: 'Filter the activity log by event type: All Events, Created (new backups), Restored (backup restorations), Downloaded, or Deleted.',
                position: 'bottom'
            },
            {
                target: 'input[type="date"], input[name*="date"]',
                title: 'Filter by Date Range',
                content: 'Set start and end dates to view activities within a specific time period. Useful for auditing and compliance reporting.',
                position: 'bottom'
            },
            {
                target: 'button[type="submit"], form button.btn-primary',
                title: 'Apply Filters',
                content: 'Click to apply your selected filters. The activity list will update to show only matching entries.',
                position: 'left',
                optional: true
            },
            {
                target: '.list-group, .activity-list',
                title: 'Activity Timeline',
                content: 'Each entry shows: What happened (created, restored, deleted), Who performed the action, When it occurred, and the IP address for security auditing.',
                position: 'top'
            },
            {
                target: '.list-group-item:first-child, .activity-item:first-child',
                title: 'Activity Entry Details',
                content: 'Click on any entry to see more details. Entries are color-coded: green for creates, yellow for restores, red for deletes.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.btn-outline-warning, button[onclick*="rollback"]',
                title: 'Rollback Changes',
                content: 'For restore operations, you may be able to rollback to the previous state. This requires password confirmation and creates a new backup point.',
                position: 'left',
                optional: true
            },
            {
                target: '.pagination, nav[aria-label*="pagination"]',
                title: 'Navigate Pages',
                content: 'Use pagination to browse through older activity entries. Activities are sorted newest-first.',
                position: 'top',
                optional: true
            },
            {
                target: 'a[href*="disaster-recovery"]:not([href*="activity"])',
                title: 'Back to Backups',
                content: 'Click here to return to the main Disaster Recovery page where you can create and manage backups.',
                position: 'left'
            }
        ]
    });
})();
