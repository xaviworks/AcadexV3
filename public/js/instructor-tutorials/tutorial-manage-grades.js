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
        description: 'Learn how to enter and manage student grades for each term',
        steps: [
            {
                target: 'select[name="subject_id"], .subject-selector',
                title: 'Select Your Subject',
                content: 'Start by selecting the subject you want to enter grades for. The page will load the grading interface for that subject.',
                position: 'bottom'
            },
            {
                target: '.term-stepper, .btn-group:has([data-term]), .nav-tabs',
                title: 'Select Term',
                content: 'Choose which term you want to enter grades for: Prelim, Midterm, Pre-Final, or Finals. Each term has its own set of grades.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.grading-formula, .formula-display, .card:contains("Formula")',
                title: 'Grading Formula',
                content: 'This shows the grading formula configured for this subject (e.g., Written Work 30%, Performance Task 40%, Exam 30%). All grades must follow this formula.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'table thead, .grades-table thead',
                title: 'Grades Table',
                content: 'The table displays all students with columns for each grade component. You can enter scores directly in the table cells.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'input[type="number"], .grade-input, td input',
                title: 'Enter Grades',
                content: 'Click on any cell to enter a grade. The system auto-saves your entries and automatically calculates the final grade based on the formula.',
                position: 'top',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-primary:contains("Save"), button:contains("Save Grades")',
                title: 'Save Grades',
                content: 'Although grades auto-save, you can manually save using this button. The system will validate all entries before saving.',
                position: 'left',
                optional: true
            },
            {
                target: '.total-column, td:last-child, .final-grade',
                title: 'Calculated Total',
                content: 'This column shows the automatically calculated final grade based on the grading formula. It updates in real-time as you enter component scores.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-info:contains("Import"), button:contains("Excel")',
                title: 'Import from Excel',
                content: 'For faster entry, you can import grades from an Excel file. Download the template first, fill it out, then upload it here.',
                position: 'left',
                optional: true
            },
            {
                target: '.validation-error, .text-danger, .alert',
                title: 'Validation Messages',
                content: 'The system will show validation messages if you enter invalid grades (e.g., negative numbers, grades exceeding maximum). Fix these before proceeding.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: 'table tbody tr, .grades-table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', 'td[colspan]'],
            entityName: 'students',
            addButtonSelector: null
        }
    });
})();
