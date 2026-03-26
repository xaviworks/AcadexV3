/**
 * Chairperson Tutorial - Outcomes Summary Reports
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Reports tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-program', {
        title: 'Outcomes Summary by Program',
        description: 'Review course outcome attainment across programs.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Program Outcomes Summary',
                content: 'This report summarizes course outcome attainment across department courses.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Program Summary Table',
                content: 'The table shows CO1 to CO6 percentages by course/program.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-course', {
        title: 'Outcomes Summary by Course - Select Course',
        description: 'Select a course to view detailed outcome results.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Course Outcomes Summary',
                content: 'Choose a course card to open detailed CO attainment by subject.',
                position: 'bottom'
            },
            {
                target: '.course-card:first-of-type, .row .course-card',
                title: 'Course Cards',
                content: 'Each card opens the selected course outcome summary details.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-course-detail', {
        title: 'Outcomes Summary by Course - Details',
        description: 'Review detailed course outcome results per subject.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Course Detail Report',
                content: 'This page shows outcome percentages for each subject in the selected course.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'Subject CO Table',
                content: 'Review CO columns and indicators to identify strengths and gaps.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-student', {
        title: 'Outcomes Summary by Student - Selection',
        description: 'Search and select a student and enrolled course.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Student Outcomes Summary',
                content: 'Search a student first, then select one enrolled course to view detailed outcomes.',
                position: 'bottom'
            },
            {
                target: '#student_query',
                title: 'Student Search',
                content: 'Type a name and select a student from suggestions or search results.',
                position: 'bottom'
            },
            {
                target: '.enrolled-course-card, .enrolled-courses-list .col-12:first-child a',
                title: 'Enrolled Courses',
                content: 'After selecting a student, choose one enrolled course to open the student outcome report.',
                position: 'top',
                optional: true
            }
        ]
    });

    window.ChairpersonTutorial.registerTutorial('chairperson-reports-co-student-detail', {
        title: 'Outcomes Summary by Student - Details',
        description: 'Review detailed student outcome performance.',
        steps: [
            {
                target: '.container-fluid h2, .container-fluid h1',
                title: 'Student Outcome Report',
                content: 'This report displays the selected student\'s course outcome percentages by term and overall.',
                position: 'bottom'
            },
            {
                target: '.table thead',
                title: 'CO Results Table',
                content: 'Compare term-by-term CO results and overall performance against configured targets.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
