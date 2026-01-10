/**
 * GE Coordinator Tutorial - CO Reports
 * Tutorial for the Course Outcome Reports pages
 */

(function() {
    'use strict';

    // Wait for GECoordinatorTutorial to be available
    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Reports tutorial registration deferred.');
        return;
    }

    // ========== Program CO Report ==========
    
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-program', {
        title: 'Program CO Summary for GE',
        description: 'View course outcome compliance across all GE courses',
        steps: [
            {
                target: '.container-fluid h2, h2.fw-bold',
                title: 'Program CO Summary',
                content: 'This report shows Course Outcome (CO) compliance across all General Education courses. It provides an overview of how well students are achieving the defined GE outcomes.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle, .badge:contains("AY")',
                title: 'Academic Period',
                content: 'The current academic year and semester for this report are displayed here. Reports reflect the active academic period.',
                position: 'left',
                optional: true
            },
            {
                target: '.table thead',
                title: 'CO Summary Table',
                content: 'The table shows each GE course and its performance across 6 Course Outcomes (CO1-CO6). Each cell displays the attainment percentage and raw scores.',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child td:first-child',
                title: 'Course Information',
                content: 'Each row shows a GE course code and description. These are the General Education subjects offered across all programs.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: '.badge.bg-success-subtle.text-success, .badge.bg-danger-subtle.text-danger',
                title: 'Attainment Indicators',
                content: 'Performance is color-coded: Green badges (≥75%) mean the target is met, Red badges (<75%) indicate the outcome needs improvement. Raw scores (e.g., "45/60") are shown below each percentage.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: 'tbody tr:first-child',
                title: 'Reading CO Data',
                content: 'For each GE course, review the CO attainment percentages. Focus on red-flagged outcomes to identify areas where students need additional support or where curriculum adjustments may be needed.',
                position: 'right',
                optional: true,
                requiresData: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'course outcomes',
            noAddButton: true
        }
    });

    // ========== Course CO Report - Chooser ==========
    
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-course', {
        title: 'Course CO Summary - Selection',
        description: 'Select a course to view detailed GE CO compliance',
        steps: [
            {
                target: '.container-fluid h2, h2.fw-bold',
                title: 'Course CO Summary',
                content: 'This page allows you to view Course Outcome compliance for GE subjects by academic program. Select a course to see how students from that program perform in GE subjects.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle, .badge:contains("AY")',
                title: 'Academic Period',
                content: 'Reports are generated for the current active academic year and semester.',
                position: 'left',
                optional: true
            },
            {
                target: '.row.g-4, .course-cards',
                title: 'Course Selection Cards',
                content: 'Each card represents an academic program (e.g., BSIT, BSBA, BSN). Click on a course card to view GE subject performance for students in that program.',
                position: 'bottom'
            },
            {
                target: '.course-card:first-of-type, .col-md-4:first-child .card',
                title: 'Course Card',
                content: 'Click on a course card or the "View Subjects" button to see detailed Course Outcome data for all GE subjects taken by students in that program.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // ========== Course CO Report - Detail ==========
    
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-course-detail', {
        title: 'Course CO Summary - Details',
        description: 'View GE subject-level CO attainment for a program',
        steps: [
            {
                target: '.container-fluid h2, h2.fw-bold',
                title: 'Course CO Summary',
                content: 'This page shows Course Outcome attainment for all GE subjects taken by students in the selected program.',
                position: 'bottom'
            },
            {
                target: 'a.btn-outline-secondary, .btn:contains("Choose")',
                title: 'Choose Another Course',
                content: 'Click this button to go back and select a different academic program.',
                position: 'left',
                optional: true
            },
            {
                target: '.table thead',
                title: 'GE Subjects CO Table',
                content: 'The table shows each GE subject and its performance across 6 Course Outcomes (CO1-CO6). Each column represents a different learning outcome.',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child',
                title: 'Subject Row',
                content: 'Each row shows a GE subject\'s CO attainment percentages for students in the selected program. Green badges (≥75%) indicate targets met, red badges indicate areas needing improvement.',
                position: 'bottom',
                optional: true,
                requiresData: true
            },
            {
                target: 'tbody tr:first-child td:first-child',
                title: 'Subject Code and Title',
                content: 'The first column shows the GE subject code and full title. Use this to identify which General Education courses need curriculum review.',
                position: 'right',
                optional: true,
                requiresData: true
            }
        ],
        tableDataCheck: {
            selector: '.table tbody tr',
            entityName: 'subjects',
            noAddButton: true
        }
    });

    // ========== Student CO Report - Chooser ==========
    
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-student', {
        title: 'Student CO Report - Selection',
        description: 'Generate individual student CO reports for GE subjects',
        steps: [
            {
                target: '.container-fluid h2, h2.fw-bold',
                title: 'Student CO Report',
                content: 'This page allows you to generate detailed Course Outcome reports for individual students in GE subjects. Use this to monitor individual student performance.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle, .badge:contains("AY")',
                title: 'Academic Period',
                content: 'Reports are generated for the current active academic year and semester.',
                position: 'left',
                optional: true
            },
            {
                target: '.col-lg-6:first-child .card',
                title: 'Step 1: Select GE Subject',
                content: 'First, choose a General Education subject from the dropdown. This will load the list of students enrolled in that GE subject.',
                position: 'right'
            },
            {
                target: 'select#subject_id, select[name="subject_id"]',
                title: 'Subject Dropdown',
                content: 'Select the GE subject you want to analyze. The page will refresh to show all students enrolled in that subject.',
                position: 'bottom'
            },
            {
                target: '.col-lg-6:last-child .card',
                title: 'Step 2: Select Student',
                content: 'After selecting a GE subject, choose a specific student from this dropdown to generate their individual Course Outcome report.',
                position: 'left'
            },
            {
                target: 'select#student_id, select[name="student_id"]',
                title: 'Student Dropdown',
                content: 'Choose the student whose CO performance you want to review. Students are listed by name and ID.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'button[type="submit"], .btn-primary:contains("Generate")',
                title: 'Generate Report',
                content: 'Click this button to generate the selected student\'s Course Outcome report for the GE subject. The report will show their attainment for each CO.',
                position: 'top'
            }
        ]
    });

    // ========== Student CO Report - Detail ==========
    
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-reports-co-student-detail', {
        title: 'Student CO Report - Details',
        description: 'View individual student CO performance in GE subject',
        steps: [
            {
                target: '.container-fluid h2, h2.fw-bold',
                title: 'Student CO Report',
                content: 'This report shows the selected student\'s Course Outcome performance for the GE subject. Use this to identify areas where the student excels or needs support.',
                position: 'bottom'
            },
            {
                target: '.card .text-muted, .subject-info',
                title: 'Report Information',
                content: 'The header displays the GE subject name, student name and ID, and the academic period. Verify you\'re viewing the correct report.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.card:has(.table), .table-responsive',
                title: 'CO Performance Table',
                content: 'The table displays the student\'s attainment for each Course Outcome (CO1-CO6), showing both percentages and raw scores (e.g., "8/10").',
                position: 'bottom'
            },
            {
                target: '.table tbody tr:first-child',
                title: 'CO Attainment Row',
                content: 'Each row shows a Course Outcome number, the attainment percentage, and the raw score. Green indicates the student met the target (≥75%), red indicates areas needing improvement.',
                position: 'right',
                optional: true
            },
            {
                target: '.badge.bg-success, .badge.bg-danger',
                title: 'Performance Indicators',
                content: 'Color-coded badges make it easy to spot strengths (green) and weaknesses (red) in the student\'s GE subject performance.',
                position: 'left',
                optional: true
            },
            {
                target: '.btn:contains("Print"), .btn:contains("Export")',
                title: 'Export Options',
                content: 'Use these buttons to print or export the student\'s CO report for documentation, parent conferences, or academic advising.',
                position: 'left',
                optional: true
            },
            {
                target: 'a.btn-outline-secondary, .btn:contains("Back")',
                title: 'Generate Another Report',
                content: 'Click here to go back and generate a report for a different student or GE subject.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
