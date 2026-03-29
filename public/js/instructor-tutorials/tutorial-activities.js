/**
 * Instructor Tutorial - Activities
 * Tutorial for the Activities Management page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Activities tutorial registration deferred.');
        return;
    }

    // Register the activities tutorial
    window.InstructorTutorial.registerTutorial('instructor-activities', {
        title: 'Manage Activities',
        description: 'Learn how to manage activity setup, filtering, and formula alignment',
        steps: [
            {
                target: '#activities-tab, button[data-bs-target="#activities"]',
                title: 'My Activities Tab',
                content: 'Use this tab to manage your class activities, including filtering, creating new activities, and reviewing current records.',
                position: 'bottom'
            },
            {
                target: '#activityFilters select[name="subject_id"], select[name="subject_id"]',
                title: 'Select Course',
                content: 'Choose the course you want to manage. Activity records and formula alignment are based on this selected subject.',
                position: 'bottom'
            },
            {
                target: '#activityFilters select[name="term"], select[name="term"]',
                title: 'Filter by Period',
                content: 'Use this filter to focus on one grading period or view all periods.',
                position: 'bottom',
                
            },
            {
                target: '#activityFilters .badge, .badge.bg-success, .badge.bg-warning',
                title: 'Alignment Status',
                content: 'This badge shows whether your current activity setup is aligned with the grading formula for the selected subject and term.',
                position: 'bottom',
                
            },
            {
                target: 'button[data-bs-target="#createActivityModal"], .btn-success:contains("New Activity")',
                title: 'Create New Activity',
                content: 'Click this button to open the create activity modal and add a new assessment component.',
                position: 'left'
            },
            {
                target: '#alignment-tab, button[data-bs-target="#alignment"]',
                title: 'Formula Alignment Tab',
                content: 'Switch here to review activity distribution and realign components based on the formula structure.',
                position: 'bottom'
            },
            {
                target: '#formula-tab, button[data-bs-target="#formula"]',
                title: 'Formula Info Tab',
                content: 'Open this tab to view formula details and component weight guidance while planning activities.',
                position: 'bottom'
            }
        ],
        tableDataCheck: {
            selector: '#activitiesTable tbody tr, .table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', 'td[colspan]', '.empty-state', '.card .card-body .text-muted'],
            entityName: 'activities',
            addButtonSelector: '.btn-success:contains("Save Activity"), .btn-success:contains("New Activity"), button[data-bs-target="#createActivityModal"]'
        }
    });

    // Also register the create activity tutorial
    window.InstructorTutorial.registerTutorial('instructor-activities-create', {
        title: 'Create Activity',
        description: 'Learn the complete activity page workflow without skipped steps',
        steps: [
            {
                target: '#activities-tab, button[data-bs-target="#activities"]',
                title: 'My Activities Tab',
                content: 'Start here to manage your activities list, create new entries, and monitor setup for each course.',
                position: 'bottom'
            },
            {
                target: '#activityFilters select[name="subject_id"], select[name="subject_id"]',
                title: 'Select Course',
                content: 'Choose your target course before creating or reviewing activities.',
                position: 'bottom'
            },
            {
                target: '#activityFilters select[name="term"], select[name="term"]',
                title: 'Period Filter',
                content: 'Filter the list to one period or keep all periods visible while planning assessments.',
                position: 'bottom'
            },
            {
                target: '#activityFilters .badge, .badge.bg-success, .badge.bg-warning',
                title: 'Check Alignment Status',
                content: 'Use this status indicator to quickly verify whether your setup needs formula alignment updates.',
                position: 'bottom'
            },
            {
                target: 'button[data-bs-target="#createActivityModal"], .btn-success:contains("New Activity")',
                title: 'Open Create Activity Modal',
                content: 'Click this to open the form for adding a new activity to the selected course.',
                position: 'left'
            },
            {
                target: '#alignment-tab, button[data-bs-target="#alignment"]',
                title: 'Formula Alignment Tab',
                content: 'Switch to this tab to inspect component counts by term and realign when needed.',
                position: 'bottom'
            },
            {
                target: '#formula-tab, button[data-bs-target="#formula"]',
                title: 'Formula Info Tab',
                content: 'Review formula structure details here to guide how you distribute activities.',
                position: 'bottom'
            }
        ]
    });
})();
