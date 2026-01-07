/**
 * VPAA Tutorial - Dashboard
 * Tutorial for the VPAA Dashboard page
 */

(function() {
    'use strict';

    // Wait for VPAATutorial to be available
    if (typeof window.VPAATutorial === 'undefined') {
        console.warn('VPAATutorial core not loaded. Dashboard tutorial registration deferred.');
        return;
    }

    // Register the dashboard tutorial
    window.VPAATutorial.registerTutorial('vpaa-dashboard', {
        title: 'VPAA Dashboard Overview',
        description: 'Learn how to navigate the VPAA dashboard and access academic management features',
        steps: [
            {
                target: '.container-fluid h2.fw-bold',
                title: 'Welcome to VPAA Dashboard',
                content: 'This is your central hub for overseeing academic operations and institutional management. As Vice President for Academic Affairs, you have access to comprehensive institutional data.',
                position: 'bottom'
            },
            {
                target: '.row.g-3 > .col-md-3:first-child .card',
                title: 'Total Departments',
                content: 'This card shows the total number of active departments in the institution. Click to access detailed department management.',
                position: 'bottom'
            },
            {
                target: '.row.g-3 > .col-md-3:nth-child(2) .card',
                title: 'Total Instructors',
                content: 'View the count of active faculty members across all departments. This helps you monitor staffing levels institution-wide.',
                position: 'bottom'
            },
            {
                target: '.row.g-3 > .col-md-3:nth-child(3) .card',
                title: 'Total Students',
                content: 'Shows the total number of enrolled students. Use this to track overall enrollment trends and institutional growth.',
                position: 'bottom'
            },
            {
                target: '.row.g-3 > .col-md-3:nth-child(4) .card',
                title: 'Academic Programs',
                content: 'Displays the estimated number of course offerings across all departments.',
                position: 'bottom'
            },
            {
                target: '.row.g-3.mt-1 > .col-lg-6:first-child > .card',
                title: 'Department Management Panel',
                content: 'This panel provides a quick overview of department status. View active departments and their operational metrics. Click "View All" to access the full department management page.',
                position: 'right'
            },
            {
                target: '.row.g-3.mt-1 > .col-lg-6:last-child > .card > .card-body > .mb-3',
                title: 'Quick Access Panel',
                content: 'Use these shortcuts to quickly navigate to frequently used sections: Departments, Instructors, and Students management.',
                position: 'bottom'
            },
            {
                target: '.row.g-3.mt-1 .col-md-4:first-child > a',
                title: 'Departments Quick Link',
                content: 'Click here to navigate to the departments overview page where you can see detailed information about each department.',
                position: 'bottom'
            },
            {
                target: '.row.g-3.mt-1 .col-md-4:nth-child(2) > a',
                title: 'Instructors Quick Link',
                content: 'Access the instructor management page to view and manage faculty across all departments.',
                position: 'bottom'
            },
            {
                target: '.row.g-3.mt-1 .col-md-4:nth-child(3) > a',
                title: 'Students Quick Link',
                content: 'Navigate to the student records page to browse students by department and course.',
                position: 'bottom'
            }
        ]
    });
})();
