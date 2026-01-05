/**
 * Admin Tutorial - Academic Structure
 * Tutorials for Departments, Programs, Subjects, and Academic Periods pages
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Academic structure tutorial registration deferred.');
        return;
    }

    // Register the departments tutorial
    window.AdminTutorial.registerTutorial('admin-departments', {
        title: 'Department Management',
        description: 'Learn how to add and view academic departments',
        tableDataCheck: {
            selector: '#departmentsTable tbody tr, table tbody tr',
            emptySelectors: ['.dataTables_empty', 'td[colspan]'],
            entityName: 'departments',
            addButtonSelector: 'button.btn-success'
        },
        steps: [
            {
                target: 'button.btn-success',
                title: 'Add Department',
                content: 'Click here to create a new academic department. You\'ll enter a short code (e.g., CITE, CAS, CON) and the full department description.',
                position: 'left'
            },
            {
                target: '#departmentsTable thead, table thead',
                title: 'Departments Table',
                content: 'All departments are listed with: ID (unique identifier), Code (short name), Description (full name), and Creation Date.',
                position: 'bottom'
            },
            {
                target: '#departmentsTable_filter input, .dataTables_filter input',
                title: 'Search Departments',
                content: 'Use the search box to quickly find departments by code or description. Results filter as you type.',
                position: 'left',
                optional: true
            },
            {
                target: '#departmentsTable tbody tr:first-child, table tbody tr:first-child',
                title: 'Department Entry',
                content: 'Each row represents a department. The code is used throughout the system to identify the department (e.g., in programs, subjects, and formulas).',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '.dataTables_length select, select[name*="length"]',
                title: 'Entries Per Page',
                content: 'Change how many departments are shown per page: 10, 25, 50, or 100 entries at a time.',
                position: 'left',
                optional: true
            },
            {
                target: '#departmentsTable th, table thead th',
                title: 'Sort Columns',
                content: 'Click any column header to sort the table. Click again to reverse the sort order (ascending/descending).',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register the programs tutorial
    window.AdminTutorial.registerTutorial('admin-programs', {
        title: 'Program Management',
        description: 'Learn how to add and manage academic programs (degree courses)',
        tableDataCheck: {
            selector: '#coursesTable tbody tr, table tbody tr',
            emptySelectors: ['.dataTables_empty', 'td[colspan]'],
            entityName: 'programs',
            addButtonSelector: 'button.btn-success'
        },
        steps: [
            {
                target: 'button.btn-success',
                title: 'Add Program',
                content: 'Click here to add a new academic program (e.g., BSIT, BSCS, BSN). You\'ll specify the program code, description, and select which department it belongs to.',
                position: 'left'
            },
            {
                target: '#coursesTable thead, table thead',
                title: 'Programs Table',
                content: 'View all academic programs with: ID, Code (e.g., BSIT), Description (full program name), Department association, and Creation Date.',
                position: 'bottom'
            },
            {
                target: '#coursesTable_filter input, .dataTables_filter input',
                title: 'Search Programs',
                content: 'Quickly find programs by searching for code, description, or department name. Useful when you have many programs.',
                position: 'left',
                optional: true
            },
            {
                target: '.badge.bg-light, table tbody td:nth-child(4)',
                title: 'Department Association',
                content: 'Each program belongs to exactly one department. The badge shows both the department code and full description.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#coursesTable th, table thead th',
                title: 'Sort by Column',
                content: 'Click column headers to sort programs alphabetically or by date. Sorting helps organize large program lists.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register the subjects tutorial
    window.AdminTutorial.registerTutorial('admin-subjects', {
        title: 'Course (Subject) Management',
        description: 'Learn how to add and manage academic courses/subjects',
        tableDataCheck: {
            selector: '#subjectsTable tbody tr, table tbody tr',
            emptySelectors: ['.dataTables_empty', 'td[colspan]'],
            entityName: 'courses',
            addButtonSelector: 'button[data-bs-target="#subjectModal"], button.btn-success'
        },
        steps: [
            {
                target: 'button[data-bs-target="#subjectModal"], button.btn-success',
                title: 'Add Course',
                content: 'Click to add a new course/subject. You\'ll specify: Academic Period, Department, Program, Course Code, Description, Units, and Year Level.',
                position: 'left'
            },
            {
                target: '#subjectsTable thead, table thead',
                title: 'Courses Table',
                content: 'All courses are displayed with: ID, Code (e.g., ITE 101), Description, Units, Year Level, Department, Program, and Academic Period.',
                position: 'bottom'
            },
            {
                target: '#subjectsTable_filter input, .dataTables_filter input',
                title: 'Search Courses',
                content: 'Find courses by code, description, department, or program. Essential when managing hundreds of subjects.',
                position: 'left',
                optional: true
            },
            {
                target: '#subjectsTable tbody td:nth-child(4), table tbody td:nth-child(4)',
                title: 'Course Units',
                content: 'Units determine the credit weight of each course. Typically ranges from 1-6 units depending on course intensity.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#subjectsTable tbody td:nth-child(5), table tbody td:nth-child(5)',
                title: 'Year Level',
                content: 'Indicates which year students typically take this course: 1st, 2nd, 3rd, 4th, or 5th year.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#subjectsTable tbody td:last-child, table tbody td:last-child',
                title: 'Academic Period',
                content: 'Shows which academic year and semester this course is offered. Courses can be offered in multiple periods.',
                position: 'left',
                requiresData: true
            }
        ]
    });

    // Register the academic periods tutorial
    window.AdminTutorial.registerTutorial('admin-academic-periods', {
        title: 'Academic Period Management',
        description: 'Learn how to generate and manage academic periods (semesters)',
        tableDataCheck: {
            selector: 'table tbody tr',
            emptySelectors: ['td.text-muted.fst-italic', 'td[colspan]'],
            entityName: 'academic periods',
            addButtonSelector: 'button.btn-success'
        },
        steps: [
            {
                target: 'button.btn-success',
                title: 'Generate New Period',
                content: 'Click to automatically generate the next academic period. The system calculates the next semester based on the latest existing period.',
                position: 'left'
            },
            {
                target: 'table thead',
                title: 'Periods Table',
                content: 'View all academic periods with: Academic Year (e.g., 2025-2026), Semester (1st, 2nd, or Summer), and Creation Date.',
                position: 'bottom'
            },
            {
                target: 'table tbody tr:first-child',
                title: 'Period Entry',
                content: 'Academic periods organize all academic activities by semester. Courses, grades, and enrollments are all tied to specific periods.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '#confirmModal, .modal-content',
                title: 'Generation Confirmation',
                content: 'When generating a new period, you\'ll see a confirmation dialog. The system automatically determines whether the next period is a new semester or new academic year.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
