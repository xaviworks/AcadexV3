/**
 * GE Coordinator Tutorial - Help Guides
 */

(function() {
    'use strict';

    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Help guides tutorial registration deferred.');
        return;
    }

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-manage-help-guides', {
        title: 'Manage Help Guides',
        description: 'Create, search, edit, and delete role-based help guides.',
        steps: [
            {
                target: '.container-fluid h1.h3, .container-fluid h1',
                title: 'Manage Help Guides',
                content: 'Use this page to maintain help content for selected user roles.',
                position: 'bottom'
            },
            {
                target: 'button[data-bs-target="#createGuideModal"]',
                title: 'Create Guide',
                content: 'Start a new guide entry from this button.',
                position: 'left',
                optional: true
            },
            {
                target: '#guidesSearch',
                title: 'Search Guides',
                content: 'Filter existing guides to find entries quickly.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#guidesTable thead, .guides-table-wrapper table thead',
                title: 'Guides Table',
                content: 'Review guide title, visibility, status, update time, and action controls.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-help-guides', {
        title: 'Help Guides',
        description: 'Browse available help guides.',
        steps: [
            {
                target: '.container-fluid h1.h3, .container-fluid h1',
                title: 'Help Guides List',
                content: 'This page contains help guides available to your role.',
                position: 'bottom'
            },
            {
                target: 'input[x-model="search"], .input-group input',
                title: 'Search',
                content: 'Use search to quickly locate a specific guide.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.guide-item:first-child .card-header, .help-guides-list .card:first-child .card-header',
                title: 'Open Guide',
                content: 'Click a guide header to expand and read its content.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.GECoordinatorTutorial.registerTutorial('gecoordinator-help-guides-detail', {
        title: 'Help Guide Details',
        description: 'Read a selected guide and view attachments.',
        steps: [
            {
                target: '.card .card-header h1.h4, .card .card-header h1',
                title: 'Guide Content',
                content: 'This page shows the full guide details.',
                position: 'bottom'
            },
            {
                target: '.pdf-thumbnail-card:first-child',
                title: 'PDF Attachments',
                content: 'Open PDF thumbnails to view reference materials in full-screen mode.',
                position: 'left',
                optional: true
            },
            {
                target: 'a[href$="/help-guides"], .btn.btn-outline-secondary.w-100',
                title: 'Back to Guides',
                content: 'Use this action to return to the help guides list.',
                position: 'top',
                optional: true
            }
        ]
    });
})();
