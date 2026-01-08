/**
 * VPAA Tutorial - Grades
 * Tutorial for the VPAA Final Grades page
 */

(function() {
    'use strict';

    // Wait for VPAATutorial to be available
    if (typeof window.VPAATutorial === 'undefined') {
        console.warn('VPAATutorial core not loaded. Grades tutorial registration deferred.');
        return;
    }

    // Register the grades page tutorial
    window.VPAATutorial.registerTutorial('vpaa-grades', {
        title: 'Final Grades',
        description: 'Learn how to view student grades across departments and courses',
        steps: [
            {
                target: '.container h1, h1.text-2xl',
                title: 'Final Grades Overview',
                content: 'This page allows you to view final grades for students across all departments and courses. Use the filters to narrow down the results.',
                position: 'bottom'
            },
            {
                target: 'a.btn-outline-secondary[href*="departments"]',
                title: 'Back to Departments',
                content: 'Click this button to return to the departments page.',
                position: 'left',
                optional: true
            },
            {
                target: '.card.border-0.shadow-sm.mb-4 form',
                title: 'Filter Panel',
                content: 'Use these filters to select the department and course you want to view grades for.',
                position: 'bottom'
            },
            {
                target: 'select#department_id',
                title: 'Department Selection',
                content: 'First, select a department to see available courses.',
                position: 'bottom'
            },
            {
                target: 'select#course_id',
                title: 'Course Selection',
                content: 'After selecting a department, choose a specific course to view its student grades.',
                position: 'bottom'
            },
            {
                target: 'button[type="submit"]',
                title: 'Apply Filter',
                content: 'Click the Filter button to apply your selections and view the grades.',
                position: 'bottom'
            }
        ]
    });

    // Register the grades detail tutorial
    window.VPAATutorial.registerTutorial('vpaa-grades-detail', {
        title: 'Course Grades Detail',
        description: 'Learn how to read and understand the grades table',
        steps: [
            {
                target: '.container h1, h1.text-2xl',
                title: 'Course Grades',
                content: 'You\'re viewing grades for the selected course. The table shows all students and their grades across subjects.',
                position: 'bottom'
            },
            {
                target: '.card-header.bg-light, .card:has(.bi-people)',
                title: 'Instructors Panel',
                content: 'This section shows the instructors assigned to this course. Each badge represents one instructor.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-responsive table thead',
                title: 'Grades Table Headers',
                content: 'The table headers show the Student name column, followed by columns for each subject, and a final GPA column.',
                position: 'bottom'
            },
            {
                target: '.table-responsive table thead th[colspan]',
                title: 'Subject Columns',
                content: 'Each subject column shows the subject code and the instructor\'s name. Grades are displayed for each student-subject combination.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-responsive table tbody tr:first-child',
                title: 'Student Row',
                content: 'Each row shows a student\'s grades. The final column shows their computed GPA based on all subject grades.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table-responsive table tbody tr',
            entityName: 'grades',
            noAddButton: true
        }
    });
})();
