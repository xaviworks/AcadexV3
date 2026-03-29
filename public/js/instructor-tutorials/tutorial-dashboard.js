/**
 * Instructor Tutorial - Dashboard
 * Tutorial for the Instructor Dashboard page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Dashboard tutorial registration deferred.');
        return;
    }

    // Register the dashboard tutorial
    window.InstructorTutorial.registerTutorial('instructor-dashboard', {
        title: 'Instructor Dashboard Overview',
        description: 'Learn how to navigate your dashboard and access your subjects',
        steps: [
            {
                target: 'h2.fw-bold, .d-flex.justify-content-between h2',
                title: 'Welcome to Your Dashboard',
                content: 'This is your Instructor Dashboard - your central hub for managing students, grades, activities, and course outcomes for all your subjects.',
                position: 'bottom'
            },
            {
                target: '.card:has(.bi-people-fill), .col-md-3:first-child .card',
                title: 'Total Students',
                content: 'This card shows the total number of students currently enrolled across all your subjects. Keep track of your overall student count at a glance.',
                position: 'bottom'
            },
            {
                target: '.card:has(.bi-journal-text), .col-md-3:nth-child(2) .card',
                title: 'Course Load',
                content: 'View how many subjects you\'re teaching in the current semester. This helps you monitor your teaching workload.',
                position: 'bottom'
            },
            {
                target: '.card:has(.bi-check-circle-fill)',
                title: 'Students Passed',
                content: 'Track the number of students who have passed based on final grades. This metric shows your teaching effectiveness.',
                position: 'bottom'
            },
            {
                target: '.card:has(.bi-x-circle-fill)',
                title: 'Students Failed',
                content: 'Monitor students who need additional support. Use this to identify areas where students may be struggling.',
                position: 'bottom'
            },
            {
                target: '.card:has(canvas), .chart-container',
                title: 'Subject Charts',
                content: 'Visual charts showing grade distribution and term completion progress for each of your subjects. Use these to track student performance trends.',
                position: 'top',
                optional: true
            },
            {
                target: '.card:has(.bi-graph-up-arrow), .col-lg-5 .card',
                title: 'Grading Progress',
                content: 'This section tracks grading completion for each term. Use it to see how many grades have already been submitted and which term still needs attention.',
                position: 'right',
                optional: true
            }
        ]
    });
})();
