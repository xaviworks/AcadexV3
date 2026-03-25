/**
 * GE Coordinator Tutorial - Students
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Students tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-students', {
        title: 'View Students - Select Course',
        description: 'Select a GE subject to view enrolled students.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'View Students',
                content: 'Start by selecting a GE subject card to open its student list.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, .row .subject-card',
                title: 'GE Subject Cards',
                content: 'Each card represents a GE subject with assigned instructors.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-students-list', {
        title: 'View Students - List',
        description: 'Filter and inspect enrolled GE students.',
        steps: [
            {
                target: '#yearFilter',
                title: 'Year Level Filter',
                content: 'Filter students by year level to narrow the list quickly.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#studentsTable thead, .table thead',
                title: 'Students Table',
                content: 'Review students, GE subjects, and assigned instructors here.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
