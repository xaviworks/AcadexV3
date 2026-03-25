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
        description: 'Learn how to create and manage graded activities for your subjects',
        steps: [
            {
                target: 'select[name="subject_id"], .subject-selector',
                title: 'Select Your Subject',
                content: 'Choose the subject you want to create activities for. Each subject can have different activities and assessment types.',
                position: 'bottom'
            },
            {
                target: '.btn-success:contains("Add"), .btn-primary:contains("Create"), button:contains("New Activity")',
                title: 'Create New Activity',
                content: 'Click this button to create a new graded activity. You can add quizzes, assignments, projects, exams, and more.',
                position: 'left',
                optional: true
            },
            {
                target: '.term-selector, select[name="term"], .btn-group:has([data-term])',
                title: 'Filter by Term',
                content: 'Use this to filter activities by term (Prelim, Midterm, Pre-Final, Finals). You can organize activities by when they occur in the semester.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#activitiesTable thead, table thead',
                title: 'Activities Table',
                content: 'This table shows all your activities with their Title, Type (Quiz, Assignment, etc.), Term, Total Points, and Status.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.activity-type-badge, .badge',
                title: 'Activity Types',
                content: 'Each activity is color-coded by type: Written Work (blue), Performance Task (purple), Exam (red), and others. This helps you track assessment balance.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-primary:contains("View"), .btn-info',
                title: 'View & Enter Scores',
                content: 'Click to view activity details and enter student scores. You can grade students and see completion statistics.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-warning:contains("Edit"), button[onclick*="edit"]',
                title: 'Edit Activity',
                content: 'Modify activity details like title, description, total points, or attached course outcomes. Be careful when editing after students have been graded.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-danger:contains("Delete"), button[onclick*="delete"]',
                title: 'Delete Activity',
                content: 'Remove an activity from your subject. Warning: This will delete all associated student scores. Use with caution!',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.progress, .completion-bar',
                title: 'Grading Progress',
                content: 'Progress bars show how many students have been graded for each activity. Track your grading workflow at a glance.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ],
        tableDataCheck: {
            selector: '#activitiesTable tbody tr, .table tbody tr',
            emptySelectors: ['.dataTables_empty', '.no-data', 'td[colspan]'],
            entityName: 'activities',
            addButtonSelector: '.btn-success:contains("Add"), .btn-primary:contains("Create")'
        }
    });

    // Also register the create activity tutorial
    window.InstructorTutorial.registerTutorial('instructor-activities-create', {
        title: 'Create Activity',
        description: 'Learn how to create a new graded activity',
        steps: [
            {
                target: 'input[name="title"], #activity-title',
                title: 'Activity Title',
                content: 'Enter a descriptive title for your activity (e.g., "Quiz 1 - Cell Biology", "Midterm Exam").',
                position: 'bottom'
            },
            {
                target: 'select[name="type"], #activity-type',
                title: 'Activity Type',
                content: 'Select the type of activity: Written Work (quizzes, exercises), Performance Task (projects, labs), or Exam (major assessments).',
                position: 'bottom'
            },
            {
                target: 'select[name="term"], #activity-term',
                title: 'Select Term',
                content: 'Choose which term this activity belongs to. This helps organize activities throughout the semester.',
                position: 'bottom'
            },
            {
                target: 'input[name="total_points"], #total-points',
                title: 'Total Points',
                content: 'Set the maximum points/score for this activity. Student scores will be entered out of this total.',
                position: 'bottom'
            },
            {
                target: 'textarea[name="description"], #activity-description',
                title: 'Description (Optional)',
                content: 'Add any additional details about the activity - instructions, topics covered, special requirements, etc.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'select[name="course_outcomes[]"], .course-outcomes-select',
                title: 'Link Course Outcomes',
                content: 'Select which course outcomes this activity assesses. This helps track student progress on learning objectives.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.btn-primary:contains("Create"), button[type="submit"]',
                title: 'Create Activity',
                content: 'Click to create the activity. You can then start entering student scores for this activity.',
                position: 'left'
            }
        ]
    });
})();
