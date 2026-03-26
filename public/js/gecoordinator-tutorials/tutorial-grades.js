/**
 * GE Coordinator Tutorial - View Grades
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Grades tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-grades', {
        title: 'View Grades - Select Instructor',
        description: 'Choose an instructor to start grade review.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Students\' Final Grades',
                content: 'This flow starts by selecting an instructor, then subject, then final grades table.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, .row .subject-card',
                title: 'Instructor Cards',
                content: 'Pick an instructor card to continue to subject selection.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-grades-subjects', {
        title: 'View Grades - Select Subject',
        description: 'Choose a subject taught by the selected instructor.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Select Subject',
                content: 'Now select a subject to view enrolled students\' final grades.',
                position: 'bottom'
            },
            {
                target: '#subject-selection .subject-card, .subject-card:first-of-type',
                title: 'Subject Cards',
                content: 'Each subject card opens the final grades list for that class.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-grades-students', {
        title: 'View Grades - Final Grades Table',
        description: 'Inspect student term grades and final averages.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Final Grades Table',
                content: 'This page displays final grade components, average, and remarks for students.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Grades Columns',
                content: 'Review term columns, computed final average, and pass/fail status.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table tbody tr:first-child',
                title: 'Student Record Row',
                content: 'Each row represents one student and grade breakdown for the selected subject.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
