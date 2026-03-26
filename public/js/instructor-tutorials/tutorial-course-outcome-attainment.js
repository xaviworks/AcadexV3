/**
 * Instructor Tutorial - Course Outcome Attainment
 * Tutorial for the Course Outcome Attainment Report page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Course Outcome Attainment tutorial registration deferred.');
        return;
    }

    // Register the course outcome attainment tutorial
    window.InstructorTutorial.registerTutorial('instructor-course-outcome-attainment', {
        title: 'Course Outcome Attainment Report',
        description: 'Learn how to view and analyze student achievement of course learning outcomes',
        steps: [
            {
                target: 'h1:contains("Course Outcome"), .page-title',
                title: 'Course Outcome Attainment',
                content: 'This report shows how well students are achieving the course learning outcomes based on their performance in linked activities.',
                position: 'bottom'
            },
            {
                target: '.subject-card, #subject-selection',
                title: 'Select Subject',
                content: 'Click on a subject card to view the course outcome attainment report for that subject. Each subject has its own set of learning outcomes.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.compact-stepper, .term-stepper, .nav-tabs',
                title: 'Filter by Term',
                content: 'Switch between terms (Prelim, Midterm, Pre-Final, Finals, or All Terms) to see outcome achievement for specific periods of the semester.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'table thead .table-success, table thead',
                title: 'Course Outcome Columns',
                content: 'Each column represents a course outcome (CO1, CO2, CO3, etc.). These show student performance on activities linked to each outcome.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'tbody tr:first-child',
                title: 'Student Performance',
                content: 'Each row shows a student\'s achievement across all course outcomes. Scores are displayed as percentages based on their performance in related activities.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: '.score-value, td[data-percentage]',
                title: 'Achievement Scores',
                content: 'The percentage shows how well each student achieved each outcome. Green highlighting indicates passing scores (typically 75% or higher).',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: 'tfoot, .table-footer',
                title: 'Overall Achievement Rate',
                content: 'The footer shows the overall class achievement rate for each outcome. This helps identify which outcomes students are struggling with.',
                position: 'top',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-primary:contains("Print"), .btn-success:contains("Export")',
                title: 'Export Report',
                content: 'Export or print the attainment report for documentation, accreditation, or department review purposes.',
                position: 'left',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: 'tbody tr, .co-table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', 'tbody:empty'],
            entityName: 'student records',
            addButtonSelector: null
        }
    });
})();
