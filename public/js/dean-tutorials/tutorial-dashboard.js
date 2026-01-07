/**
 * Dean Tutorial - Dashboard
 * Tutorial for the Dean Dashboard page
 */

(function() {
    'use strict';

    // Wait for DeanTutorial to be available
    if (typeof window.DeanTutorial === 'undefined') {
        console.warn('DeanTutorial core not loaded. Dashboard tutorial registration deferred.');
        return;
    }

    // Register the dashboard tutorial
    window.DeanTutorial.registerTutorial('dean-dashboard', {
        title: 'Dean Dashboard',
        description: 'Get familiar with your academic overview dashboard',
        steps: [
            {
                target: 'h2.fw-bold',
                title: 'Welcome to Your Dashboard',
                content: 'This is your Dean\'s Academic Overview dashboard. Here you can monitor academic performance and department statistics at a glance.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-3:first-child .card',
                title: 'Total Students',
                content: 'This card shows the total number of students across all departments. It gives you a quick overview of your student population.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-3:nth-child(2) .card',
                title: 'Total Instructors',
                content: 'View the total count of active faculty members. This helps you track your teaching staff.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-3:nth-child(3) .card',
                title: 'Total Courses',
                content: 'See how many active academic courses are being offered. Each course represents a program of study.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-3:nth-child(4) .card',
                title: 'Departments',
                content: 'The number of active departments under your oversight. Click to see department-specific details.',
                position: 'bottom'
            },
            {
                target: '.col-lg-8 .card',
                title: 'Course Distribution',
                content: 'This table shows how students are distributed across different courses. The progress bars visualize the relative enrollment in each program.',
                position: 'top'
            },
            {
                target: '.col-lg-4 .card',
                title: 'Department Overview',
                content: 'This panel provides a breakdown of students per department with visual progress indicators showing the distribution.',
                position: 'left'
            }
        ]
    });
})();
