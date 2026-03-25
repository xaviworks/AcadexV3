/**
 * Instructor Tutorial - Scores & Final Grades
 * Tutorial for the Scores and Final Grades page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Scores tutorial registration deferred.');
        return;
    }

    // Register the scores tutorial
    window.InstructorTutorial.registerTutorial('instructor-scores', {
        title: 'Scores & Final Grades',
        description: 'Learn how to view comprehensive student scores and final grades',
        steps: [
            {
                target: 'select[name="subject_id"], .subject-selector',
                title: 'Select Your Subject',
                content: 'Choose the subject to view complete grade breakdowns and final grades for all students.',
                position: 'bottom'
            },
            {
                target: '.term-tabs, .nav-tabs, .btn-group:has([data-term])',
                title: 'View by Term',
                content: 'Switch between terms to see grades for Prelim, Midterm, Pre-Final, Finals, or view the complete Semester Final grade.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#scoresTable thead, table thead',
                title: 'Comprehensive Scores Table',
                content: 'This table shows all students with detailed breakdowns: Written Work scores, Performance Task scores, Exam scores, and calculated Final Grade.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'th:contains("WW"), th:contains("Written")',
                title: 'Written Work Column',
                content: 'Shows the average or total score from all Written Work activities (quizzes, exercises, etc.) for this term.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: 'th:contains("PT"), th:contains("Performance")',
                title: 'Performance Task Column',
                content: 'Displays the average or total from Performance Task activities (projects, laboratory work, demonstrations).',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: 'th:contains("Exam"), th:contains("QA")',
                title: 'Exam Column',
                content: 'Shows exam scores for this term. Exams typically have higher weight in the final grade calculation.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: 'th:contains("Final"), td.final-grade, .font-weight-bold',
                title: 'Final Grade',
                content: 'The calculated final grade based on the grading formula. This applies the configured percentage weights to each component.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.grade-remark, .badge:contains("Passed"), .badge:contains("Failed")',
                title: 'Grade Remarks',
                content: 'Indicates whether the student has passed or failed based on the passing grade threshold (usually 75 or 3.0).',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-info:contains("Details"), .btn-primary:contains("View")',
                title: 'View Student Details',
                content: 'Click to see a complete breakdown of individual activity scores that contributed to the final grade.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-success:contains("Export"), .btn-secondary:contains("Print")',
                title: 'Export Grades',
                content: 'Export the grade sheet to Excel or PDF for record-keeping, printing, or submission to the registrar.',
                position: 'left',
                optional: true
            },
            {
                target: '.statistics-card, .summary-panel',
                title: 'Class Statistics',
                content: 'View class performance statistics: average grade, passing rate, highest/lowest scores, and grade distribution.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.formula-display, .card:contains("Formula")',
                title: 'Applied Formula',
                content: 'Reference the grading formula used to calculate final grades. This shows the weight distribution (e.g., WW 30%, PT 40%, Exam 30%).',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '#scoresTable tbody tr, .table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', 'td[colspan]'],
            entityName: 'grade records',
            addButtonSelector: null
        }
    });
})();
