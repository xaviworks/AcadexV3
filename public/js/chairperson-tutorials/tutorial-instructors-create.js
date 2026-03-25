/**
 * Chairperson Tutorial - Create Instructor Account
 */

(function() {
    'use strict';

    if (typeof window.ChairpersonTutorial === 'undefined') {
        console.warn('ChairpersonTutorial core not loaded. Create instructor tutorial registration deferred.');
        return;
    }

    window.ChairpersonTutorial.registerTutorial('chairperson-instructors-create', {
        title: 'Create Instructor Account',
        description: 'Fill out and submit a new instructor account request.',
        steps: [
            {
                target: '.container-fluid h1.text-2xl, .container-fluid h1',
                title: 'Add New Instructor',
                content: 'Use this page to create a new instructor account for your department.',
                position: 'bottom'
            },
            {
                target: 'form[action*="/chairperson/instructors/store"]',
                title: 'Account Form',
                content: 'Enter instructor identity, department/course assignment, and credentials here.',
                position: 'bottom'
            },
            {
                target: 'button[type="submit"]',
                title: 'Submit for Approval',
                content: 'Submit to send the account for approval workflow.',
                position: 'top',
                optional: true
            }
        ]
    });
})();
