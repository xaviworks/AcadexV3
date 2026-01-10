/**
 * GE Coordinator Tutorial - View Grades
 * Tutorial for the View Grades wizard pages
 */

(function() {
    'use strict';

    // Wait for GECoordinatorTutorial to be available
    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Grades tutorial registration deferred.');
        return;
    }

    // Step 1: Select Course
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-grades', {
        title: 'View GE Grades - Select Course',
        description: 'Learn how to view student grades in GE courses',
        tableDataCheck: {
            selector: 'table tbody tr',
            emptySelectors: ['.alert-info', '.alert-warning'],
            entityName: 'courses',
            noAddButton: true
        },
        steps: [
            {
                target: '.page-title h1, h1.text-3xl, h1',
                title: 'View GE Grades Wizard',
                content: 'Welcome to the View Grades wizard. This multi-step process helps you navigate through courses, instructors, subjects, and finally to student grades.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb, .wizard-steps',
                title: 'Navigation Breadcrumb',
                content: 'Follow the breadcrumb to track your progress: Course → Instructor → Subject → Students. You can click previous steps to go back.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-responsive, table',
                title: 'Courses Table',
                content: 'Select a course/program to view GE grades for students in that program. Each row shows the course name and number of GE students enrolled.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'tbody tr:first-child a, tbody tr:first-child .btn',
                title: 'Select Course',
                content: 'Click on a course to proceed to the next step. You\'ll then select an instructor teaching GE subjects to that course.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ]
    });

    // Step 2: Select Instructor
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-grades-instructors', {
        title: 'View GE Grades - Select Instructor',
        description: 'Choose which instructor\'s grades to view',
        tableDataCheck: {
            selector: 'table tbody tr',
            emptySelectors: ['.alert-info'],
            entityName: 'instructors',
            noAddButton: true
        },
        steps: [
            {
                target: 'h1, .page-title h1',
                title: 'Select Instructor',
                content: 'You\'ve selected a course. Now choose which GE instructor\'s grades you want to review.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb, .wizard-steps',
                title: 'Progress Tracking',
                content: 'You are on step 2 of 4. After selecting an instructor, you\'ll choose which subject to review.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-responsive, table',
                title: 'Instructors Table',
                content: 'This table shows all GE instructors teaching subjects for the selected course. Select an instructor to view their subjects.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'tbody tr:first-child a, tbody tr:first-child .btn',
                title: 'Select Instructor',
                content: 'Click on an instructor to see the GE subjects they teach for this course.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ]
    });

    // Step 3: Select Subject
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-grades-subjects', {
        title: 'View GE Grades - Select Subject',
        description: 'Choose which subject\'s grades to view',
        tableDataCheck: {
            selector: 'table tbody tr',
            emptySelectors: ['.alert-info'],
            entityName: 'subjects',
            noAddButton: true
        },
        steps: [
            {
                target: 'h1, .page-title h1',
                title: 'Select Subject',
                content: 'Now choose which GE subject you want to review grades for.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb, .wizard-steps',
                title: 'Almost There',
                content: 'You are on step 3 of 4. After selecting a subject, you\'ll see the student grades.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-responsive, table',
                title: 'Subjects Table',
                content: 'This table lists all GE subjects taught by the selected instructor for the chosen course. Select a subject to view student grades.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'tbody tr:first-child',
                title: 'Subject Details',
                content: 'Each row shows the subject code, title, year level, semester, and number of enrolled students.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: 'tbody tr:first-child a, tbody tr:first-child .btn',
                title: 'View Grades',
                content: 'Click on a subject to view detailed grades for all students enrolled in that GE course.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ]
    });

    // Step 4: View Student Grades
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-grades-students', {
        title: 'View GE Grades - Student Grades',
        description: 'Review student performance in the selected GE subject',
        tableDataCheck: {
            selector: 'table tbody tr',
            emptySelectors: ['.alert-info'],
            entityName: 'student grades',
            noAddButton: true
        },
        steps: [
            {
                target: 'h1, .page-title h1',
                title: 'Student Grades',
                content: 'You\'ve reached the final step. Here you can review all student grades for the selected GE subject.',
                position: 'bottom'
            },
            {
                target: '.card-header, .subject-info',
                title: 'Subject Information',
                content: 'The header shows the subject code, title, instructor name, and academic period. Verify you\'re viewing the correct subject.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-responsive, table',
                title: 'Grades Table',
                content: 'This table displays all students enrolled in the subject with their grades, term grades, final grades, and overall status.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'table thead',
                title: 'Grade Columns',
                content: 'Columns typically include: Student ID, Student Name, Prelim Grade, Midterm Grade, Finals Grade, Term Average, and Remarks (Passed/Failed).',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'tbody tr:first-child',
                title: 'Student Grade Entry',
                content: 'Each row shows a student\'s complete grade record for this subject. Review grades to identify struggling students who may need support.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: '.badge:contains("Passed"), .badge:contains("Failed")',
                title: 'Grade Status Badges',
                content: 'Status badges show at a glance whether students passed (green) or failed (red) the subject. Failed grades require attention and possible intervention.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn:contains("Export"), .btn:contains("Print")',
                title: 'Export Options',
                content: 'Use these buttons to export or print the grades report for record-keeping or sharing with administrators.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
