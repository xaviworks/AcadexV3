/**
 * Dean Tutorial - Instructors
 * Tutorial for the Dean Instructors page
 */

(function() {
    'use strict';

    // Wait for DeanTutorial to be available
    if (typeof window.DeanTutorial === 'undefined') {
        console.warn('DeanTutorial core not loaded. Instructors tutorial registration deferred.');
        return;
    }

    // Register the instructors tutorial
    window.DeanTutorial.registerTutorial('dean-instructors', {
        title: 'Department Instructors',
        description: 'Learn how to view instructors in your department',
        steps: [
            {
                target: 'h1.text-2xl',
                title: 'Instructors in Department',
                content: 'This page displays all instructors assigned to your department. As Dean, you can view their information and status.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Instructors Table',
                content: 'The table shows instructor details: Name, Email, Course assignment, and their account Status (Active/Deactivated).',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child td:first-child',
                title: 'Instructor Name',
                content: 'Each row displays an instructor\'s full name.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table tbody tr:first-child td:nth-child(3)',
                title: 'Course Assignment',
                content: 'Shows which course/program the instructor is assigned to teach.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success-subtle, .badge.bg-danger-subtle',
                title: 'Status Indicator',
                content: 'The status badge shows whether the instructor account is Active (green) or Deactivated (red). Deactivated instructors cannot access the system.',
                position: 'left',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'instructors',
            noAddButton: true
        }
    });
})();
