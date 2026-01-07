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
    
    // Student CO Report - Chooser
    window.VPAATutorial.registerTutorial('vpaa-reports-co-student', {
        title: 'Student CO Report - Selection',
        description: 'Learn how to generate individual student course outcome reports',
        steps: [
            {
                target: '.container-fluid h2',
                title: 'Student CO Report',
                content: 'This page allows you to generate detailed course outcome reports for individual students. Select a subject and student to view their performance.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle',
                title: 'Academic Period',
                content: 'The current academic year and semester are shown here. Reports are generated for this period.',
                position: 'left',
                optional: true
            },
            {
                target: 'a.btn-outline-secondary[href*="dashboard"]',
                title: 'Back to Dashboard',
                content: 'Click this button to return to the VPAA dashboard.',
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
                target: 'select#student_id',
                title: 'Student Dropdown',
                content: 'Select a student to view their course outcome performance. This dropdown is enabled after selecting a subject.',
                position: 'bottom'
            },
            {
                target: 'button[type="submit"]',
                title: 'Generate Report',
                content: 'Click this button to generate the selected student\'s course outcome report.',
                position: 'top'
            }
        ]
    });

    // ========== Course CO Report Tutorials ==========
    
    // Course CO Report - Chooser
    window.VPAATutorial.registerTutorial('vpaa-reports-co-course', {
        title: 'Course CO Summary - Selection',
        description: 'Learn how to view course outcome compliance by course',
        steps: [
            {
                target: '.container-fluid h2',
                title: 'Course CO Summary',
                content: 'This page shows course outcome compliance across all subjects in a course. Select a course to view detailed CO performance.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle',
                title: 'Academic Period',
                content: 'Reports are generated for the current academic year and semester shown here.',
                position: 'left',
                optional: true
            },
            {
                target: 'a.btn-outline-secondary[href*="dashboard"]',
                title: 'Back to Dashboard',
                content: 'Return to the VPAA dashboard by clicking this button.',
                position: 'left',
                optional: true
            },
            {
                target: '.row.g-4',
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
                target: '.container-fluid h2',
                title: 'Course CO Summary',
                content: 'This page shows course outcome attainment for all subjects in the selected course. Each subject\'s performance is displayed in the table.',
                position: 'bottom'
            },
            {
                target: 'a.btn-outline-secondary[href*="co-course"]',
                title: 'Choose Another Course',
                content: 'Click this button to go back and select a different course.',
                position: 'left',
                optional: true
            },
            {
                target: '.table thead',
                title: 'CO Summary Table',
                content: 'The table shows each subject and its performance across 6 course outcomes (CO1-CO6). Each cell shows the attainment percentage.',
                position: 'bottom'
            },
            {
                target: '.table thead th:has(.bi-mortarboard-fill)',
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
                target: '.container-fluid h2',
                title: 'Program CO Summary',
                content: 'This page allows you to view course outcome compliance at the program level. Select a department to see its students\' overall CO attainment.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle',
                title: 'Academic Period',
                content: 'Reports are generated for the academic year and semester shown here.',
                position: 'left',
                optional: true
            },
            {
                target: 'a.btn-outline-secondary[href*="dashboard"]',
                title: 'Back to Dashboard',
                content: 'Return to the VPAA dashboard by clicking this button.',
                position: 'left',
                optional: true
            },
            {
                target: '.row.g-4',
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
})();
