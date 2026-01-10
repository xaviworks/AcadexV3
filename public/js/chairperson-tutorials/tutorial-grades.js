/**
 * Chairperson Tutorial - View Grades
 * Tutorial for the Students' Final Grades wizard
 */

(function() {
    'use strict';

    // Wait for ChairpersonTutorial to be available
    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Grades tutorial registration deferred.');
        return;
    }

    // Register the grades selection tutorial (Step 1: Select Instructor)
    window.ChairpersonTutorial.registerTutorial('chairperson-grades', {
        title: 'View Grades - Select Instructor',
        description: 'Learn how to navigate the grades viewing wizard',
        steps: [
            {
                target: '.page-title h1, h1:has(.bi-bar-chart-fill)',
                title: 'Students\' Final Grades',
                content: 'Welcome to the Grades Viewer! This is a step-by-step wizard to view student final grades. Start by selecting an instructor.',
                position: 'bottom'
            },
            {
                target: '.page-subtitle, .page-title p',
                title: 'Wizard Instructions',
                content: 'Follow the wizard steps: First select an instructor, then choose a subject, and finally view the students\' grades.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb, nav[aria-label="breadcrumb"]',
                title: 'Navigation Breadcrumb',
                content: 'The breadcrumb shows your current position in the wizard. Click any breadcrumb link to go back to a previous step.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 .col-md-4:first-child .card, .subject-card:first-child',
                title: 'Instructor Cards',
                content: 'Each card represents an instructor in your department. Click on any instructor card to proceed to subject selection.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.subject-card, .card[onclick]',
                title: 'Click to Select',
                content: 'Cards are clickable. Simply click on an instructor to move to the next step where you\'ll select which subject\'s grades to view.',
                position: 'right',
                optional: true
            }
        ]
    });

    // Register grades subjects tutorial (Step 2: Select Subject)
    window.ChairpersonTutorial.registerTutorial('chairperson-grades-subjects', {
        title: 'View Grades - Select Subject',
        description: 'Choose a subject to view student grades',
        steps: [
            {
                target: '.page-title h1',
                title: 'Select a Subject',
                content: 'You\'ve selected an instructor. Now choose which subject\'s grades you want to view.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb, nav[aria-label="breadcrumb"]',
                title: 'Breadcrumb Navigation',
                content: 'Notice the breadcrumb has updated. Click "Select Instructor" to go back and choose a different instructor.',
                position: 'bottom'
            },
            {
                target: '#subject-selection .col-md-4:first-child .card, .subject-card:first-child',
                title: 'Subject Cards',
                content: 'Each card shows a subject taught by the selected instructor. The subject code appears in the circular badge.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.subject-card .card-body h6, .wildcard-circle-positioned',
                title: 'Subject Information',
                content: 'The card displays the subject code and description. Click any subject card to view student grades for that course.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register grades students tutorial (Step 3: View Students' Grades)
    window.ChairpersonTutorial.registerTutorial('chairperson-grades-students', {
        title: 'View Grades - Student Results',
        description: 'Understanding the student grades table',
        steps: [
            {
                target: '.page-title h1',
                title: 'Students\' Final Grades',
                content: 'Here you can see all student grades for the selected instructor and subject combination.',
                position: 'bottom'
            },
            {
                target: '.breadcrumb, nav[aria-label="breadcrumb"]',
                title: 'Full Navigation Path',
                content: 'The breadcrumb shows the complete path: Instructor → Subject → Grades. Click any step to navigate back.',
                position: 'bottom'
            },
            {
                target: 'table, .table',
                title: 'Grades Table',
                content: 'This table displays all students enrolled in the subject with their term grades, final average, and remarks.',
                position: 'top'
            },
            {
                target: 'thead th:nth-child(2), th:contains("Prelim")',
                title: 'Term Grade Columns',
                content: 'Grades are broken down by term: Prelim, Midterm, Prefinal, and Final. Each column shows the grade for that grading period.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'thead th:contains("Final Average"), th.text-success',
                title: 'Final Average',
                content: 'The Final Average is calculated from all four term grades. This determines whether the student passed or failed.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'thead th:contains("Remarks")',
                title: 'Remarks Column',
                content: 'Shows "Passed" (green badge, 75% or higher) or "Failed" (red badge, below 75%) based on the final average.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'thead th:contains("Notes"), th:last-child',
                title: 'Notes Column',
                content: 'You can add notes for each student to record observations, concerns, or follow-up actions. Click the edit icon to add or modify notes.',
                position: 'left',
                optional: true
            },
            {
                target: 'tbody tr:first-child .badge, .badge.bg-success-subtle, .badge.bg-danger-subtle',
                title: 'Pass/Fail Indicators',
                content: 'Green "Passed" badges indicate students meeting the 75% threshold. Red "Failed" badges highlight students needing intervention.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
