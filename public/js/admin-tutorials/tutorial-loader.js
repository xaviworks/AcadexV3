/**
 * Admin Tutorial System - Module Loader
 * Dynamically loads tutorial modules and initializes the system
 * 
 * This file handles loading all tutorial modules in the correct order
 * and initializing the tutorial system once all modules are registered.
 */

(function() {
    'use strict';

    // Tutorial module files to load (relative to /js/admin-tutorials/)
    const TUTORIAL_MODULES = [
        'tutorial-dashboard.js',
        'tutorial-users.js',
        'tutorial-sessions.js',
        'tutorial-disaster-recovery.js',
        'tutorial-academic-structure.js',
        'tutorial-grades-formula.js',
        'tutorial-structure-template-requests.js'
    ];

    // Track loaded modules
    let loadedModules = 0;
    const totalModules = TUTORIAL_MODULES.length;

    /**
     * Get the base path for tutorial scripts
     */
    function getBasePath() {
        // Try to detect base path from current script
        const scripts = document.querySelectorAll('script[src*="admin-tutorial"]');
        for (const script of scripts) {
            const src = script.getAttribute('src');
            if (src) {
                // Extract base path (everything before the filename)
                const basePath = src.substring(0, src.lastIndexOf('/') + 1);
                // If main admin-tutorial.js, append admin-tutorials/
                if (src.includes('admin-tutorial.js') && !src.includes('admin-tutorials/')) {
                    return basePath + 'admin-tutorials/';
                }
                // If already in admin-tutorials folder
                if (src.includes('admin-tutorials/')) {
                    return basePath;
                }
            }
        }
        // Default fallback
        return '/js/admin-tutorials/';
    }

    /**
     * Load a script dynamically
     */
    function loadScript(src, callback) {
        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = src;
        script.async = false; // Load in order
        
        script.onload = function() {
            if (callback) callback(null);
        };
        
        script.onerror = function() {
            console.warn('Failed to load tutorial module:', src);
            if (callback) callback(new Error('Failed to load: ' + src));
        };
        
        document.head.appendChild(script);
    }

    /**
     * Module loaded callback
     */
    function onModuleLoaded(error) {
        loadedModules++;
        
        if (loadedModules >= totalModules) {
            // All modules loaded, initialize the tutorial system
            initializeTutorialSystem();
        }
    }

    /**
     * Initialize the tutorial system after all modules are loaded
     */
    function initializeTutorialSystem() {
        if (typeof window.AdminTutorial !== 'undefined' && window.AdminTutorial.init) {
            // Small delay to ensure DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() { window.AdminTutorial.init(); }, 500);
                });
            } else {
                setTimeout(function() { window.AdminTutorial.init(); }, 500);
            }
        } else {
            console.warn('AdminTutorial core not available after loading modules');
        }
    }

    /**
     * Load all tutorial modules
     */
    function loadAllModules() {
        const basePath = getBasePath();
        
        TUTORIAL_MODULES.forEach(function(module) {
            loadScript(basePath + module, onModuleLoaded);
        });
    }

    // Start loading modules after core is ready
    // The core should already be loaded via admin-tutorial.js
    if (typeof window.AdminTutorial !== 'undefined') {
        loadAllModules();
    } else {
        // Wait for core to load
        let checkCount = 0;
        const checkInterval = setInterval(function() {
            checkCount++;
            if (typeof window.AdminTutorial !== 'undefined') {
                clearInterval(checkInterval);
                loadAllModules();
            } else if (checkCount > 50) {
                // Timeout after 5 seconds
                clearInterval(checkInterval);
                console.warn('AdminTutorial core did not load in time');
            }
        }, 100);
    }
})();
