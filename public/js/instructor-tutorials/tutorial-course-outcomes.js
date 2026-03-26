/**
 * Instructor Tutorial - Course Outcomes
 * Tutorial for the Course Outcomes Management page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Course Outcomes tutorial registration deferred.');
        return;
    }

    // Register the course outcomes tutorial
    window.InstructorTutorial.registerTutorial('instructor-course-outcomes', {
        title: 'Manage Course Outcomes',
        description: 'Learn how to define and track student achievement of course learning outcomes',
        steps: [
            {
                target: 'h1:contains("Course Outcomes"), h1 .bi-bullseye',
                title: 'Course Outcomes Overview',
                content: 'Course outcomes are the learning objectives that define what students should know or be able to do after completing your subject.',
                position: 'bottom'
            },
            {
                target: '#subject-selection, .subject-card',
                title: 'Select Your Subject',
                content: 'Click on a subject card to view and manage its course outcomes. Each subject has specific learning objectives that students should achieve.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.btn-success:contains("Add Course Outcome"), button[data-bs-target="#addCourseOutcomeModal"]',
                title: 'Add Course Outcome',
                content: 'Click to define a new course outcome/learning objective for this subject. These represent what students should know or be able to do.',
                position: 'left',
                optional: true
            },
            {
                target: 'table thead, .table-responsive thead',
                title: 'Course Outcomes Table',
                content: 'This table lists all course outcomes with their Code (CO1, CO2, etc.), Identifier, Description, Academic Period, and Percentage.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'td.fw-semibold, tbody td:first-child',
                title: 'Outcome Code',
                content: 'Each outcome has a unique code (e.g., CO1, CO2). Use these codes when linking activities to outcomes.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: 'tbody td:nth-child(3)',
                title: 'Outcome Description',
                content: 'The learning objective statement describing what students should achieve (e.g., "Apply scientific method to analyze biological phenomena").',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.text-success:contains("%")',
                title: 'Passing Percentage',
                content: 'Shows the required percentage for students to be considered as having achieved this outcome. Typically set at 75%.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-success:contains("Edit"), a:contains("Edit")',
                title: 'Edit Outcome',
                content: 'Modify the outcome description, identifier, or passing threshold. Changes apply to ongoing assessments.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ],
        tableDataCheck: {
            selector: 'table tbody tr, .table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', 'tbody:empty'],
            entityName: 'course outcomes',
            addButtonSelector: '.btn-success:contains("Add Course Outcome")'
        }
    });
})();
