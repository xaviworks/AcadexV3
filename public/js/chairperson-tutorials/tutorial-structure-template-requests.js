/**
 * Chairperson Tutorial - Structure Formula Requests
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Formula requests tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-formula-requests', {
        title: 'Formula Requests',
        description: 'Manage custom structure formula requests.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Structure Formula Requests',
                content: 'This page lists your submitted formula requests and their review status.',
                position: 'bottom'
            },
            {
                target: 'a[href*="/structure-templates/create"], .btn.btn-success',
                title: 'Create New Request',
                content: 'Use this button to create and submit a new formula request.',
                position: 'left',
                optional: true
            },
            {
                target: '.request-card, .card[data-status]',
                title: 'Request Cards',
                content: 'Each card shows request status, metadata, and access to detailed view.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-formula-requests-create', {
        title: 'Create Formula Request',
        description: 'Design a custom grading structure template request.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Create Request Form',
                content: 'Fill out template information, define grading components, and submit for approval.',
                position: 'bottom'
            },
            {
                target: '#templateRequestForm',
                title: 'Template Request Form',
                content: 'This form includes template details and dynamic grading component configuration.',
                position: 'bottom'
            },
            {
                target: '#components-container',
                title: 'Grading Components',
                content: 'Add main and sub-components with weights that total exactly 100%.',
                position: 'top',
                optional: true
            },
            {
                target: '#submit-btn',
                title: 'Submit Request',
                content: 'Submit becomes enabled when your configuration is valid.',
                position: 'top',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-formula-requests-detail', {
        title: 'Formula Request Details',
        description: 'Review a submitted formula request and its status.',
        steps: [
            {
                target: '.container-fluid h1, .container-fluid h4, .container-fluid h5',
                title: 'Request Detail Page',
                content: 'This page shows full request configuration, admin feedback, and approval history.',
                position: 'bottom'
            },
            {
                target: '.card',
                title: 'Request Information',
                content: 'Review the structure setup and status details for this request.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
