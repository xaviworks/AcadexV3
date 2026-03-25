/**
 * GE Coordinator Tutorial System
 * Provides contextual step-based guided tours for GE Coordinator pages.
 *
 * Structure:
 * - tutorial-core.js         - Core tutorial manager functionality
 * - tutorial-dashboard.js    - Dashboard tutorial
 * - tutorial-instructors.js  - Accounts/Users tutorial
 * - tutorial-students.js     - Students tutorial
 * - tutorial-courses.js      - Manage/Import courses tutorials
 * - tutorial-course-outcomes.js - Course outcomes tutorials
 * - tutorial-grades.js       - View grades tutorials
 * - tutorial-reports.js      - Reports tutorials
 * - tutorial-help-guides.js  - Help guides tutorials
 */

(function() {
    'use strict';

    function getScriptBasePath() {
        const scripts = document.querySelectorAll('script[src*="gecoordinator-tutorial"]');
        for (const script of scripts) {
            const src = script.getAttribute('src');
            if (src && src.includes('gecoordinator-tutorial.js')) {
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

            script.onload = function() { resolve(); };
            script.onerror = function() {
                console.warn('Failed to load GE Coordinator tutorial script:', src);
                reject(new Error('Failed to load: ' + src));
            };

            document.head.appendChild(script);
        });
    }

    function loadTutorialSystem() {
        const basePath = getScriptBasePath();
        const tutorialsPath = basePath + 'gecoordinator-tutorials/';

        loadScript(tutorialsPath + 'tutorial-core.js')
            .then(function() {
                return Promise.all([
                    loadScript(tutorialsPath + 'tutorial-dashboard.js'),
                    loadScript(tutorialsPath + 'tutorial-instructors.js'),
                    loadScript(tutorialsPath + 'tutorial-students.js'),
                    loadScript(tutorialsPath + 'tutorial-courses.js'),
                    loadScript(tutorialsPath + 'tutorial-course-outcomes.js'),
                    loadScript(tutorialsPath + 'tutorial-grades.js'),
                    loadScript(tutorialsPath + 'tutorial-reports.js'),
                    loadScript(tutorialsPath + 'tutorial-help-guides.js')
                ]);
            })
            .then(function() {
                if (typeof window.GECoordinatorTutorial !== 'undefined' && window.GECoordinatorTutorial.init) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', function() {
                            setTimeout(function() { window.GECoordinatorTutorial.init(); }, 500);
                        });
                    } else {
                        setTimeout(function() { window.GECoordinatorTutorial.init(); }, 500);
                    }
                }
            })
            .catch(function(error) {
                console.warn('GE Coordinator Tutorial system failed to load:', error);
            });
    }

    loadTutorialSystem();
})();
