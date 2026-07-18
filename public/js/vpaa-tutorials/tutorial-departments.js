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
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Departments Overview',
                content: 'This page displays all academic departments in the institution. Each department card shows key statistics and provides quick access to instructor management.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"]',
                title: 'Breadcrumb Navigation',
                content: 'Use this page subtitle and navigation trail to confirm you are in the Departments area and orient yourself within the VPAA portal.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.row.g-4, .row.g-4 > div:first-child .card',
                title: 'Departments Grid',
                content: 'Departments are displayed as cards in this grid. Select any card to open the instructor list filtered to that department.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > div:first-child .card, .col-xl-3:first-child .card',
                title: 'Department Card',
                content: 'Each card shows the department name and quick stats so you can compare departments at a glance.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 .bg-success-subtle, .card .row.g-2 .col-6:first-child',
                title: 'Instructor Count',
                content: 'This shows the number of instructors assigned to the department. Monitor faculty distribution across departments.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.row.g-4 .bg-info-subtle, .card .row.g-2 .col-6:last-child',
                title: 'Student Count',
                content: 'View the number of students enrolled in programs under this department.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.row.g-4 .card[onclick*="vpaa.instructors"], .row.g-4 .card:has(.bi-arrow-right-circle)',
                title: 'Click to View Instructors',
                content: 'Click anywhere on a department card to navigate to the instructor list filtered by that department. This allows you to quickly access faculty members.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
