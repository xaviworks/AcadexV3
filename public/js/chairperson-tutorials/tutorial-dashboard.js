/**
 * Chairperson Tutorial - Dashboard
 * Tutorial for the Chairperson Dashboard page
 */

(function() {
    'use strict';

    // Wait for ChairpersonTutorial to be available
    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Dashboard tutorial registration deferred.');
        return;
    }

    // Register the dashboard tutorial
    window.ChairpersonTutorial.registerTutorial('chairperson-dashboard', {
        title: 'Chairperson Dashboard Overview',
        description: 'Learn how to monitor your department performance and faculty management',
        steps: [
            {
                target: 'h2.fw-bold, .container-fluid h2',
                title: 'Course Chair Control Panel',
                content: 'Welcome to the Chairperson Dashboard! This is your central hub for managing department faculty, monitoring student enrollment, and overseeing course offerings.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-4:nth-child(1) .card',
                title: 'Total Instructors',
                content: 'This card shows the total number of faculty members in your department. This includes all active instructors teaching courses under your program.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-4:nth-child(2) .card',
                title: 'Total Students Enrolled',
                content: 'View the total number of students enrolled in your department this semester. This helps you track program reach and student engagement.',
                position: 'bottom'
            },
            {
                target: '.row.g-4 > .col-md-4:nth-child(3) .card',
                title: 'Active Courses',
                content: 'Shows the number of course offerings currently active. These are the subjects being taught this academic period in your department.',
                position: 'bottom'
            },
            {
                target: '.col-lg-8 .card',
                title: 'Faculty Status Overview Panel',
                content: 'This panel provides a comprehensive view of your faculty status distribution. It shows how many faculty members are active, inactive, or pending verification with visual indicators and percentages.',
                position: 'bottom'
            },
            {
                target: '.col-lg-8 .progress, .col-lg-8 .progress-bar',
                title: 'Faculty Distribution Bar',
                content: 'Visual progress bar showing the distribution of faculty members: Green for Active (currently teaching), Red for Inactive (on leave/deactivated), and Yellow for Pending (awaiting approval). Hover over each segment for exact counts.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-lg-8 .row.g-4 .col-md-4:nth-child(1) .card',
                title: 'Active Faculty Card',
                content: 'Shows the number and percentage of currently active faculty members who are teaching courses. Click this card to view the full list of active instructors.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-lg-8 .row.g-4 .col-md-4:nth-child(2) .card',
                title: 'Inactive Faculty Card',
                content: 'Displays faculty members who are on leave or have been deactivated. These instructors are not currently teaching but remain in the system. Click to manage inactive accounts.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-lg-8 .row.g-4 .col-md-4:nth-child(3) .card',
                title: 'Pending Verification Card',
                content: 'Shows faculty members awaiting your approval. These are instructors who have registered but need verification before they can access the system. Click to review pending accounts.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.col-lg-4 .card',
                title: 'Quick Actions Panel',
                content: 'This panel provides shortcuts to your most common tasks as a Chairperson. Use these buttons to quickly access key management features.',
                position: 'left'
            },
            {
                target: '.col-lg-4 .card a.btn[href*="assign-subjects"]',
                title: 'Assign Courses Button',
                content: 'Click here to assign courses to faculty members. This is where you manage faculty teaching loads and subject assignments for your department.',
                position: 'left'
            },
            {
                target: '.col-lg-4 .card a.btn[href*="students-by-year"]',
                title: 'Student List',
                content: 'View all students enrolled in your department, organized by year level. This helps you monitor student distribution across year levels.',
                position: 'left'
            },
            {
                target: '.col-lg-4 .card a.btn[href*="/grades"]',
                title: 'View Grades',
                content: 'Access grades and monitor student performance across all courses in your department. Use this to track academic progress and identify areas needing attention.',
                position: 'left'
            }
        ]
    });
})();
