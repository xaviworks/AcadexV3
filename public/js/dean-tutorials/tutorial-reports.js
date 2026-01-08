/**
 * Dean Tutorial - Reports
 * Tutorial for the Dean CO Reports pages
 */

(function() {
    'use strict';

    // Wait for DeanTutorial to be available
    if (typeof window.DeanTutorial === 'undefined') {
        console.warn('DeanTutorial core not loaded. Reports tutorial registration deferred.');
        return;
    }

    // ========== Program CO Report ==========
    
    window.DeanTutorial.registerTutorial('dean-reports-co-program', {
        title: 'Program CO Summary',
        description: 'View course outcome compliance across your department',
        steps: [
            {
                target: '.container-fluid h2',
                title: 'Program CO Summary',
                content: 'This report shows Course Outcome (CO) compliance across all courses in your department. It provides an overview of how well students are achieving the defined outcomes.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle',
                title: 'Academic Period',
                content: 'The current academic year and semester for this report are shown here.',
                position: 'left',
                optional: true
            },
            {
                target: '.table thead',
                title: 'CO Summary Table',
                content: 'The table shows each course and its performance across 6 Course Outcomes (CO1-CO6). Each cell displays the attainment percentage.',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child td:first-child',
                title: 'Course Information',
                content: 'Each row shows a course code and description from your department.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success-subtle.text-success, .badge.bg-danger-subtle.text-danger',
                title: 'Attainment Indicators',
                content: 'Performance is color-coded: Green (≥75%) means the target is met, Red (<75%) indicates the outcome needs improvement. Raw scores are shown below each percentage.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'course outcomes',
            noAddButton: true
        }
    });

    // ========== Course CO Report - Chooser ==========
    
    window.DeanTutorial.registerTutorial('dean-reports-co-course', {
        title: 'Course CO Summary - Selection',
        description: 'Select a course to view detailed CO compliance',
        steps: [
            {
                target: '.container-fluid h2',
                title: 'Course CO Summary',
                content: 'This page allows you to view Course Outcome compliance for a specific course. Select a course to see detailed subject-level performance.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle',
                title: 'Academic Period',
                content: 'Reports are generated for the current academic year and semester.',
                position: 'left',
                optional: true
            },
            {
                target: '.row.g-4',
                title: 'Course Cards',
                content: 'Each card represents a course in your department. Click on one to view its CO summary.',
                position: 'bottom'
            },
            {
                target: '.course-card:first-of-type, .col-md-4:first-child .card',
                title: 'Course Card',
                content: 'Click on a course card or the "View Subjects" button to see detailed Course Outcome data for all subjects in that course.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // ========== Course CO Report - Detail ==========
    
    window.DeanTutorial.registerTutorial('dean-reports-co-course-detail', {
        title: 'Course CO Summary - Details',
        description: 'View subject-level CO attainment',
        steps: [
            {
                target: '.container-fluid h2',
                title: 'Course CO Summary',
                content: 'This page shows Course Outcome attainment for all subjects in the selected course.',
                position: 'bottom'
            },
            {
                target: 'a.btn-outline-secondary',
                title: 'Choose Another Course',
                content: 'Click this button to go back and select a different course.',
                position: 'left',
                optional: true
            },
            {
                target: '.table thead',
                title: 'CO Summary Table',
                content: 'The table shows each subject and its performance across 6 Course Outcomes (CO1-CO6).',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child',
                title: 'Subject Row',
                content: 'Each row shows a subject\'s CO attainment percentages. Green badges (≥75%) indicate targets met, red badges indicate areas needing improvement.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'subjects',
            noAddButton: true
        }
    });

    // ========== Student CO Report - Chooser ==========
    
    window.DeanTutorial.registerTutorial('dean-reports-co-student', {
        title: 'Student CO Report - Selection',
        description: 'Generate individual student CO reports',
        steps: [
            {
                target: '.container-fluid h2',
                title: 'Student CO Report',
                content: 'This page allows you to generate detailed Course Outcome reports for individual students. Select a subject and student to view their performance.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle',
                title: 'Academic Period',
                content: 'Reports are generated for the current academic year and semester.',
                position: 'left',
                optional: true
            },
            {
                target: '.col-lg-6:first-child .card',
                title: 'Step 1: Select Subject',
                content: 'First, choose a subject from the dropdown. This will load the list of students enrolled in that subject.',
                position: 'right'
            },
            {
                target: 'select#subject_id',
                title: 'Subject Dropdown',
                content: 'Select the subject you want to analyze. The page will refresh to show enrolled students.',
                position: 'bottom'
            },
            {
                target: '.col-lg-6:last-child .card',
                title: 'Step 2: Select Student',
                content: 'After selecting a subject, choose a student from this dropdown to generate their individual CO report.',
                position: 'left'
            },
            {
                target: 'button[type="submit"]',
                title: 'Generate Report',
                content: 'Click this button to generate the selected student\'s Course Outcome report.',
                position: 'top'
            }
        ]
    });

    // ========== Student CO Report - Detail ==========
    
    window.DeanTutorial.registerTutorial('dean-reports-co-student-detail', {
        title: 'Student CO Report - Details',
        description: 'View individual student CO performance',
        steps: [
            {
                target: '.container-fluid h2, h2.fw-bold',
                title: 'Student CO Report',
                content: 'This report shows the selected student\'s Course Outcome performance for the subject.',
                position: 'bottom'
            },
            {
                target: '.card:has(.table), .table-responsive',
                title: 'CO Performance Table',
                content: 'The table displays the student\'s attainment for each Course Outcome, showing percentages and raw scores.',
                position: 'bottom'
            }
        ]
    });
})();
