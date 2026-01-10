/**
 * Chairperson Tutorial - Students by Year
 * Tutorial for the Students List page
 */

(function() {
    'use strict';

    // Wait for ChairpersonTutorial to be available
    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Students tutorial registration deferred.');
        return;
    }

    // Register the students tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-students', {
        title: 'Students List by Year',
        description: 'Learn how to view and filter students in your department',
        tableDataCheck: {
            selector: '#all-years tbody tr',
            emptySelectors: ['.alert-warning', '.bg-warning'],
            entityName: 'students',
            noAddButton: true
        },
        steps: [
            {
                target: '.page-title h1, h1:has(.bi-people-fill)',
                title: 'Students List',
                content: 'Welcome to the Students List page! Here you can view all students enrolled in your department and filter them by year level.',
                position: 'bottom'
            },
            {
                target: '.page-subtitle, .page-title p',
                title: 'Page Description',
                content: 'This page displays students under your department and course. Use the tabs to filter by specific year levels.',
                position: 'bottom'
            },
            {
                target: '#yearTabs, .nav-tabs',
                title: 'Year Level Filter Tabs',
                content: 'Use these tabs to filter students by year level. Click any tab to see only students in that specific year.',
                position: 'bottom'
            },
            {
                target: '#all-years-tab',
                title: 'All Years Tab',
                content: 'The "All Years" tab shows every student in your department regardless of year level. This is the default view.',
                position: 'bottom'
            },
            {
                target: '#first-year-tab',
                title: '1st Year Filter',
                content: 'Click here to filter and show only 1st year students. Other year tabs work the same way.',
                position: 'bottom'
            },
            {
                target: '#all-years table, .table',
                title: 'Students Table',
                content: 'This table displays student information including name, course, and year level. Data is organized alphabetically by last name.',
                position: 'top',
                requiresData: true
            },
            {
                target: '#all-years thead th:nth-child(1), table thead th:first-child',
                title: 'Student Name Column',
                content: 'Student names are displayed in "Last Name, First Name" format for easy alphabetical sorting and identification.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#all-years thead th:nth-child(2), table thead th:nth-child(2)',
                title: 'Course Column',
                content: 'Shows the course code each student is enrolled in. This helps identify students from different programs within your department.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#all-years tbody tr:first-child td:last-child .badge, .badge.bg-success-subtle',
                title: 'Year Level Badge',
                content: 'The year level badge shows each student\'s current academic year (1st, 2nd, 3rd, or 4th year) with a color-coded indicator.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
