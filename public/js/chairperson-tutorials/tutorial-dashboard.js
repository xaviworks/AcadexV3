/**
 * Chairperson Tutorial - Dashboard
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Dashboard tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-dashboard', {
        title: 'Chairperson Dashboard',
        description: 'Get familiar with your department overview and quick actions.',
        steps: [
            {
                target: 'h2.fw-bold',
                title: 'Welcome Overview',
                content: 'This is your Chairperson dashboard where you can monitor instructors, students, and active courses.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-4:first-child .card',
                title: 'Summary Cards',
                content: 'These cards provide quick counts for instructors, students, and active courses in your department.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-lg-8 .card',
                title: 'Faculty Status Overview',
                content: 'Use this panel to track active, inactive, and pending faculty accounts and their distribution.',
                position: 'top',
                optional: true
            },
            {
                target: '.col-lg-4 .card',
                title: 'Quick Actions',
                content: 'Quickly open key pages like Assign Courses, Student List, and View Grades from this panel.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
