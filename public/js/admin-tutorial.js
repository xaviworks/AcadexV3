/**
 * Admin Tutorial System
 * Provides contextual step-based guided tours for admin pages
 * 
 * Features:
 * - Step-based guided tours with spotlight highlighting
 * - Auto-advance on correct actions
 * - First-visit detection
 * - Accessible via header button
 * 
 * This file serves as a loader that imports the modular tutorial system.
 * Individual tutorial definitions are split into separate files in:
 * /js/admin-tutorials/
 * 
 * Structure:
 * - tutorial-core.js           - Core tutorial manager functionality
 * - tutorial-dashboard.js      - Admin Dashboard tutorial
 * - tutorial-users.js          - User Management tutorial
 * - tutorial-sessions.js       - Session & Activity Monitor tutorial
 * - tutorial-disaster-recovery.js - Disaster Recovery tutorials
 * - tutorial-academic-structure.js - Departments, Programs, Subjects, Academic Periods tutorials
 * - tutorial-grades-formula.js - All Grades Formula related tutorials
 * - tutorial-structure-template-requests.js - Structure Template Requests tutorial
 */

(function() {
    'use strict';

    /**
     * Get the base path for script loading
     */
    function getScriptBasePath() {
        const scripts = document.querySelectorAll('script[src*="admin-tutorial"]');
        for (const script of scripts) {
            const src = script.getAttribute('src');
            if (src && src.includes('admin-tutorial.js')) {
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
                console.warn('Failed to load tutorial script:', src);
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
        const tutorialsPath = basePath + 'admin-tutorials/';
        
        // Load core first, then all tutorial modules
        loadScript(tutorialsPath + 'tutorial-core.js')
            .then(function() {
                // Load all tutorial modules in parallel
                return Promise.all([
                    loadScript(tutorialsPath + 'tutorial-dashboard.js'),
                    loadScript(tutorialsPath + 'tutorial-users.js'),
                    loadScript(tutorialsPath + 'tutorial-sessions.js'),
                    loadScript(tutorialsPath + 'tutorial-disaster-recovery.js'),
                    loadScript(tutorialsPath + 'tutorial-academic-structure.js'),
                    loadScript(tutorialsPath + 'tutorial-grades-formula.js'),
                    loadScript(tutorialsPath + 'tutorial-structure-template-requests.js')
                ]);
            })
            .then(function() {
                // Initialize the tutorial system
                if (typeof window.AdminTutorial !== 'undefined' && window.AdminTutorial.init) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', function() {
                            setTimeout(function() { window.AdminTutorial.init(); }, 500);
                        });
                    } else {
                        setTimeout(function() { window.AdminTutorial.init(); }, 500);
                    }
                }
            })
            .catch(function(error) {
                console.error('Failed to load tutorial system:', error);
            });
    }

    // Start loading the tutorial system
    loadTutorialSystem();
})();
