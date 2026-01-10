/**
 * GE Coordinator Tutorial - Students by Year
 * Tutorial for the Students by Year page
 */

(function() {
    'use strict';

    // Wait for GECoordinatorTutorial to be available
    if (typeof window.GECoordinatorTutorial === 'undefined') {
        console.warn('GECoordinatorTutorial core not loaded. Students tutorial registration deferred.');
        return;
    }

    // Register the students tutorial
    window.GECoordinatorTutorial.registerTutorial('gecoordinator-students', {
        title: 'GE Students by Year Level',
        description: 'Learn how to view and monitor students enrolled in GE courses',
        tableDataCheck: {
            selector: 'table tbody tr',
            emptySelectors: ['.alert-info', '.alert-warning'],
            entityName: 'students',
            noAddButton: true
        },
        steps: [
            {
                target: '.page-title h1, h1.text-3xl, h1',
                title: 'GE Students List',
                content: 'Welcome to the GE Students page. Here you can view all students enrolled in General Education courses, organized by year level and course.',
                position: 'bottom'
            },
            {
                target: 'select[name="year_level"], select#yearLevelFilter',
                title: 'Year Level Filter',
                content: 'Use this dropdown to filter students by year level (1st Year, 2nd Year, 3rd Year, 4th Year). Select a year level to see students at that stage.',
                position: 'bottom'
            },
            {
                target: 'select[name="course"], select#courseFilter',
                title: 'Course/Program Filter',
                content: 'Filter students by their academic program (e.g., BSIT, BSBA, BSN). This helps you see GE enrollment distribution across different courses.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.table-responsive, table',
                title: 'Students Table',
                content: 'This table displays all students matching your selected filters. Each row shows student details including their ID, name, course, year level, and enrollment status.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'table thead',
                title: 'Table Columns',
                content: 'The columns show: Student ID, Student Name, Course/Program, Year Level, Email Address, and Enrollment Status.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'tbody tr:first-child td:nth-child(1)',
                title: 'Student ID',
                content: 'The unique student identification number. Use this for quick reference when communicating with faculty or administration.',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: 'tbody tr:first-child td:nth-child(2)',
                title: 'Student Name',
                content: 'The full name of the student in "Last Name, First Name" format. Click on a name to view detailed student information (if available).',
                position: 'right',
                optional: true,
                requiresData: true
            },
            {
                target: '#studentsTable_filter input, .dataTables_filter input',
                title: 'Search Students',
                content: 'Use the search box to quickly find specific students by name, ID, or any visible field. Results filter in real-time as you type.',
                position: 'left',
                optional: true,
                requiresData: true
            },
            {
                target: '.dataTables_length, select[name*="length"]',
                title: 'Entries Per Page',
                content: 'Change how many student records are displayed per page. Choose from 10, 25, 50, or 100 entries for easier navigation.',
                position: 'right',
                optional: true,
                requiresData: true
            }
        ]
    });
})();
