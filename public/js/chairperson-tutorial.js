/**
 * Chairperson Tutorial System
 * Provides contextual step-based guided tours for Chairperson pages
 *
 * This file serves as a loader that imports the modular tutorial system.
 * Individual tutorial definitions are split into separate files in:
 * /js/chairperson-tutorials/
 *
 * Structure:
 * - tutorial-core.js            - Core tutorial manager functionality
 * - tutorial-dashboard.js       - Chairperson dashboard tutorial
 * - tutorial-students.js        - Students list tutorial
 * - tutorial-manage-course.js   - Manage/Import course tutorials
 * - tutorial-course-outcomes.js - Course outcomes tutorials
 * - tutorial-grades.js          - View grades tutorials
 * - tutorial-structure-template-requests.js - Formula requests tutorials
 * - tutorial-reports.js         - Outcomes summary tutorials
 * - tutorial-help-guides.js     - Help guides tutorials
 * - tutorial-users-accounts.js  - Instructor account management tutorial
 */

(function() {
    'use strict';

    function getScriptBasePath() {
        const scripts = document.querySelectorAll('script[src*="chairperson-tutorial"]');
        for (const script of scripts) {
            const src = script.getAttribute('src');
            if (src && src.includes('chairperson-tutorial.js')) {
                return src.substring(0, src.lastIndexOf('/') + 1);
            }
        }
        return '/js/';
    }

    function loadScript(src) {
        return new Promise(function(resolve, reject) {
            const script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = src;
            script.async = false;

            script.onload = function() {
                resolve();
            };

            script.onerror = function() {
                console.warn('Failed to load Chairperson tutorial script:', src);
                reject(new Error('Failed to load: ' + src));
            };

            document.head.appendChild(script);
        });
    }

    function loadTutorialSystem() {
        const basePath = getScriptBasePath();
        const tutorialsPath = basePath + 'chairperson-tutorials/';

        loadScript(tutorialsPath + 'tutorial-core.js')
            .then(function() {
                return Promise.all([
                    loadScript(tutorialsPath + 'tutorial-dashboard.js'),
                    loadScript(tutorialsPath + 'tutorial-students.js'),
                    loadScript(tutorialsPath + 'tutorial-manage-course.js'),
                    loadScript(tutorialsPath + 'tutorial-course-outcomes.js'),
                    loadScript(tutorialsPath + 'tutorial-grades.js'),
                    loadScript(tutorialsPath + 'tutorial-structure-template-requests.js'),
                    loadScript(tutorialsPath + 'tutorial-reports.js'),
                    loadScript(tutorialsPath + 'tutorial-help-guides.js'),
                    loadScript(tutorialsPath + 'tutorial-instructors-create.js'),
                    loadScript(tutorialsPath + 'tutorial-users-accounts.js')
                ]);
            })
            .then(function() {
                if (typeof window.ChairpersonTutorial !== 'undefined' && window.ChairpersonTutorial.init) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', function() {
                            setTimeout(function() { window.ChairpersonTutorial.init(); }, 500);
                        });
                    } else {
                        setTimeout(function() { window.ChairpersonTutorial.init(); }, 500);
                    }
                }
            })
            .catch(function(error) {
                console.warn('Chairperson Tutorial system failed to load:', error);
            });
    }

    loadTutorialSystem();
})();
