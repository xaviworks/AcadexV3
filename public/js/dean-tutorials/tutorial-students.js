/**
 * Dean Tutorial - Students
 * Tutorial for the Dean Students page
 */

(function() {
    'use strict';

    // Wait for DeanTutorial to be available
    if (typeof window.DeanTutorial === 'undefined') {
        console.warn('DeanTutorial core not loaded. Students tutorial registration deferred.');
        return;
    }

    // Register the students tutorial
    window.DeanTutorial.registerTutorial('dean-students', {
        title: 'Department Students',
        description: 'Learn how to view and filter students in your department',
        steps: [
            {
                target: 'h1.text-2xl',
                title: 'Students in Department',
                content: 'This page displays all students enrolled in programs under your department. You can filter by course to narrow down the list.',
                position: 'bottom'
            },
            {
                target: 'form select#courseFilter, form .form-select',
                title: 'Course Filter',
                content: 'Use this dropdown to filter students by course/program. Select a specific course to see only students enrolled in that program, or choose "All Courses" to view everyone.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '.table thead',
                title: 'Students Table',
                content: 'The table displays student information: Name, Course enrollment, and Year Level.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '.table tbody tr:first-child',
                title: 'Student Record',
                content: 'Each row shows a student\'s name (Last Name, First Name), their course code, and current year level.',
                position: 'bottom',
                optional: true,
                requiresData: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'students',
            noAddButton: true,
            emptySelectors: [
                '.table tbody tr td[colspan]',
                '.dataTables_empty'
            ]
        }
    });
})();
