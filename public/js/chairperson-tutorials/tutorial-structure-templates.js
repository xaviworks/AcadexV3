/**
 * Chairperson Tutorial - Structure Templates
 * Tutorials for Structure Formula Request pages
 */

(function() {
    'use strict';

    // Wait for ChairpersonTutorial to be available
    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Structure templates tutorials registration deferred.');
        return;
    }

    // Register Structure Template Requests List tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-structure-templates', {
        title: 'Structure Formula Requests',
        description: 'Learn how to create and manage custom grading structure formulas',
        steps: [
            {
                target: 'h3.fw-bold, .d-flex h3',
                title: 'Structure Formula Requests',
                content: 'Welcome to the Structure Formula Requests page! Here you can create and manage custom grading structure formulas for your courses.',
                position: 'bottom'
            },
            {
                target: 'a[href*="structureTemplates"][href*="create"], .btn-success:has(.bi-plus-circle)',
                title: 'New Formula Request',
                content: 'Click this button to create a new grading structure formula request. You can define custom lecture, lab, or combined structures.',
                position: 'left'
            },
            {
                target: '.card-body .d-flex.gap-3',
                title: 'Request Statistics',
                content: 'This section shows a summary of your requests: Pending (awaiting approval), Approved (ready to use), and Rejected (needs revision).',
                position: 'bottom',
                optional: true
            },
            {
                target: '.row.g-4 .col-12:first-child .card, .request-card:first-child',
                title: 'Request Cards',
                content: 'Each card represents a formula request. Cards show the request name, structure type, status badge, and available actions.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-warning, .badge.bg-success, .badge.bg-danger',
                title: 'Status Badges',
                content: 'Yellow = Pending Review, Green = Approved, Red = Rejected. Only approved formulas can be used for grading.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register Create Structure Template tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-structure-templates-create', {
        title: 'Create Formula Request',
        description: 'Learn how to create a custom grading structure formula',
        steps: [
            {
                target: 'h3.fw-bold, .page-title h3',
                title: 'Create New Formula',
                content: 'Use this form to create a custom grading structure formula. Define how different assessment types contribute to the final grade.',
                position: 'bottom'
            },
            {
                target: 'input[name="name"], #name',
                title: 'Formula Name',
                content: 'Enter a descriptive name for your formula (e.g., "Standard Lecture Grading" or "Lab-Heavy Structure").',
                position: 'right',
                optional: true
            },
            {
                target: 'select[name="structure_type"], #structure_type',
                title: 'Structure Type',
                content: 'Choose the structure type: Lecture Only, Lecture + Lab, or Custom. This determines which grading components are available.',
                position: 'right',
                optional: true
            },
            {
                target: 'textarea[name="description"], #description',
                title: 'Description',
                content: 'Provide a brief description explaining when and how this formula should be used.',
                position: 'right',
                optional: true
            },
            {
                target: '.card:has(input[type="number"]), .grading-weights',
                title: 'Grading Weights',
                content: 'Define the percentage weight for each grading component (quizzes, exams, activities, etc.). Weights should total 100%.',
                position: 'top',
                optional: true
            },
            {
                target: 'button[type="submit"], .btn-success[type="submit"]',
                title: 'Submit Request',
                content: 'Click to submit your formula request for approval. The Dean or VPAA will review and approve or reject it.',
                position: 'top',
                optional: true
            }
        ]
    });

    // Register View Structure Template Detail tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-structure-templates-show', {
        title: 'Formula Request Details',
        description: 'View the details of a grading structure formula request',
        steps: [
            {
                target: 'h3.fw-bold, .card-header h5',
                title: 'Request Details',
                content: 'This page shows the complete details of your formula request including structure configuration and approval status.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-warning, .badge.bg-success, .badge.bg-danger',
                title: 'Approval Status',
                content: 'The status badge shows whether your request is pending, approved, or rejected.',
                position: 'left',
                optional: true
            },
            {
                target: '.card-body table, .structure-details',
                title: 'Structure Configuration',
                content: 'View the complete grading structure configuration including all component weights and term distributions.',
                position: 'top',
                optional: true
            },
            {
                target: 'a[href*="structureTemplates"]:not([href*="show"]), .btn-outline-secondary',
                title: 'Back to List',
                content: 'Click to return to the list of all your formula requests.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
