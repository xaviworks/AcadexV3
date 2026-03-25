/**
 * GE Coordinator Tutorial - Reports
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Reports tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-overview', {
        title: 'GE Reports Overview',
        description: 'Understand high-level GE reporting metrics.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'GE Coordinator Reports',
                content: 'This page summarizes GE subject, assignment, instructor, and enrollment metrics.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 .card:first-child',
                title: 'Statistics Cards',
                content: 'Use these cards to quickly monitor assignment rates and current GE capacity.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'a[href*="/assign-subjects"], a[href*="/manage-schedule"]',
                title: 'Action Buttons',
                content: 'Jump directly to assignment management or schedule review from these actions.',
                position: 'top',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-program', {
        title: 'Outcomes Summary by Program',
        description: 'Review CO attainment across GE programs.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Program Outcomes Summary',
                content: 'This report summarizes course outcome attainment across programs.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Program CO Table',
                content: 'Compare CO1 to CO6 percentages by program with target indicators.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-course', {
        title: 'Outcomes Summary by Course - Select Program',
        description: 'Select a course/program to drill into subject outcomes.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Course Outcomes Summary',
                content: 'Choose a course card to open detailed GE subject outcome performance.',
                position: 'bottom'
            },
            {
                target: '.course-card:first-of-type, .row .course-card',
                title: 'Course Cards',
                content: 'Each card navigates to detailed subject-level CO summaries.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-course-detail', {
        title: 'Outcomes Summary by Course - Details',
        description: 'Inspect subject-level CO attainment in a selected course.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Course Detail Report',
                content: 'This report shows subject-level CO attainment for the selected course.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Subject CO Matrix',
                content: 'Review each subject\'s CO columns and identify below-target areas.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-student', {
        title: 'Outcomes Summary by Student - Selection',
        description: 'Find a student and choose one enrolled GE subject.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Student Outcomes Summary',
                content: 'Search for a student first, then select one enrolled GE subject to view details.',
                position: 'bottom'
            },
            {
                target: '#student_query',
                title: 'Student Search',
                content: 'Type student names and select from suggestions or results.',
                position: 'bottom'
            },
            {
                target: '.enrolled-course-card, .enrolled-courses-list .col-12:first-child a',
                title: 'Enrolled GE Courses',
                content: 'Choose one enrolled GE subject to generate the outcome summary.',
                position: 'top',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-student-detail', {
        title: 'Outcomes Summary by Student - Details',
        description: 'Inspect term and overall CO attainment for a student.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Student Detail Report',
                content: 'This report shows term-level and overall CO percentages for the selected student.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'CO Score Table',
                content: 'Review term and overall rows to check attainment against targets.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
