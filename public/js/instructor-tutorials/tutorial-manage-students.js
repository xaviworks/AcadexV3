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
                target: '#list-tab, button[data-bs-target="#list"]',
                title: 'Manage Students Tab',
                content: 'Use this tab to handle subject enrollment, update student details, and drop students from a selected course.',
                position: 'bottom'
            },
            {
                target: 'select[name="subject_id"], .subject-selector',
                title: 'Select Your Subject',
                content: 'First, select a subject from this dropdown to view its enrolled students. The page will update to show only students from the selected subject.',
                position: 'bottom'
            },
            {
                target: 'button[data-bs-target="#enrollStudentModal"], .btn-success:contains("Enroll Student")',
                title: 'Enroll Student',
                content: 'After selecting a subject, use this button to enroll a student manually. It opens the enrollment form modal.',
                position: 'left',
                optional: true
            },
            {
                target: '#list .table thead, .table thead',
                title: 'Students Table',
                content: 'This table lists enrolled students for the selected subject, including year level, status, and available actions.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'button[data-bs-target="#manageStudentModal"], .btn-success.btn-sm:contains("Edit")',
                title: 'Edit Student',
                content: 'Use Edit to update student details such as first name, last name, year level, and enrollment status for this subject.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: 'button[data-bs-target="#confirmDropModal"], .btn-danger.btn-sm:contains("Drop")',
                title: 'Drop Student',
                content: 'Use Drop to remove a student from the selected subject. This does not delete the student account from the system.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '#import-tab, button[data-bs-target="#import"]',
                title: 'Import Students Tab',
                content: 'Switch to this tab when you want to upload student lists from Excel and import multiple students at once.',
                position: 'bottom'
            },
            {
                target: '#uploadForm input[type="file"], #uploadForm button[type="submit"]',
                title: 'Upload Excel File',
                content: 'Choose a valid Excel file, then click Upload Excel to load student records for review before final import.',
                position: 'left',
                optional: true
            },
            {
                target: '#crossCheckBtn, #compareSubjectSelect',
                title: 'Cross Check Data',
                content: 'Use Compare with Course and Cross Check Data to identify which uploaded students are already enrolled and which are new.',
                position: 'top',
                optional: true
            },
            {
                target: '#importBtn, #selectedCount',
                title: 'Import Selected Students',
                content: 'Select students from the uploaded list, then click Import Selected to confirm and add them to your target subject.',
                position: 'top',
                optional: true,
                requiresData: true
            }
        ],
        // Config for checking if students table has data
        tableDataCheck: {
            selector: '#list .table tbody tr, .table tbody tr',
            emptySelectors: ['.dataTables_empty', 'td[colspan]'],
            entityName: 'students',
            addButtonSelector: '.btn-success:contains("Enroll Student"), button[data-bs-target="#enrollStudentModal"]'
        }
    });
})();
