/**
 * GE Coordinator Tutorial - Assign Subjects
 * Tutorial for the Assign Subjects page
 */

(function() {
    'use strict';

    // Wait for GECoordinatorTutorial to be available
    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Assign Subjects tutorial registration deferred.');
        return;
    }

    // Register the assign subjects tutorial
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-assign-subjects', {
        title: 'Assign GE Subjects to Instructors',
        description: 'Learn how to manage subject assignments for GE faculty members',
        tableDataCheck: {
            selector: 'table tbody tr',
            emptySelectors: ['.alert-info'],
            entityName: 'subjects',
            noAddButton: true
        },
        steps: [
            {
                target: '.page-title h1, h1.text-3xl, h1',
                title: 'Subject Assignment Page',
                content: 'Welcome to the Subject Assignment page. Here you assign General Education subjects to faculty members for the active academic period.',
                position: 'bottom'
            },
            {
                target: '.table-responsive, table',
                title: 'GE Subjects Table',
                content: 'This table lists all General Education subjects available for assignment. Each row shows the subject details and assigned instructors.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'table thead',
                title: 'Table Columns',
                content: 'The columns show: Subject Code, Subject Title, Year Level, Semester, and Assigned Instructors with management actions.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'tbody tr:first-child td:nth-child(1)',
                title: 'Subject Code',
                content: 'The unique identifier for each General Education subject (e.g., GE101, MATH001). Use this for quick reference.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: 'tbody tr:first-child td:nth-child(2)',
                title: 'Subject Title',
                content: 'The full name of the General Education course. Review this to ensure correct subject-instructor matching based on expertise.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: 'tbody tr:first-child .badge',
                title: 'Assigned Instructors',
                content: 'Shows all instructors currently assigned to teach this subject. Multiple instructors can be assigned to handle different sections.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-primary:contains("Assign"), .btn-primary:contains("Manage")',
                title: 'Assign/Manage Button',
                content: 'Click this button to assign instructors to the subject or manage existing assignments. You can add or remove instructors from the assignment list.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-primary',
                title: 'Making Assignments',
                content: 'When you click Assign/Manage, a modal will open showing available GE instructors. Select instructors based on their expertise and current workload to ensure balanced course distribution.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ]
    });
})();
