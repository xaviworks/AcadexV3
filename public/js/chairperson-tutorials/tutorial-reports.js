/**
 * Chairperson Tutorial - CO Reports
 * Tutorials for Course Outcome reports pages
 */

(function() {
    'use strict';

    // Wait for ChairpersonTutorial to be available
    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Reports tutorials registration deferred.');
        return;
    }

    // Register Program CO Report tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-program', {
        title: 'Program Outcomes Summary',
        description: 'Learn how to interpret program-level Course Outcome compliance',
        steps: [
            {
                target: 'h2.fw-bold, h2:has(.bi-diagram-3)',
                title: 'Program Outcomes Summary',
                content: 'This report shows Course Outcome (CO) compliance across all courses in your department. It provides a high-level overview of academic performance.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle:has(.bi-calendar3)',
                title: 'Academic Period',
                content: 'The current academic year and semester are displayed here. Reports are filtered to show data for this specific period.',
                position: 'left',
                optional: true
            },
            {
                target: '.btn-outline-secondary:has(.bi-arrow-left)',
                title: 'Back to Dashboard',
                content: 'Click this button to return to your dashboard at any time.',
                position: 'left',
                optional: true
            },
            {
                target: 'table, .table',
                title: 'CO Compliance Table',
                content: 'This table shows each course and its compliance percentage for Course Outcomes 1-6 (CO1 through CO6).',
                position: 'top'
            },
            {
                target: 'thead th:first-child',
                title: 'Course Column',
                content: 'Lists all courses in your department with their code and description.',
                position: 'right',
                optional: true
            },
            {
                target: 'thead th:nth-child(2), thead th:contains("CO1")',
                title: 'CO Columns',
                content: 'Each CO column (CO1-CO6) shows the compliance percentage for that specific Course Outcome. Green indicates 75%+ compliance, red indicates below 75%.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success-subtle, .badge.bg-danger-subtle',
                title: 'Compliance Indicators',
                content: 'Green badges show courses meeting the 75% threshold. Red badges indicate areas needing attention. The raw score (e.g., "45/60") is shown below the percentage.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register Course CO Chooser tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-course', {
        title: 'Course Outcomes - Select Course',
        description: 'Learn how to navigate course-level CO reports',
        steps: [
            {
                target: 'h2.fw-bold, h2:has(.bi-book)',
                title: 'Course Outcomes Summary',
                content: 'Select a course to view detailed Course Outcome compliance for all subjects within that course.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success-subtle:has(.bi-calendar3)',
                title: 'Current Period',
                content: 'Reports show data for the displayed academic year and semester.',
                position: 'left',
                optional: true
            },
            {
                target: '.row.g-4 .col-md-4:first-child, .course-card:first-child',
                title: 'Course Cards',
                content: 'Each card represents a course in your department. Click any card to view its detailed CO compliance report.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.course-card .btn-success, .course-card',
                title: 'View Course Details',
                content: 'Click the "View Courses" button or anywhere on the card to see subject-level CO breakdown for that course.',
                position: 'right',
                optional: true
            }
        ]
    });

    // Register Course CO Detail tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-course-detail', {
        title: 'Course Outcomes Detail',
        description: 'Understanding subject-level CO compliance',
        steps: [
            {
                target: 'h2.fw-bold',
                title: 'Course CO Details',
                content: 'This page shows Course Outcome compliance for each subject within the selected course.',
                position: 'bottom'
            },
            {
                target: '.btn-outline-secondary:has(.bi-arrow-left), a[href*="co-course"]:not([href*="course_id"])',
                title: 'Choose Different Course',
                content: 'Click here to go back and select a different course to analyze.',
                position: 'left',
                optional: true
            },
            {
                target: 'table, .table',
                title: 'Subject CO Table',
                content: 'This table breaks down CO compliance by individual subject. Each row shows one subject\'s performance across all 6 Course Outcomes.',
                position: 'top'
            },
            {
                target: 'tbody tr:first-child td:first-child',
                title: 'Subject Information',
                content: 'Each row shows the subject code and description. Subjects are listed under the selected course.',
                position: 'right',
                optional: true
            },
            {
                target: '.bg-light.rounded-3, .mt-4.p-3',
                title: 'Performance Legend',
                content: 'The legend explains how to interpret the color-coded results: green for 75%+ compliance, red for below 75%.',
                position: 'top',
                optional: true
            }
        ]
    });

    // Register Student CO Chooser tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-student', {
        title: 'Student Outcomes - Selection',
        description: 'Learn how to generate student-level CO reports',
        steps: [
            {
                target: 'h2.fw-bold, h2:has(.bi-person-lines-fill)',
                title: 'Student Outcomes Summary',
                content: 'This page lets you view individual student Course Outcome performance. Select a course and student to generate a personalized report.',
                position: 'bottom'
            },
            {
                target: '.col-lg-6:first-child .card, .card:has(#subject_id)',
                title: 'Step 1: Select Course',
                content: 'First, choose a course from the dropdown. This filters the student list to show only students enrolled in that course.',
                position: 'right'
            },
            {
                target: '#subject_id, select[name="subject_id"]',
                title: 'Course Dropdown',
                content: 'Select a course from this dropdown. The list shows all courses in your department with their codes and descriptions.',
                position: 'bottom'
            },
            {
                target: '.col-lg-6:last-child .card, .card:has(#student_id)',
                title: 'Step 2: Select Student',
                content: 'After selecting a course, choose a student from the list. The dropdown becomes enabled once a course is selected.',
                position: 'left'
            },
            {
                target: '#student_id, select[name="student_id"]',
                title: 'Student Dropdown',
                content: 'Select a student to view their individual CO performance. Students are listed by last name, first name.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'button[type="submit"], .btn-success.w-100',
                title: 'Generate Report',
                content: 'Click "Generate Report" to view the selected student\'s Course Outcome performance across all assessment activities.',
                position: 'top'
            }
        ]
    });

    // Register Student CO Detail tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-student-detail', {
        title: 'Student CO Performance',
        description: 'Understanding individual student CO compliance',
        steps: [
            {
                target: 'h2.fw-bold, .card-header',
                title: 'Student CO Report',
                content: 'This report shows the selected student\'s performance on each Course Outcome, with scores from individual assessment activities.',
                position: 'bottom'
            },
            {
                target: '.btn-outline-secondary:has(.bi-arrow-left)',
                title: 'Select Different Student',
                content: 'Click here to go back and generate a report for a different student.',
                position: 'left',
                optional: true
            },
            {
                target: 'table, .table',
                title: 'CO Performance Table',
                content: 'This table shows the student\'s score for each Course Outcome, broken down by assessment activity (quizzes, exams, activities, etc.).',
                position: 'top',
                optional: true
            },
            {
                target: '.badge.bg-success, .badge.bg-danger',
                title: 'Performance Indicators',
                content: 'Green indicates the student met the CO requirement (75%+), red indicates they did not meet the threshold and may need additional support.',
                position: 'left',
                optional: true
            }
        ]
    });
})();
