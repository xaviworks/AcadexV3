/**
 * Instructor Tutorial - Manage Students
 * Tutorial for the Manage Students page
 */

(function() {
    'use strict';

    // Wait for InstructorTutorial to be available
    if (typeof window.InstructorTutorial === 'undefined') {
        console.warn('InstructorTutorial core not loaded. Manage Students tutorial registration deferred.');
        return;
    }

    // Register the manage students tutorial
    window.InstructorTutorial.registerTutorial('instructor-manage-students', {
        title: 'Manage Students',
        description: 'Learn how to view and manage students in your subjects',
        steps: [
            {
                target: 'select[name="subject_id"], .subject-selector',
                title: 'Select Your Subject',
                content: 'First, select a subject from this dropdown to view its enrolled students. The page will update to show only students from the selected subject.',
                position: 'bottom'
            },
            {
                target: '.btn-success:contains("Add"), button:contains("Add Student")',
                title: 'Add Students',
                content: 'Click this button to add new students to your subject. You can add students individually or import them from an Excel file.',
                position: 'left',
                optional: true
            },
            {
                target: '#studentsTable thead, .table thead, table tr:first-child',
                title: 'Students Table',
                content: 'This table displays all students enrolled in the selected subject with their Student ID, Name, Course/Year/Section, and available actions.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.dataTables_filter input, input[type="search"]',
                title: 'Search Students',
                content: 'Use the search box to quickly find students by name, student ID, or any visible field. Results filter in real-time as you type.',
                position: 'left',
                optional: true
            },
            {
                target: '.btn-info:contains("View"), .action-btn',
                title: 'View Student Details',
                content: 'Click the eye icon to view detailed information about a student, including their grades across all terms.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.btn-danger:contains("Remove"), button[onclick*="remove"]',
                title: 'Remove Student',
                content: 'Use the trash icon to remove a student from this subject. Note: This only removes them from this specific subject, not from the system.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.badge:contains("Prelim"), .badge:contains("Midterm"), .term-badge',
                title: 'Term Grade Status',
                content: 'Color-coded badges show the status of grades for each term: Green = Completed, Yellow = In Progress, Gray = Not Started.',
                position: 'left',
                optional: true,
                requiresData: true
            }
        ],
        // Config for checking if students table has data
        tableDataCheck: {
            selector: '#studentsTable tbody tr, .table tbody tr',
            emptySelectors: ['.dataTables_empty', 'td[colspan]'],
            entityName: 'students',
            addButtonSelector: '.btn-success:contains("Add"), button:contains("Add Student")'
        }
    });
})();
