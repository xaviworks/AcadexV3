/**
 * Instructor Tutorial - Manage Grades
 * Tutorial for the Manage Grades page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Manage Grades tutorial registration deferred.');
        return;
    }

    // Register the manage grades tutorial
    window.InstructorTutorial.registerTutorial('instructor-manage-grades', {
        title: 'Manage Grades',
        description: 'Learn how to select a subject, manage term grades, and save updates',
        steps: [
            {
                target: '#instructor-subject-selection, .subject-card, .stepper',
                title: 'Start on Manage Grades',
                content: 'This page lets you manage student grades by subject and term. If subject cards are shown, choose one to open the grading workspace.',
                position: 'bottom'
            },
            {
                target: '.subject-card, .term-step[data-term]',
                title: 'Choose Subject or Term',
                content: 'From the subject cards, open the course you want. Inside the workspace, use the term stepper to switch grading periods.',
                position: 'bottom'
            },
            {
                target: '#componentUsageSummary, .term-step[data-term]',
                title: 'Review Activity Slot Status',
                content: 'Check the activity slot summary for the selected term so you can see component limits, required items, and formula scope at a glance.',
                position: 'bottom'
            },
            {
                target: 'button[data-bs-target="#addActivityModal"], .btn-success:contains("Add Activity")',
                title: 'Add Activity (If Needed)',
                content: 'Use Add Activity to create a missing assessment component before entering grades.',
                position: 'left'
            },
            {
                target: '#gradeForm table thead, .table thead',
                title: 'Grades Table',
                content: 'The table shows students as rows and activities as columns. You can update item counts, outcome links, and student scores here.',
                position: 'bottom'
            },
            {
                target: '#gradeForm .grade-input, input.grade-input',
                title: 'Enter Student Scores',
                content: 'Type each score directly in the input cells. The system recalculates term grades as scores are updated.',
                position: 'top'
            },
            {
                target: '#saveGradesBtn, button[type="submit"]:contains("Save Grades")',
                title: 'Save Grades',
                content: 'Click Save Grades to persist your updates for the selected subject and term.',
                position: 'left'
            }
        ],
        tableDataCheck: {
            selector: '#gradeForm table tbody tr, table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', 'td[colspan]', '.empty-state'],
            entityName: 'students',
            addButtonSelector: 'button[data-bs-target="#addActivityModal"], .btn-success:contains("Add Activity")'
        }
    });
})();
