/**
 * VPAA Tutorial - Course Outcome Attainment
 * Tutorial for the VPAA Course Outcome Attainment pages
 */

(function() {
    'use strict';

    // Wait for VPAATutorial to be available
    if (typeof window.VPAATutorial === 'undefined') {
        console.warn('VPAATutorial core not loaded. CO Attainment tutorial registration deferred.');
        return;
    }

    // Register the CO attainment department selection tutorial
    window.VPAATutorial.registerTutorial('vpaa-co-attainment', {
        title: 'Course Outcome Attainment',
        description: 'Learn how to view course outcome attainment across departments',
        steps: [
            {
                target: '.breadcrumb',
                title: 'Navigation',
                content: 'You\'re in the Course Outcome Attainment section. Use the breadcrumb to navigate back to the dashboard.',
                position: 'bottom'
            },
            {
                target: '.card.border-0.shadow-sm.rounded-4.mb-3',
                title: 'Selection Instructions',
                content: 'To view course outcome attainment, first select a department from the cards below.',
                position: 'bottom'
            },
            {
                target: '#department-selection, .row.g-4',
                title: 'Department Cards',
                content: 'Each card represents a department. Click on a department to view its subjects and their course outcome data.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, .col-md-4:first-child .card',
                title: 'Department Card',
                content: 'Click on any department card to view the subjects offered by that department and their course outcomes.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register the subjects list tutorial
    window.VPAATutorial.registerTutorial('vpaa-co-attainment-subjects', {
        title: 'Subject Selection',
        description: 'Learn how to select subjects for course outcome analysis',
        steps: [
            {
                target: '.card:has(#subject-selection), .card.border-0.shadow-sm.rounded-4',
                title: 'Subject Selection',
                content: 'This page shows all subjects in the selected department. Click on a subject to view its course outcomes.',
                position: 'bottom'
            },
            {
                target: '#subject-selection, .row.g-4',
                title: 'Subject Cards',
                content: 'Each card represents a subject. The card shows the subject code and description.',
                position: 'bottom'
            },
            {
                target: '.subject-card:first-of-type, .col-md-4:first-child .card',
                title: 'Subject Card',
                content: 'Click on any subject card to view its course outcomes and student attainment data.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register the subject CO detail tutorial
    window.VPAATutorial.registerTutorial('vpaa-co-attainment-subject', {
        title: 'Course Outcome Details',
        description: 'Learn how to read course outcome attainment data',
        steps: [
            {
                target: '.card:has(.bi-list-check), .card.border-0.shadow-sm.rounded-4:first-of-type',
                title: 'Course Outcomes Table',
                content: 'This table lists all course outcomes (COs) defined for the selected subject. Each row shows a specific outcome with its details.',
                position: 'bottom'
            },
            {
                target: '.table thead.table-success',
                title: 'Table Headers',
                content: 'The columns show: CO Code (identifier), Identifier, Description (what students should achieve), Academic Period, and Status.',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child',
                title: 'Course Outcome Row',
                content: 'Each row represents one course outcome. The CO Code uniquely identifies the outcome, while the Description explains what competency is being measured.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success',
                title: 'Status Badge',
                content: 'The status indicates whether the course outcome is Active or Inactive. Active outcomes are currently being assessed.',
                position: 'left',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'course outcomes',
            noAddButton: true
        }
    });
})();
