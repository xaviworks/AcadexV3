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
     * Resolve cache-busting version from current loader script src
     */
    function getScriptVersion() {
        try {
            const scripts = document.querySelectorAll('script[src*="instructor-tutorial.js"]');
            for (const script of scripts) {
                const src = script.getAttribute('src');
                if (!src) continue;
                const url = new URL(src, window.location.origin);
                const version = url.searchParams.get('v');
                if (version) return version;
            }
        } catch (e) {
            // Fallback below
        }

        // Fallback ensures module updates are still fetched when no version query is present
        return String(Date.now());
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
        const version = getScriptVersion() + '-' + Date.now();
        const withVersion = function(fileName) {
            return tutorialsPath + fileName + '?v=' + encodeURIComponent(version);
        };

        // Load core first, then all tutorial modules
        loadScript(withVersion('tutorial-core.js'))
            .then(function() {
                // Load all tutorial modules in parallel
                return Promise.all([
                    loadScript(withVersion('tutorial-dashboard.js')),
                    loadScript(withVersion('tutorial-manage-students.js')),
                    loadScript(withVersion('tutorial-manage-grades.js')),
                    loadScript(withVersion('tutorial-activities.js')),
                    loadScript(withVersion('tutorial-course-outcomes.js')),
                    loadScript(withVersion('tutorial-course-outcome-attainment.js')),
                    loadScript(withVersion('tutorial-scores.js'))
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
