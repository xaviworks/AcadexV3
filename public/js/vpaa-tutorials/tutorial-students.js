/**
 * VPAA Tutorial - Students
 * Tutorial for the VPAA Students pages
 */

(function() {
    'use strict';

    // Wait for VPAATutorial to be available
    if (typeof window.VPAATutorial === 'undefined') {
        console.warn('VPAATutorial core not loaded. Students tutorial registration deferred.');
        return;
    }

    // Register the students department selection tutorial
    window.VPAATutorial.registerTutorial('vpaa-students', {
        title: 'Students - Department Selection',
        description: 'Learn how to browse students by department',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Students by Department',
                content: 'This page is the department-selection view for student records. Start by reviewing the page title and instructions.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"]',
                title: 'Selection Instructions',
                content: 'Use these instructions and navigation cues to confirm you are selecting a department before viewing student records.',
                position: 'bottom'
            },
            {
                target: '#department-selection, .row.g-4',
                title: 'Department Cards',
                content: 'Each card represents a department. Click on a department to view its enrolled students.',
                position: 'bottom'
            },
            {
                target: '.subject-card, .col-md-4:first-child .card',
                title: 'Department Card',
                content: 'Click on any department card or the "View Students" button to see students enrolled in that department\'s programs.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register the students list tutorial
    window.VPAATutorial.registerTutorial('vpaa-students-list', {
        title: 'Students List',
        description: 'Learn how to browse and filter student records',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Students Overview',
                content: 'This page displays students from the selected department. You can further filter by course.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"]',
                title: 'Page Context',
                content: 'This section confirms your current view and helps you orient while reviewing filtered student data.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.card.border-0.shadow-sm.mb-4 form',
                title: 'Filter Options',
                content: 'Use these dropdowns to filter students by department and course. The filters update automatically when you make a selection.',
                position: 'bottom'
            },
            {
                target: 'select#department_id',
                title: 'Department Filter',
                content: 'Select a department to show only students from that department, or choose "All Departments" to see all students.',
                position: 'bottom'
            },
            {
                target: 'select#course_id',
                title: 'Course Filter',
                content: 'After selecting a department, you can further filter by specific course/program.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Students Table',
                content: 'The table shows student information: Name, Course, Department, and Year Level. Each row represents one student.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-primary-subtle',
                title: 'Course Badge',
                content: 'The course badge shows which program the student is enrolled in.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success-subtle',
                title: 'Year Level',
                content: 'Shows the student\'s current year level in their program.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'students',
            noAddButton: true
        }
    });
})();
