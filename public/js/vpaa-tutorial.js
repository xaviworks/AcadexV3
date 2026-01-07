/**
 * VPAA Tutorial System
 * Provides contextual step-based guided tours for VPAA pages
 * 
 * Features:
 * - Step-based guided tours with spotlight highlighting
 * - Auto-advance on correct actions
 * - First-visit detection
 * - Accessible via header button
 * 
 * This file serves as a loader that imports the modular tutorial system.
 * Individual tutorial definitions are split into separate files in:
 * /js/vpaa-tutorials/
 * 
 * Structure:
 * - tutorial-core.js           - Core tutorial manager functionality
 * - tutorial-dashboard.js      - VPAA Dashboard tutorial
 * - tutorial-departments.js    - Departments Overview tutorial
 * - tutorial-instructors.js    - Instructor Management tutorials
 * - tutorial-students.js       - Students Management tutorials
 * - tutorial-grades.js         - Final Grades tutorials
 * - tutorial-co-attainment.js  - Course Outcome Attainment tutorials
 * - tutorial-reports.js        - CO Reports tutorials (Student, Course, Program)
 */

(function() {
    'use strict';

    /**
     * Get the base path for script loading
     */
    function getScriptBasePath() {
        const scripts = document.querySelectorAll('script[src*="vpaa-tutorial"]');
        for (const script of scripts) {
            const src = script.getAttribute('src');
            if (src && src.includes('vpaa-tutorial.js')) {
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
                console.warn('Failed to load VPAA tutorial script:', src);
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
        const tutorialsPath = basePath + 'vpaa-tutorials/';
        
        // Load core first, then all tutorial modules
        loadScript(tutorialsPath + 'tutorial-core.js')
            .then(function() {
                // Load all tutorial modules in parallel
                return Promise.all([
                    loadScript(tutorialsPath + 'tutorial-dashboard.js'),
                    loadScript(tutorialsPath + 'tutorial-departments.js'),
                    loadScript(tutorialsPath + 'tutorial-instructors.js'),
                    loadScript(tutorialsPath + 'tutorial-students.js'),
                    loadScript(tutorialsPath + 'tutorial-grades.js'),
                    loadScript(tutorialsPath + 'tutorial-co-attainment.js'),
                    loadScript(tutorialsPath + 'tutorial-reports.js')
                ]);
            })
            .then(function() {
                // Initialize the tutorial system
                if (typeof window.VPAATutorial !== 'undefined' && window.VPAATutorial.init) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', function() {
                            setTimeout(function() { window.VPAATutorial.init(); }, 500);
                        });
                    } else {
                        setTimeout(function() { window.VPAATutorial.init(); }, 500);
                    }
                }
            })
            .catch(function(error) {
                console.warn('VPAA Tutorial system failed to load:', error);
            });
    }

    // Start loading
    loadTutorialSystem();
})();
