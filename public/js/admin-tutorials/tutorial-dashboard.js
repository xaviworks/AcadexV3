/**
 * Admin Tutorial - Dashboard
 * Tutorial for the Admin Dashboard page
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Dashboard tutorial registration deferred.');
        return;
    }

    // Register the dashboard tutorial
    window.AdminTutorial.registerTutorial('admin-dashboard', {
        title: 'Admin Dashboard Overview',
        description: 'Learn how to monitor system activity, user statistics, login patterns, and security metrics',
        steps: [
            {
                target: '.container-fluid h2, .fw-bold.text-dark',
                title: 'Admin Control Panel',
                content: 'Welcome to the Admin Control Panel! This is your central hub for monitoring system health, user activity, and security metrics. The dashboard provides real-time insights into how users interact with Acadex.',
                position: 'bottom'
            },
            {
                target: '.hover-lift:first-child, .col-md-3:first-child .card',
                title: 'Total Users Card',
                content: 'This card displays the total number of registered accounts in the system, including Instructors, Chairpersons, Deans, GE Coordinators, VPAA, and Admins. Use this to track overall system adoption.',
                position: 'bottom'
            },
            {
                target: '.hover-lift:nth-child(2), .col-md-3:nth-child(2) .card',
                title: 'Successful Logins Today',
                content: 'Shows the count of successful login attempts for the current day. A healthy system should show consistent daily logins. Sudden drops may indicate technical issues or user problems.',
                position: 'bottom'
            },
            {
                target: '.hover-lift:nth-child(3), .col-md-3:nth-child(3) .card',
                title: 'Failed Login Attempts',
                content: 'SECURITY METRIC: Tracks failed login attempts today. Monitor this closely - sudden spikes could indicate: brute-force attacks, credential stuffing, or users having password issues. Consider enabling account lockouts if this is consistently high.',
                position: 'bottom'
            },
            {
                target: '.hover-lift:nth-child(4), .col-md-3:nth-child(4) .card',
                title: 'Active Users Percentage',
                content: 'Shows what percentage of registered users logged in today. This engagement metric helps you understand system utilization. Low percentages during academic periods may warrant investigation.',
                position: 'bottom'
            },
            {
                target: '.col-lg-8 .card, .card:has(.bi-graph-up)',
                title: 'Hourly Login Activity Panel',
                content: 'This detailed table breaks down login activity by hour. It shows successful logins (green badges), failed attempts (red badges), and success rate with visual progress bars. Use this to identify peak usage times.',
                position: 'bottom'
            },
            {
                target: '.table-responsive table thead',
                title: 'Activity Table Headers',
                content: 'The table columns show: Hour (12 AM to 11 PM), Successful Logins count, Failed Attempts count, and Success Rate percentage. Each row represents one hour of the selected day.',
                position: 'bottom'
            },
            {
                target: '.table-active, .table-responsive tbody tr:first-child',
                title: 'Peak Activity Hours',
                content: 'Highlighted rows (with subtle background) indicate peak activity hours - the times with highest total login attempts. Plan system maintenance and updates during off-peak hours to minimize user disruption.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.progress-bar, .progress',
                title: 'Success Rate Indicators',
                content: 'Visual progress bars show the success rate for each time period. Colors indicate health: Green (90%+) = Excellent, Blue (70-89%) = Good, Yellow (50-69%) = Needs attention, Red (<50%) = Investigate immediately.',
                position: 'left',
                optional: true
            },
            {
                target: '.col-lg-4 .card, .card:has(.bi-calendar-check)',
                title: 'Monthly Overview Panel',
                content: 'This panel shows login trends across the entire year. Compare month-over-month activity to identify seasonal patterns, such as increased usage during enrollment periods or reduced activity during breaks.',
                position: 'left'
            },
            {
                target: 'select[name="year"]',
                title: 'Year Selection Filter',
                content: 'Use this dropdown to view statistics from previous years. Compare year-over-year trends to track system growth and identify long-term patterns in user engagement.',
                position: 'left'
            },
            {
                target: '.col-lg-4 .bg-light, .col-lg-4 .mb-3:first-of-type',
                title: 'Monthly Statistics Display',
                content: 'Each month shows: successful logins (green badge), failed attempts (red badge), and a progress bar indicating success rate. Highlighted months indicate highest activity periods.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
