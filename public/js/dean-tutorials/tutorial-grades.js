/**
 * Dean Tutorial - Grades
 * Tutorial for the Dean Grades pages (multi-step wizard)
 */

(function() {
    'use strict';

    // Wait for DeanTutorial to be available
    if (typeof window.DeanTutorial === 'undefined') {
        console.warn('DeanTutorial core not loaded. Grades tutorial registration deferred.');
        return;
    }

    // Step 1: Course Selection
    window.DeanTutorial.registerTutorial('dean-grades', {
        title: 'View Grades - Course Selection',
        description: 'Learn how to navigate to view student grades',
        steps: [
            {
                target: 'h1.text-2xl',
                title: 'View Grades',
                content: 'This page allows you to view student grades. The process follows a step-by-step wizard: first select a Course, then an Instructor, then a Subject to see grades.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb',
                title: 'Navigation Breadcrumb',
                content: 'The breadcrumb shows your current step in the process. You can click on previous steps to go back.',
                position: 'bottom'
            },
            {
                target: '.row.g-4',
                title: 'Course Cards',
                content: 'Select a course to proceed. Each card represents a course/program in your department.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, .col-md-4:first-child .card',
                title: 'Course Card',
                content: 'Click on a course card to see the instructors teaching in that program. The card shows the course code and description.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Step 2: Instructor Selection
    window.DeanTutorial.registerTutorial('dean-grades-instructors', {
        title: 'View Grades - Instructor Selection',
        description: 'Select an instructor to view their subjects',
        steps: [
            {
                target: 'h1.text-2xl',
                title: 'Select Instructor',
                content: 'Now select an instructor to view the subjects they teach and their students\' grades.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb',
                title: 'Navigation',
                content: 'You\'re now at the "Select Instructor" step. Click "Select Course" in the breadcrumb to go back.',
                position: 'bottom'
            },
            {
                target: '.row.g-4',
                title: 'Instructor Cards',
                content: 'Each card represents an instructor. Click on one to see the subjects they handle.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, .col-md-4:first-child .card',
                title: 'Instructor Card',
                content: 'Click on an instructor\'s card to proceed to subject selection.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Step 3: Subject Selection
    window.DeanTutorial.registerTutorial('dean-grades-subjects', {
        title: 'View Grades - Subject Selection',
        description: 'Select a subject to view student grades',
        steps: [
            {
                target: 'h1.text-2xl',
                title: 'Select Subject',
                content: 'Select a subject to view the students\' final grades for that subject.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb',
                title: 'Navigation',
                content: 'You\'re at the "Select Subject" step. Use the breadcrumb to navigate back to previous steps.',
                position: 'bottom'
            },
            {
                target: '.row.g-4',
                title: 'Subject Cards',
                content: 'Each card shows a subject taught by the selected instructor. Click to view grades.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, .col-md-4:first-child .card',
                title: 'Subject Card',
                content: 'Click on a subject to view the students\' final grades for that subject.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Step 4: Students' Final Grades
    window.DeanTutorial.registerTutorial('dean-grades-students', {
        title: 'Students\' Final Grades',
        description: 'View and understand student grades',
        steps: [
            {
                target: 'h1.text-2xl',
                title: 'Students\' Final Grades',
                content: 'This page displays the final grades for all students in the selected subject.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb',
                title: 'Navigation',
                content: 'You\'re viewing the final grades. Use the breadcrumb to navigate back to select a different course, instructor, or subject.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Grades Table',
                content: 'The table shows student names, their final grades, and status for the selected subject.',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child',
                title: 'Student Grade Row',
                content: 'Each row displays a student\'s final grade for this subject.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'grades',
            noAddButton: true
        }
    });
})();
