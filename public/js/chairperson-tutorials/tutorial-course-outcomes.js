/**
 * Chairperson Tutorial - Course Outcomes
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Course outcomes tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-course-outcomes', {
        title: 'Course Outcome Management',
        description: 'Select a subject to manage its course outcomes.',
        steps: [
            {
                target: 'h4.fw-bold, h1.text-2xl, h1',
                title: 'Course Outcome Management',
                content: 'This page is where you start managing course outcomes by selecting a subject.',
                position: 'bottom'
            },
            {
                target: '#yearTabs, .nav.nav-tabs',
                title: 'Year Level Tabs',
                content: 'Subjects are grouped by year level for easier navigation.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.subject-card:first-of-type, .row .subject-card',
                title: 'Subject Cards',
                content: 'Click a subject card to open and manage its detailed course outcomes.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-course-outcomes-table', {
        title: 'Course Outcomes Table',
        description: 'Manage and review outcomes for the selected subject.',
        steps: [
            {
                target: '.card .card-body h5.fw-bold, .container-fluid h4.fw-bold',
                title: 'Subject Outcome View',
                content: 'You are now viewing course outcomes for a specific subject.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Outcomes Table',
                content: 'The table lists CO code, identifier, description, academic period, and target percentage.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'button[data-bs-target="#addCourseOutcomeModal"], .btn.btn-success',
                title: 'Add Course Outcome',
                content: 'Use this action to add a new course outcome for the selected subject.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
