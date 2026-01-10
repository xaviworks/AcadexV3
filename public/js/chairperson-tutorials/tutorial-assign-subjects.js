/**
 * Chairperson Tutorial - Assign Subjects
 * Tutorial for the Assign Courses to Instructors page
 */

(function() {
    'use strict';

    // Wait for ChairpersonTutorial to be available
    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Assign subjects tutorial registration deferred.');
        return;
    }

    // Register the assign subjects tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-assign-subjects', {
        title: 'Assign Courses to Instructors',
        description: 'Learn how to assign and manage course loads for faculty members',
        steps: [
            {
                target: '.page-title h1, h1:has(.bi-person-badge)',
                title: 'Course Assignment Page',
                content: 'Welcome to the Course Assignment page! Here you can assign subjects to instructors, manage teaching loads, and organize course offerings by year level.',
                position: 'bottom'
            },
            {
                target: '#viewMode, .form-select[onchange="toggleViewMode()"]',
                title: 'View Mode Switcher',
                content: 'Toggle between "Year View" (courses organized by year level tabs) and "Full View" (all courses displayed at once) to suit your preference.',
                position: 'left'
            },
            {
                target: '#yearTabs, .nav-tabs',
                title: 'Year Level Tabs',
                content: 'Click these tabs to navigate between different year levels. Each tab shows courses specific to that year in the curriculum.',
                position: 'bottom'
            },
            {
                target: '#year-level-1, .nav-tabs .nav-link:first-child',
                title: '1st Year Courses',
                content: 'This tab displays all 1st year courses. You can quickly see which subjects need instructor assignments.',
                position: 'bottom'
            },
            {
                target: '#yearView table, .table',
                title: 'Course Assignment Table',
                content: 'This table lists all courses with their codes, descriptions, assigned instructors, and action buttons. Unassigned courses show a dash (—) in the instructor column.',
                position: 'top'
            },
            {
                target: '#yearView table thead th:nth-child(1)',
                title: 'Course Code Column',
                content: 'The unique course code identifier (e.g., CS101, MATH201) is displayed here for quick reference.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#yearView table thead th:nth-child(3)',
                title: 'Assigned Instructor Column',
                content: 'Shows the instructor currently assigned to teach this course. A dash (—) indicates no instructor has been assigned yet.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.btn-success:has(.bi-person-plus), button[onclick*="openConfirmAssignModal"]',
                title: 'Assign Instructor Button',
                content: 'Click the "Assign" button to assign an instructor to an unassigned course. A modal will appear letting you select from available faculty members.',
                position: 'left',
                optional: true
            },
            {
                target: '.btn-danger:has(.bi-x-circle), button[onclick*="openConfirmUnassignModal"]',
                title: 'Unassign Instructor Button',
                content: 'Click "Unassign" to remove the current instructor from a course. This makes the course available for reassignment to another faculty member.',
                position: 'left',
                optional: true
            },
            {
                target: '#fullView, #yearView',
                title: 'Course Overview',
                content: 'You can switch between Year View and Full View anytime. Full View shows all courses across all year levels in expandable sections.',
                position: 'top'
            }
        ]
    });
})();
