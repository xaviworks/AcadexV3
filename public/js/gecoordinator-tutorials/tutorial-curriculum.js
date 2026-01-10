/**
 * GE Coordinator Tutorial - Import Courses (Select Subjects)
 * Tutorial for the Import Courses / Select Curriculum Subjects page
 */

(function() {
    'use strict';

    // Wait for GECoordinatorTutorial to be available
    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Curriculum tutorial registration deferred.');
        return;
    }

    // Register the import courses / select subjects tutorial
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-curriculum', {
        title: 'Import Courses from Curriculum',
        description: 'Learn how to select and import GE courses from a curriculum into the system',
        steps: [
            {
                target: '.page-title h1, h1.text-3xl, h1',
                title: 'Import Courses Page',
                content: 'Welcome to the Import Courses page. Here you can select a curriculum and choose which GE courses to import into the system for the current academic period.',
                position: 'bottom'
            },
            {
                target: '.alert-custom, .alert-info',
                title: 'Important Notice',
                content: 'This notice explains which course categories are managed by the GE Coordinator. GE, PD, PE, RS, and NSTP subjects fall under GE Coordinator management.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.curriculum-select-section, #curriculumSelect',
                title: 'Curriculum Selection',
                content: 'Use this dropdown to select which curriculum you want to import courses from. Curriculums are organized by program and version.',
                position: 'bottom'
            },
            {
                target: '#curriculumSelect',
                title: 'Select a Curriculum',
                content: 'Click the dropdown and choose a curriculum. Each option shows the curriculum name and associated course/program.',
                position: 'bottom'
            },
            {
                target: '#loadSubjectsBtn, .btn-load',
                title: 'Load Courses Button',
                content: 'After selecting a curriculum, click this button to load all available courses. The button becomes enabled once you select a curriculum.',
                position: 'left'
            },
            {
                target: '#yearTabs, .nav-tabs',
                title: 'Year Level Tabs',
                content: 'Once courses are loaded, they are organized by year level. Click on different tabs to view courses for each year (1st Year, 2nd Year, etc.).',
                position: 'bottom',
                optional: true
            },
            {
                target: '#selectAllBtn, .btn-select-all',
                title: 'Select All Button',
                content: 'Use this button to quickly select or deselect all courses at once. Helpful when you want to import most or all courses.',
                position: 'left',
                optional: true
            },
            {
                target: '#subjectsTableBody, .tab-content',
                title: 'Course Selection Area',
                content: 'This area displays all courses from the selected curriculum. Check the boxes next to courses you want to import into the system.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#selectedCount',
                title: 'Selection Counter',
                content: 'This counter shows how many courses you have currently selected for import. Review this before confirming.',
                position: 'top',
                optional: true
            },
            {
                target: '#openConfirmModalBtn, .btn-confirm',
                title: 'Confirm Selection',
                content: 'Once you\'ve selected the courses to import, click this button to confirm. A confirmation dialog will appear before the courses are added to the system.',
                position: 'top',
                optional: true
            }
        ]
    });
})();
