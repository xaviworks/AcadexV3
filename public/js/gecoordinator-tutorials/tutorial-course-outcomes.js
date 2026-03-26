/**
 * GE Coordinator Tutorial - Course Outcomes
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Course outcomes tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-course-outcomes', {
        title: 'Course Outcome Management',
        description: 'Select a subject to manage and review outcomes.',
        steps: [
            {
                target: 'h4.fw-bold, h1.text-2xl, h1',
                title: 'Course Outcome Management',
                content: 'Select a GE subject to open course outcome details.',
                position: 'bottom'
            },
            {
                target: '#yearTabs, .nav.nav-tabs',
                title: 'Year Tabs',
                content: 'Subjects are grouped by year level for easier navigation.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.subject-card:first-of-type, .row .subject-card',
                title: 'Subject Cards',
                content: 'Click a subject card to manage its course outcomes.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-course-outcomes-table', {
        title: 'Course Outcomes Table',
        description: 'Review and manage subject course outcomes.',
        steps: [
            {
                target: '.card .card-body h5.fw-bold, .container-fluid h4.fw-bold',
                title: 'Subject Outcome Details',
                content: 'You are viewing detailed outcomes for a selected subject.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Outcomes Table',
                content: 'The table lists CO code, identifier, description, and target percentage.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'button[data-bs-target="#addCourseOutcomeModal"], .btn.btn-success',
                title: 'Add Outcome',
                content: 'Use this action to add a new course outcome where applicable.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
