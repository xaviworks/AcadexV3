/**
 * GE Coordinator Tutorial - Dashboard
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Dashboard tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-dashboard', {
        title: 'GE Coordinator Dashboard',
        description: 'Get familiar with GE metrics and quick actions.',
        steps: [
            {
                target: 'h2.fw-bold',
                title: 'Dashboard Overview',
                content: 'This dashboard summarizes GE instructors, enrolled students, and active GE courses.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-4:first-child .card',
                title: 'Summary Cards',
                content: 'Use these cards to quickly monitor key General Education metrics.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-lg-8 .card',
                title: 'Faculty Status Overview',
                content: 'Track active, inactive, and pending faculty status from this section.',
                position: 'top',
                optional: true
            },
            {
                target: '.col-lg-4 .card',
                title: 'Quick Actions',
                content: 'Jump quickly to Manage Courses, Students, or Grades from here.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
