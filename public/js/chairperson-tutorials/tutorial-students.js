/**
 * Chairperson Tutorial - Students
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Students tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-students', {
        title: 'Students List',
        description: 'Learn how to view and filter students by year level.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Students List Page',
                content: 'This page shows all students under your department and allows filtering by year level.',
                position: 'bottom'
            },
            {
                target: '#yearTabs',
                title: 'Year Level Tabs',
                content: 'Use these tabs to switch between All Years and specific year-level student lists.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#yearTabsContent .table thead',
                title: 'Student Table',
                content: 'The table displays student details for the currently selected year tab.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
