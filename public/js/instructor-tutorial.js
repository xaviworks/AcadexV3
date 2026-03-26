/**
 * Instructor Tutorial System
 * Provides contextual step-based guided tours for Instructor pages
 *
 * Features:
 * - Step-based guided tours with spotlight highlighting
 * - Auto-advance on correct actions
 * - First-visit detection
 * - Accessible via header button
 *
 * This file serves as a loader that imports the modular tutorial system.
 * Individual tutorial definitions are split into separate files in:
 * /js/instructor-tutorials/
 *
 * Structure:
 * - tutorial-core.js           - Core tutorial manager functionality
 * - tutorial-dashboard.js      - Dashboard page tutorial
 * - tutorial-manage-students.js - Manage Students page tutorial
 * - tutorial-manage-grades.js  - Manage Grades page tutorial
 * - tutorial-activities.js     - Activities page tutorial
 * - tutorial-course-outcomes.js - Course Outcomes page tutorial
 * - tutorial-course-outcome-attainment.js - Course Outcome Attainment Report tutorial
 * - tutorial-scores.js         - Scores/Final Grades page tutorial
 */

(function() {
    'use strict';

    /**
     * Get the base path for script loading
     */
    function getScriptBasePath() {
        const scripts = document.querySelectorAll('script[src*="instructor-tutorial"]');
        for (const script of scripts) {
            const src = script.getAttribute('src');
            if (src && src.includes('instructor-tutorial.js')) {
                return src.substring(0, src.lastIndexOf('/') + 1);
            }
        }
        return '/js/';
    }

    /**
     * Load a script and return a promise
     */
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
                console.warn('Failed to load Instructor tutorial script:', src);
                reject(new Error('Failed to load: ' + src));
            };

            document.head.appendChild(script);
        });
    }

    /**
     * Load all tutorial modules in order
     */
    function loadTutorialSystem() {
        const basePath = getScriptBasePath();
        const tutorialsPath = basePath + 'instructor-tutorials/';

        // Load core first, then all tutorial modules
        loadScript(tutorialsPath + 'tutorial-core.js')
            .then(function() {
                // Load all tutorial modules in parallel
                return Promise.all([
                    loadScript(tutorialsPath + 'tutorial-dashboard.js'),
                    loadScript(tutorialsPath + 'tutorial-manage-students.js'),
                    loadScript(tutorialsPath + 'tutorial-manage-grades.js'),
                    loadScript(tutorialsPath + 'tutorial-activities.js'),
                    loadScript(tutorialsPath + 'tutorial-course-outcomes.js'),
                    loadScript(tutorialsPath + 'tutorial-course-outcome-attainment.js'),
                    loadScript(tutorialsPath + 'tutorial-scores.js')
                ]);
            })
            .then(function() {
                // Initialize the tutorial system
                if (typeof window.InstructorTutorial !== 'undefined' && window.InstructorTutorial.init) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', function() {
                            setTimeout(function() { window.InstructorTutorial.init(); }, 500);
                        });
                    } else {
                        setTimeout(function() { window.InstructorTutorial.init(); }, 500);
                    }
                }
            })
            .catch(function(error) {
                console.warn('Instructor Tutorial system failed to load:', error);
            });
    }

    // Start loading
    loadTutorialSystem();
})();
