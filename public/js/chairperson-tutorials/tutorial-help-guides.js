/**
 * Chairperson Tutorial - Help Guides
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Help guides tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-help-guides', {
        title: 'Help Guides',
        description: 'Learn how to browse user help guides.',
        steps: [
            {
                target: '.container-fluid h1.h3, .container-fluid h1',
                title: 'Help Guides Library',
                content: 'This page lists available help guides for your role.',
                position: 'bottom'
            },
            {
                target: 'input[x-model="search"], .input-group input',
                title: 'Search Guides',
                content: 'Use search to quickly find relevant guides by title or content.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.guide-item:first-child .card-header, .help-guides-list .card:first-child .card-header',
                title: 'Open a Guide',
                content: 'Click a guide header to expand content and view any attachments.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-help-guides-detail', {
        title: 'Help Guide Details',
        description: 'Read guide content and open attachments.',
        steps: [
            {
                target: '.card .card-header h1.h4, .card .card-header h1',
                title: 'Guide Content',
                content: 'This page shows the full selected help guide content.',
                position: 'bottom'
            },
            {
                target: '.pdf-thumbnail-card:first-child',
                title: 'Attachments',
                content: 'Click PDF thumbnails to open attachments in the full-screen viewer.',
                position: 'left',
                optional: true
            },
            {
                target: 'a[href$="/help-guides"], .btn.btn-outline-secondary.w-100',
                title: 'Back to Guides',
                content: 'Use this button to return to the help guides list.',
                position: 'top',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-manage-help-guides', {
        title: 'Manage Help Guides',
        description: 'Create, edit, and maintain guides for user roles.',
        steps: [
            {
                target: '.container-fluid h1.h3, .container-fluid h1',
                title: 'Manage Help Guides',
                content: 'This page lets you create and manage help guides visible to selected roles.',
                position: 'bottom'
            },
            {
                target: 'button[data-bs-target="#createGuideModal"]',
                title: 'Create Guide',
                content: 'Use this button to open the guide creation form.',
                position: 'left',
                optional: true
            },
            {
                target: '#guidesSearch',
                title: 'Search Existing Guides',
                content: 'Filter the table to quickly find guides by keyword.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#guidesTable thead, .guides-table-wrapper table thead',
                title: 'Guides Table',
                content: 'The table shows guide priority, visibility, status, update time, and actions.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.action-btn-group .btn-edit, .action-btn-group .btn-delete',
                title: 'Guide Actions',
                content: 'Use actions to edit or delete guides as needed.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
