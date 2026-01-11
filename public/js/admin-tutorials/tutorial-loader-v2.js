/**
 * Dynamic Tutorial Loader V2
 * Extends the core tutorial manager to load tutorials from API
 * Falls back to static tutorials if API fails
 */

(function() {
    'use strict';

    // Extend each tutorial manager (Admin, Dean, VPAA)
    const managers = ['AdminTutorial', 'DeanTutorial', 'VPAATutorial'];

    managers.forEach(managerName => {
        if (typeof window[managerName] === 'undefined') {
            console.warn(`${managerName} not found, skipping dynamic loader extension`);
            return;
        }

        const manager = window[managerName];
        const roleMap = {
            'AdminTutorial': 'admin',
            'DeanTutorial': 'dean',
            'VPAATutorial': 'vpaa'
        };
        const role = roleMap[managerName];

        // Store original start method
        manager._originalStart = manager.start;

        // Cache for loaded tutorials
        manager._tutorialCache = {};
        manager._cacheTimestamp = null;
        manager._cacheDuration = 5 * 60 * 1000; // 5 minutes

        /**
         * Load tutorial from API with caching and fallback
         */
        manager.loadTutorial = async function(tutorialId) {
            // Check cache first
            const now = Date.now();
            if (this._tutorialCache[tutorialId] && 
                this._cacheTimestamp && 
                (now - this._cacheTimestamp < this._cacheDuration)) {
                console.log(`[Tutorial] Using cached tutorial: ${tutorialId}`);
                return this._tutorialCache[tutorialId];
            }

            // Try API fetch
            try {
                console.log(`[Tutorial] Fetching from API: ${role}/${tutorialId}`);
                const response = await fetch(`/api/tutorials/${role}/${tutorialId}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.tutorial) {
                        // Cache the tutorial
                        this._tutorialCache[tutorialId] = data.tutorial;
                        this._cacheTimestamp = now;
                        console.log(`[Tutorial] Loaded from API: ${tutorialId}`);
                        return data.tutorial;
                    }
                }
                
                console.warn(`[Tutorial] API returned no tutorial for: ${tutorialId}`);
            } catch (error) {
                console.warn(`[Tutorial] API fetch failed for ${tutorialId}:`, error.message);
            }

            // Fallback to static registered tutorials
            if (this.tutorials[tutorialId]) {
                console.log(`[Tutorial] Using static fallback: ${tutorialId}`);
                return this.tutorials[tutorialId];
            }

            console.error(`[Tutorial] No tutorial found (API or static): ${tutorialId}`);
            return null;
        };

        /**
         * Enhanced start method with API loading
         */
        manager.start = async function(tutorialId) {
            const tutorial = await this.loadTutorial(tutorialId);
            
            if (!tutorial) {
                console.warn('Tutorial not found:', tutorialId);
                this.showNoTutorialMessage();
                return;
            }

            // Set current tutorial
            this.currentTutorial = { id: tutorialId, ...tutorial };
            this.currentStep = 0;

            // Check if tutorial requires data and if data exists (for Dean tutorials)
            if (this.hasTableData && tutorial.tableDataCheck && !this.hasTableData()) {
                const check = tutorial.tableDataCheck;
                const entityName = check.entityName || 'records';
                
                // Show alert that tutorial cannot proceed
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `No ${entityName.charAt(0).toUpperCase() + entityName.slice(1)} Available`,
                        html: check.noAddButton 
                            ? `This page currently has no ${entityName} to display.<br><br>The tutorial cannot proceed without data. ${entityName.charAt(0).toUpperCase() + entityName.slice(1)} will appear here when available.`
                            : `This page currently has no ${entityName} to display.<br><br>Please add some ${entityName} first, then try the tutorial again.`,
                        icon: 'info',
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(`Cannot start tutorial: No ${entityName} available on this page.`);
                }
                
                this.currentTutorial = null;
                return;
            }

            this.isActive = true;

            // Hide FAB
            const fab = document.getElementById('tutorial-fab');
            if (fab) fab.classList.add('hidden');

            // Show overlay
            this.overlay.classList.add('active');
            this.tooltip.classList.add('active');

            // Show first step
            this.showStep(0);
        };

        /**
         * Preload all tutorials for current role
         */
        manager.preloadTutorials = async function() {
            try {
                console.log(`[Tutorial] Preloading tutorials for role: ${role}`);
                const response = await fetch(`/api/tutorials/${role}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.tutorials) {
                        data.tutorials.forEach(tutorial => {
                            this._tutorialCache[tutorial.id] = tutorial;
                        });
                        this._cacheTimestamp = Date.now();
                        console.log(`[Tutorial] Preloaded ${data.tutorials.length} tutorials`);
                    }
                }
            } catch (error) {
                console.warn(`[Tutorial] Preload failed:`, error.message);
            }
        };

        /**
         * Clear tutorial cache
         */
        manager.clearCache = function() {
            this._tutorialCache = {};
            this._cacheTimestamp = null;
            console.log('[Tutorial] Cache cleared');
        };

        // Preload tutorials on page load (async, non-blocking)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => manager.preloadTutorials(), 1000);
            });
        } else {
            setTimeout(() => manager.preloadTutorials(), 1000);
        }

        console.log(`[Tutorial] Dynamic loader initialized for ${managerName}`);
    });
})();
