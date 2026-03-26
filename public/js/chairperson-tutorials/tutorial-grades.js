/**
 * Chairperson Tutorial - View Grades
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Grades tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-grades', {
        title: 'View Grades - Select Instructor',
        description: 'Choose an instructor to begin viewing grades.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Students\' Final Grades',
                content: 'This page follows a step-by-step flow: Select Instructor, Select Subject, then view student grades.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb',
                title: 'Progress Breadcrumb',
                content: 'The breadcrumb indicates your current step in the grade viewing process.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.subject-card:first-of-type, .row .subject-card',
                title: 'Instructor Cards',
                content: 'Choose an instructor card to continue to subject selection.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-grades-subjects', {
        title: 'View Grades - Select Subject',
        description: 'Choose a subject taught by the selected instructor.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Select Subject',
                content: 'Now choose the subject to view the class final grades.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, #subject-selection .subject-card',
                title: 'Subject Cards',
                content: 'Each subject card opens the final grade table for enrolled students.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-grades-students', {
        title: 'View Grades - Final Grades Table',
        description: 'Review student final grades and remarks.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Final Grades Overview',
                content: 'You are now viewing the students\' final grades for the selected subject.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Grades Table',
                content: 'The table shows term grades, computed final average, and pass/fail remarks.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table tbody tr:first-child',
                title: 'Student Grade Row',
                content: 'Each row represents one student and their grade breakdown.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
