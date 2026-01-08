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
                target: '.breadcrumb',
                title: 'Navigation',
                content: 'You\'re in the Students section. Use the breadcrumb to navigate back to the dashboard.',
                position: 'bottom'
            },
            {
                target: '.card.border-0.shadow-sm.rounded-4.mb-3',
                title: 'Selection Instructions',
                content: 'To view student records, first select a department from the cards below.',
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
                target: '.container h1, h1.text-2xl',
                title: 'Students Overview',
                content: 'This page displays students from the selected department. You can further filter by course.',
                position: 'bottom'
            },
            {
                target: 'a.btn-outline-secondary',
                title: 'Back to Overview',
                content: 'Click this button to return to the department selection page.',
                position: 'left',
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
