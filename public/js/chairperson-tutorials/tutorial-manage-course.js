/**
 * Chairperson Tutorial - Manage Course / Import Course
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Manage course tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-manage-course', {
        title: 'Manage Course Assignments',
        description: 'Learn how to assign subjects to instructors by year level.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Assign Courses to Instructors',
                content: 'This page lets you assign and unassign course subjects to instructors.',
                position: 'bottom'
            },
            {
                target: '#yearTabs',
                title: 'Year Tabs',
                content: 'Switch between year levels to manage subject assignments per year.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#yearTabsContent .table thead, .table thead',
                title: 'Assignment Table',
                content: 'Use the table actions to assign or unassign instructors for each subject.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-import-course', {
        title: 'Import Courses',
        description: 'Learn how to import curriculum subjects into the system.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Import Courses Page',
                content: 'This page imports curriculum subjects into the current academic setup.',
                position: 'bottom'
            },
            {
                target: '#curriculumSelect',
                title: 'Select Curriculum',
                content: 'Choose a curriculum first to load available subjects for import.',
                position: 'bottom'
            },
            {
                target: '#subjectsContainer #yearTabs, #subjectsContainer .nav-tabs',
                title: 'Subject Tabs',
                content: 'After selecting a curriculum, subjects are grouped by year tabs.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#openConfirmModalBtn',
                title: 'Confirm Selected Courses',
                content: 'Select subjects, then confirm to import them into the system.',
                position: 'top',
                optional: true
            }
        ]
    });
})();
