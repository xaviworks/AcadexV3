/**
 * Instructor Tutorial - Final Grades
 * Tutorial for the Instructor Final Grades page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Final Grades tutorial registration deferred.');
        return;
    }

    // Register the final grades tutorial
    window.InstructorTutorial.registerTutorial('instructor-scores', {
        title: 'Final Grades',
        description: 'Learn how to open a subject, generate final grades, and use printing tools',
        steps: [
            {
                target: '#subject-selection',
                title: 'Final Grades Subject Selection',
                content: 'Start here by selecting a subject to open its final grades workspace.',
                position: 'bottom'
            },
            {
                target: '#subject-selection',
                title: 'Pick a Subject',
                content: 'Start by selecting one of your subject cards to open its final grade records.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.subject-card',
                title: 'Subject Cards',
                content: 'Each card represents an assigned class. Click a card to load that class final grades view.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#print-area table thead, #print-area .table thead',
                title: 'Final Grades Table',
                content: 'The table shows each student with Prelim, Midterm, Prefinal, Final, and computed Final Average.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: 'form[action*="final-grades/generate"], button[type="submit"]:contains("Generate Final Grades")',
                title: 'Generate Final Grades',
                content: 'If grades are not generated yet, use this button to compute final averages and remarks for the selected subject.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#print-area th:contains("Final Average"), #print-area td.text-success',
                title: 'Final Average',
                content: 'This value is the computed final grade per student, rounded for display in the summary sheet.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: '#print-area .badge',
                title: 'Remarks Column',
                content: 'Remarks indicate each student status (Passed or Failed) based on your school passing-grade policy.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '#printOptionsButton',
                title: 'Print Options',
                content: 'Use Print Options to open the print menu and choose term sheets or the Final Summary report.',
                position: 'left',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '#print-area tbody tr, .table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', '.empty-state', 'td[colspan]'],
            entityName: 'grade records',
            addButtonSelector: 'form[action*="final-grades/generate"] button[type="submit"], #subject-selection .subject-card'
        }
    });
})();
