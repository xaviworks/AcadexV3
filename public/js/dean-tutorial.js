/**
 * Dean Tutorial System
 * Provides contextual step-based guided tours for Dean pages
 * 
 * Features:
 * - Step-based guided tours with spotlight highlighting
 * - Auto-advance on correct actions
 * - First-visit detection
 * - Accessible via header button
 * 
 * This file serves as a loader that imports the modular tutorial system.
 * Individual tutorial definitions are split into separate files in:
 * /js/dean-tutorials/
 * 
 * Structure:
 * - tutorial-core.js        - Core tutorial manager functionality
 * - tutorial-instructors.js - Instructors page tutorial
 * - tutorial-students.js    - Students page tutorial
 * - tutorial-grades.js      - Grades wizard tutorials
 * - tutorial-reports.js     - CO Reports tutorials
 */

(function() {
    'use strict';

    /**
     * Get the base path for script loading
     */
    function getScriptBasePath() {
        const scripts = document.querySelectorAll('script[src*="dean-tutorial"]');
        for (const script of scripts) {
            const src = script.getAttribute('src');
            if (src && src.includes('dean-tutorial.js')) {
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
                console.warn('Failed to load Dean tutorial script:', src);
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
        const tutorialsPath = basePath + 'dean-tutorials/';
        
        // Load core first, then all tutorial modules
        loadScript(tutorialsPath + 'tutorial-core.js')
            .then(function() {
                // Load all tutorial modules in parallel
                return Promise.all([
                    loadScript(tutorialsPath + 'tutorial-dashboard.js'),
                    loadScript(tutorialsPath + 'tutorial-instructors.js'),
                    loadScript(tutorialsPath + 'tutorial-students.js'),
                    loadScript(tutorialsPath + 'tutorial-grades.js'),
                    loadScript(tutorialsPath + 'tutorial-reports.js')
                ]);
            })
            .then(function() {
                // Initialize the tutorial system
                if (typeof window.DeanTutorial !== 'undefined' && window.DeanTutorial.init) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', function() {
                            setTimeout(function() { window.DeanTutorial.init(); }, 500);
                        });
                    } else {
                        setTimeout(function() { window.DeanTutorial.init(); }, 500);
                    }
                }
            })
            .catch(function(error) {
                console.warn('Dean Tutorial system failed to load:', error);
            });
    }

    // Start loading
    loadTutorialSystem();
})();
