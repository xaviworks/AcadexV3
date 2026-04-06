/**
 * VPAA Tutorial - Reports
 * Tutorial for the VPAA Reports pages (CO Student, CO Course, CO Program)
 */

(function() {
    'use strict';

    // Wait for VPAATutorial to be available
    if (typeof window.VPAATutorial === 'undefined') {
        console.warn('VPAATutorial core not loaded. Reports tutorial registration deferred.');
        return;
    }

    // ========== Student CO Report Tutorials ==========

    // Student CO Report - Search/Selection
    window.VPAATutorial.registerTutorial('vpaa-reports-co-student', {
        title: 'Student CO Report - Selection',
        description: 'Learn how to search for a student and open their outcome report',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Student CO Report',
                content: 'This page lets you search students and open individual Course Outcome reports.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"]',
                title: 'Page Context',
                content: 'Use the subtitle and breadcrumbs to confirm you are in the Student Outcomes report flow.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'form[action*="co-student"], #student_query',
                title: 'Search Student',
                content: 'Enter a student name and choose from suggestions to select the correct student record.',
                position: 'bottom'
            },
            {
                target: '#student_query',
                title: 'Student Search Field',
                content: 'Type first name, last name, or middle name. Suggestions appear while you type.',
                position: 'bottom'
            },
            {
                target: '.student-fb-suggestions, .student-fb-item',
                title: 'Suggestions List',
                content: 'Pick the correct student from the suggestions to bind the selected student ID for report generation.',
                position: 'bottom'
            },
            {
                target: 'form[action*="co-student"] button[type="submit"], .btn.btn-success[type="submit"]',
                title: 'Run Search',
                content: 'Click Search to load the selected student and view their enrolled courses for this period.',
                position: 'top'
            },
            {
                target: '.enrolled-courses-card, .enrolled-courses-list',
                title: 'Enrolled Courses',
                content: 'After selecting a student, choose one enrolled course card to open the detailed outcome report.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Student CO Report - Detail
    window.VPAATutorial.registerTutorial('vpaa-reports-co-student-detail', {
        title: 'Student CO Report - Details',
        description: 'Learn how to read an individual student outcome report',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Student Outcome Detail',
                content: 'This page shows period-by-period and overall Course Outcome attainment for the selected student and subject.',
                position: 'bottom'
            },
            {
                target: '.card.border-0.shadow-sm.rounded-4.mb-4',
                title: 'Student and Course Context',
                content: 'This section confirms which student and course the report currently represents.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table.table-bordered thead, .table thead',
                title: 'Outcome Matrix',
                content: 'Columns represent COs while rows show period-specific and overall results.',
                position: 'bottom'
            },
            {
                target: '.table.table-bordered tbody tr:first-child, .table tbody tr:first-child',
                title: 'Period Row',
                content: 'Each period row shows attainment percent and raw/max score against the configured target threshold.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-success, .badge.bg-success, .badge.bg-danger',
                title: 'Overall and Performance Status',
                content: 'The overall row summarizes performance across periods. Badge colors indicate whether targets are met.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'student outcome rows',
            noAddButton: true
        }
    });

    // ========== Course CO Report Tutorials ==========
    
    // Course CO Report - Chooser
    window.VPAATutorial.registerTutorial('vpaa-reports-co-course', {
        title: 'Course CO Summary - Selection',
        description: 'Learn how to view course outcome compliance by course',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Course CO Summary',
                content: 'This page shows course outcome compliance across all subjects in a course. Select a course to view detailed CO performance.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"], .container-fluid .mb-4',
                title: 'Page Context',
                content: 'Use the subtitle and breadcrumbs to confirm you are in the Course Outcomes report chooser.',
                position: 'bottom'
            },
            {
                target: '.row.g-4, .container-fluid .row',
                title: 'Course Cards',
                content: 'Each card represents a course/program. Click on a course to view its subjects and their course outcome attainment data.',
                position: 'bottom'
            },
            {
                target: '.course-card:first-of-type, .col-md-4:first-child .card',
                title: 'Course Card',
                content: 'Click on any course card or the "View Subjects" button to see course outcome data for all subjects in that course.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Course CO Report - Detail
    window.VPAATutorial.registerTutorial('vpaa-reports-co-course-detail', {
        title: 'Course CO Summary - Details',
        description: 'Learn how to read the course outcome summary table',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Course CO Summary',
                content: 'This page shows course outcome attainment for all subjects in the selected course. Each subject\'s performance is displayed in the table.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"], .card.border-0.shadow-sm.rounded-4',
                title: 'Page Context',
                content: 'The subtitle and breadcrumb indicate the selected course and where this report sits in the VPAA flow.',
                position: 'bottom'
            },
            {
                target: '.table thead, .table.table-hover, .card.border-0.shadow-sm.rounded-4 .card-body',
                title: 'CO Summary Table',
                content: 'The table shows each subject and its performance across 6 course outcomes (CO1-CO6). Each cell shows the attainment percentage.',
                position: 'bottom'
            },
            {
                target: '.table thead th:has(.bi-mortarboard-fill), .table thead th:nth-child(2)',
                title: 'Course Outcome Columns',
                content: 'Each CO column (CO1 through CO6) shows the attainment percentage for that specific course outcome.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table tbody tr:first-child td:first-child',
                title: 'Subject Information',
                content: 'Each row shows a subject with its code and description.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success, .badge.bg-danger',
                title: 'Attainment Badges',
                content: 'Performance is color-coded: Green (≥75%) indicates the target is met, Red (<75%) indicates the outcome needs improvement. The raw score is shown below each percentage.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.bg-light.rounded-3, .mt-4.p-3',
                title: 'Performance Legend',
                content: 'The legend explains the color coding: Green means the 75% threshold is met, Red means below threshold.',
                position: 'top',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'subjects',
            noAddButton: true
        }
    });

    // ========== Program CO Report Tutorials ==========
    
    // Program CO Report - Department Selection
    window.VPAATutorial.registerTutorial('vpaa-reports-co-program', {
        title: 'Program CO Summary - Selection',
        description: 'Learn how to view course outcome compliance by program/department',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Program CO Summary',
                content: 'This page allows you to view course outcome compliance at the program level. Select a department to see its students\' overall CO attainment.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"], .container-fluid .mb-4',
                title: 'Page Context',
                content: 'Use the subtitle and breadcrumbs to confirm this is the department-selection step for Program Outcomes reports.',
                position: 'bottom'
            },
            {
                target: '.row.g-4, .container-fluid .row',
                title: 'Department Cards',
                content: 'Each card represents a department. Click on a department to view student-level course outcome data.',
                position: 'bottom'
            },
            {
                target: '.dept-card:first-of-type, .col-md-4:first-child .card',
                title: 'Department Card',
                content: 'Click on any department card or the "View Students" button to see student CO performance in that department.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Program CO Report - Program Selection and Summary
    window.VPAATutorial.registerTutorial('vpaa-reports-co-program-detail', {
        title: 'Program CO Summary - Details',
        description: 'Learn how to select a program and read program outcome attainment',
        steps: [
            {
                target: '.container-fluid h2.fw-bold, .container-fluid h1, h1.h3',
                title: 'Program Outcomes',
                content: 'This flow lets you choose a program under a department and review Program Learning Outcome attainment.',
                position: 'bottom'
            },
            {
                target: '.container-fluid .text-muted.mb-0, .breadcrumb, nav[aria-label="breadcrumb"], .card.border-0.shadow-sm.rounded-4',
                title: 'Page Context',
                content: 'Use this context to verify whether you are selecting a program or viewing the final summary matrix.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 .course-card, .row.g-4, .card.border-0.shadow-sm.rounded-4.mt-4 .card-body',
                title: 'Program Cards',
                content: 'Select a program card to open the Program Outcomes summary for that program.',
                position: 'bottom'
            },
            {
                target: '.table.table-bordered thead, .table thead',
                title: 'Program Outcomes Matrix',
                content: 'This matrix maps each active PLO to its attainment percentage and linked CO evidence.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.plo-result-chip, .plo-level-banner, .badge.text-bg-light',
                title: 'Evidence and Levels',
                content: 'Chips show contributing COs, while level banners and tones indicate attainment level against configured thresholds.',
                position: 'bottom',
                optional: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'program outcome rows',
            noAddButton: true
        }
    });
})();
