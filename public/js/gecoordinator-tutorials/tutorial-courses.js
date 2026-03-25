/**
 * GE Coordinator Tutorial - Courses
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Courses tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-manage-courses', {
        title: 'Manage Courses',
        description: 'Manage instructor assignments per GE subject.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Manage Courses',
                content: 'This page is for assigning and editing instructors for GE subjects.',
                position: 'bottom'
            },
            {
                target: '#viewMode',
                title: 'View Mode Switcher',
                content: 'Switch between Year View and Full View depending on how you want to manage subjects.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#yearTabs',
                title: 'Year Tabs',
                content: 'In Year View, use tabs to manage subjects by academic year level.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.subject-edit-btn, .subject-view-btn',
                title: 'Assignment Actions',
                content: 'Use View and Edit actions to inspect and update instructor assignments.',
                position: 'left',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-import-courses', {
        title: 'Import GE Curriculum Subjects',
        description: 'Load a curriculum and confirm selected GE subjects.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Confirm Curriculum Subjects',
                content: 'This page lets you import eligible GE curriculum subjects.',
                position: 'bottom'
            },
            {
                target: '#curriculumSelect',
                title: 'Select Curriculum',
                content: 'Pick a curriculum first, then load available subjects.',
                position: 'bottom'
            },
            {
                target: '#loadSubjectsBtn',
                title: 'Load Subjects',
                content: 'Load subjects to review and choose what will be confirmed.',
                position: 'left',
                optional: true
            },
            {
                target: '#subjectsContainer .nav-tabs, #yearTabs',
                title: 'Subject Selection Tabs',
                content: 'After loading, subjects are grouped by year tabs for selection.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#openConfirmModalBtn',
                title: 'Confirm Selection',
                content: 'Confirm selected subjects to apply them to the academic period.',
                position: 'top',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-manage-schedule', {
        title: 'Manage Schedule',
        description: 'Review GE subjects, instructors, and enrollment data.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Manage Schedule',
                content: 'This page summarizes schedule-relevant GE subject and instructor data.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Schedule Table',
                content: 'Review course code, year level, assigned instructors, and enrolled students.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'button[onclick*="openScheduleModal"], button[onclick*="viewSubjectDetails"]',
                title: 'Schedule Actions',
                content: 'Open schedule tools and detailed subject views from these action buttons.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
