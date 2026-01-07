/**
 * VPAA Tutorial - Departments
 * Tutorial for the VPAA Departments Overview page
 */

(function() {
    'use strict';

    // Wait for VPAATutorial to be available
    if (typeof window.VPAATutorial === 'undefined') {
        console.warn('VPAATutorial core not loaded. Departments tutorial registration deferred.');
        return;
    }

    // Register the departments tutorial
    window.VPAATutorial.registerTutorial('vpaa-departments', {
        title: 'Departments Overview',
        description: 'Learn how to browse departments and view their instructors and students',
        steps: [
            {
                target: '.container-fluid h1, h1.h3',
                title: 'Departments Overview',
                content: 'This page displays all academic departments in the institution. Each department card shows key statistics and provides quick access to instructor management.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb',
                title: 'Breadcrumb Navigation',
                content: 'Use the breadcrumb trail to navigate back to the Dashboard or track your location within the VPAA portal.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-xl-3:first-child .card, .row.g-4 > div:first-child .card',
                title: 'Department Card',
                content: 'Each card represents an academic department. The card displays the department name and key statistics at a glance.',
                position: 'bottom'
            },
            {
                target: '.bg-success-subtle, .card .row.g-2 .col-6:first-child',
                title: 'Instructor Count',
                content: 'This shows the number of instructors assigned to the department. Monitor faculty distribution across departments.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.bg-info-subtle, .card .row.g-2 .col-6:last-child',
                title: 'Student Count',
                content: 'View the number of students enrolled in programs under this department.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.card[onclick*="vpaa.instructors"], .card:has(.bi-arrow-right-circle)',
                title: 'Click to View Instructors',
                content: 'Click anywhere on a department card to navigate to the instructor list filtered by that department. This allows you to quickly access faculty members.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
