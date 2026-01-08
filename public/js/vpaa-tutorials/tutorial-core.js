/**
 * VPAA Tutorial System - Core Manager
 * Provides the base tutorial manager functionality for VPAA pages
 * 
 * Features:
 * - Step-based guided tours with spotlight highlighting
 * - Auto-advance on correct actions
 * - Accessible via header button (FAB)
 */

(function() {
    'use strict';

    // Tutorial Manager
    window.VPAATutorial = {
        currentTutorial: null,
        currentStep: 0,
        overlay: null,
        spotlight: null,
        tooltip: null,
        isActive: false,
        
        // Storage key prefix for tracking completed tutorials
        STORAGE_PREFIX: 'acadex_vpaa_tutorial_',
        
        // Tutorial definitions object - populated by individual tutorial modules
        tutorials: {},
        
        /**
         * Register a tutorial from an external module
         * @param {string} id - Tutorial identifier
         * @param {object} tutorial - Tutorial definition object
         */
        registerTutorial: function(id, tutorial) {
            this.tutorials[id] = tutorial;
        },
        
        /**
         * Initialize the tutorial system
         */
        init: function() {
            this.createOverlayElements();
            this.bindEvents();
            this.createTutorialButton();
        },
        
        /**
         * Get current page identifier
         */
        getCurrentPageId: function() {
            const path = window.location.pathname;
            const urlParams = new URLSearchParams(window.location.search);
            
            // Map URL paths to tutorial IDs - Order matters (more specific first)
            
            // VPAA Dashboard
            if (path.includes('/vpaa/dashboard')) {
                return 'vpaa-dashboard';
            }
            
            // Course Outcome Attainment - Subject level
            if (path.includes('/vpaa/course-outcome-attainment/subject/')) {
                return 'vpaa-co-attainment-subject';
            }
            
            // Course Outcome Attainment - Department selection or subject list
            if (path.includes('/vpaa/course-outcome-attainment')) {
                if (urlParams.get('department_id')) {
                    return 'vpaa-co-attainment-subjects';
                }
                return 'vpaa-co-attainment';
            }
            
            // Reports - Student CO (with student selected)
            if (path.includes('/vpaa/reports/co-student')) {
                if (urlParams.get('student_id')) {
                    return 'vpaa-reports-co-student-detail';
                }
                return 'vpaa-reports-co-student';
            }
            
            // Reports - Course CO (with course selected)
            if (path.includes('/vpaa/reports/co-course')) {
                if (urlParams.get('course_id')) {
                    return 'vpaa-reports-co-course-detail';
                }
                return 'vpaa-reports-co-course';
            }
            
            // Reports - Program CO (with department selected)
            if (path.includes('/vpaa/reports/co-program')) {
                if (urlParams.get('department_id')) {
                    return 'vpaa-reports-co-program-detail';
                }
                return 'vpaa-reports-co-program';
            }
            
            // Instructors - Edit
            if (path.includes('/vpaa/instructors/') && path.includes('/edit')) {
                return 'vpaa-instructors-edit';
            }
            
            // Instructors - List (with or without department filter)
            if (path.includes('/vpaa/instructors')) {
                return 'vpaa-instructors';
            }
            
            // Students - List (with department/course filters)
            if (path.includes('/vpaa/students')) {
                if (urlParams.get('department_id')) {
                    return 'vpaa-students-list';
                }
                return 'vpaa-students';
            }
            
            // Grades
            if (path.includes('/vpaa/grades')) {
                if (urlParams.get('course_id')) {
                    return 'vpaa-grades-detail';
                }
                return 'vpaa-grades';
            }
            
            // Departments
            if (path.includes('/vpaa/departments')) {
                return 'vpaa-departments';
            }
            
            return null;
        },
        
        /**
         * Create overlay elements for the tutorial
         */
        createOverlayElements: function() {
            // Main overlay
            this.overlay = document.createElement('div');
            this.overlay.className = 'tutorial-overlay';
            this.overlay.id = 'tutorial-overlay';
            document.body.appendChild(this.overlay);
            
            // Spotlight element
            this.spotlight = document.createElement('div');
            this.spotlight.className = 'tutorial-spotlight';
            this.spotlight.id = 'tutorial-spotlight';
            document.body.appendChild(this.spotlight);
            
            // Tooltip element
            this.tooltip = document.createElement('div');
            this.tooltip.className = 'tutorial-tooltip';
            this.tooltip.id = 'tutorial-tooltip';
            this.tooltip.innerHTML = `
                <div class="tutorial-tooltip-header">
                    <span class="tutorial-step-indicator"></span>
                    <button class="tutorial-close-btn" aria-label="Close tutorial">&times;</button>
                </div>
                <h4 class="tutorial-tooltip-title"></h4>
                <p class="tutorial-tooltip-content"></p>
                <div class="tutorial-tooltip-actions">
                    <button class="tutorial-btn tutorial-btn-secondary tutorial-skip-btn">Skip Tutorial</button>
                    <div class="tutorial-nav-btns">
                        <button class="tutorial-btn tutorial-btn-secondary tutorial-prev-btn">
                            <i class="bi bi-chevron-left"></i> Previous
                        </button>
                        <button class="tutorial-btn tutorial-btn-primary tutorial-next-btn">
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(this.tooltip);
        },
        
        /**
         * Create the floating tutorial button at bottom right
         */
        createTutorialButton: function() {
            // Create floating action button
            const fab = document.createElement('button');
            fab.id = 'tutorial-fab';
            fab.className = 'tutorial-fab';
            fab.setAttribute('aria-label', 'Start Page Tutorial');
            fab.setAttribute('title', 'Page Tutorial');
            fab.innerHTML = `
                <i class="bi bi-question-lg"></i>
                <span class="tutorial-fab-tooltip">Page Tutorial</span>
            `;
            
            document.body.appendChild(fab);
            
            // Bind click event
            fab.addEventListener('click', () => {
                const pageId = this.getCurrentPageId();
                if (pageId && this.tutorials[pageId]) {
                    this.start(pageId);
                } else {
                    this.showNoTutorialMessage();
                }
            });
        },
        
        /**
         * Bind event listeners
         */
        bindEvents: function() {
            // Close button
            this.tooltip.querySelector('.tutorial-close-btn').addEventListener('click', () => this.end());
            
            // Skip button
            this.tooltip.querySelector('.tutorial-skip-btn').addEventListener('click', () => this.end());
            
            // Previous button
            this.tooltip.querySelector('.tutorial-prev-btn').addEventListener('click', () => this.prevStep());
            
            // Next button
            this.tooltip.querySelector('.tutorial-next-btn').addEventListener('click', () => this.nextStep());
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (!this.isActive) return;
                
                if (e.key === 'Escape') {
                    this.end();
                } else if (e.key === 'ArrowRight' || e.key === 'Enter') {
                    this.nextStep();
                } else if (e.key === 'ArrowLeft') {
                    this.prevStep();
                }
            });
            
            // Handle window resize
            let resizeTimeout;
            window.addEventListener('resize', () => {
                if (!this.isActive) return;
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => this.updatePosition(), 100);
            });
        },
        
        /**
         * Show message when no tutorial is available
         */
        showNoTutorialMessage: function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'No Tutorial Available',
                    text: 'There is no tutorial available for this page yet.',
                    icon: 'info',
                    confirmButtonColor: '#198754'
                });
            } else {
                alert('No tutorial available for this page.');
            }
        },
        
        /**
         * Start a tutorial
         */
        start: function(tutorialId) {
            const tutorial = this.tutorials[tutorialId];
            if (!tutorial) {
                console.warn('Tutorial not found:', tutorialId);
                return;
            }
            
            this.currentTutorial = { id: tutorialId, ...tutorial };
            this.currentStep = 0;
            this.isActive = true;
            
            // Hide FAB
            const fab = document.getElementById('tutorial-fab');
            if (fab) fab.classList.add('hidden');
            
            // Show overlay
            this.overlay.classList.add('active');
            this.tooltip.classList.add('active');
            
            // Show first step
            this.showStep(0);
        },
        
        /**
         * End the tutorial
         */
        end: function() {
            this.isActive = false;
            this.overlay.classList.remove('active');
            this.tooltip.classList.remove('active');
            this.spotlight.classList.remove('active');
            
            // Show FAB again
            const fab = document.getElementById('tutorial-fab');
            if (fab) fab.classList.remove('hidden');
            
            // Mark as completed
            if (this.currentTutorial) {
                this.markTutorialCompleted(this.currentTutorial.id);
            }
            
            this.currentTutorial = null;
            this.currentStep = 0;
            
            // Remove any highlighted elements
            document.querySelectorAll('.tutorial-highlight').forEach(el => {
                el.classList.remove('tutorial-highlight');
            });
            
            // Remove modal elevation classes
            document.querySelectorAll('.tutorial-active-modal').forEach(m => {
                m.classList.remove('tutorial-active-modal');
            });
        },
        
        /**
         * Check if a table has data based on tutorial's tableDataCheck config
         */
        hasTableData: function() {
            if (!this.currentTutorial || !this.currentTutorial.tableDataCheck) {
                return true; // No check configured, assume data exists
            }
            
            const check = this.currentTutorial.tableDataCheck;
            
            // First check if empty state indicators exist
            if (check.emptySelectors) {
                for (const emptySelector of check.emptySelectors) {
                    const emptyEl = document.querySelector(emptySelector);
                    if (emptyEl && this.isVisible(emptyEl)) {
                        return false; // Empty state found
                    }
                }
            }
            
            // Check for actual data rows
            if (check.selector) {
                const rows = document.querySelectorAll(check.selector);
                // Filter out empty state rows (rows with colspan or dataTables_empty class)
                const dataRows = Array.from(rows).filter(row => {
                    // Skip if row contains empty state cell
                    const emptyCell = row.querySelector('td[colspan], .dataTables_empty');
                    if (emptyCell) return false;
                    // Skip if row is the "No data" message
                    if (row.textContent.toLowerCase().includes('no ') && 
                        (row.textContent.toLowerCase().includes('found') || 
                         row.textContent.toLowerCase().includes('available'))) {
                        return false;
                    }
                    return true;
                });
                return dataRows.length > 0;
            }
            
            return true;
        },
        
        /**
         * Show a "No Data" step when table is empty
         */
        showNoDataStep: function(step) {
            const check = this.currentTutorial.tableDataCheck;
            const entityName = check.entityName || 'records';
            
            // Find the add button or use table header as target
            let targetEl = null;
            if (check.addButtonSelector) {
                targetEl = this.findTarget(check.addButtonSelector);
            }
            if (!targetEl) {
                targetEl = this.findTarget('table thead, .card-header, .container-fluid h1');
            }
            
            if (!targetEl) {
                // Fallback to body if nothing found
                this.showCompletion();
                return;
            }
            
            // Update UI for no-data state
            this.updateStepIndicator();
            this.updateButtons();
            this.highlightElement(targetEl, { position: 'bottom' });
            this.positionTooltip(targetEl, { position: 'bottom' });
            
            // Show no-data message
            const title = `No ${entityName.charAt(0).toUpperCase() + entityName.slice(1)} Found`;
            let content = `The table is currently empty. `;
            
            if (check.noAddButton) {
                content += `There are no ${entityName} to display yet. ${entityName.charAt(0).toUpperCase() + entityName.slice(1)} will appear here when data is available.`;
            } else {
                content += `To see more features of this page, please add some ${entityName} first, then restart this tutorial.`;
            }
            
            this.tooltip.querySelector('.tutorial-tooltip-title').textContent = title;
            this.tooltip.querySelector('.tutorial-tooltip-content').textContent = content;
            
            // Scroll element into view
            this.scrollIntoView(targetEl);
        },
        
        /**
         * Show a specific step
         */
        showStep: function(stepIndex) {
            if (!this.currentTutorial) return;
            
            const steps = this.currentTutorial.steps;
            
            // Handle optional steps that might not have targets
            let step = steps[stepIndex];
            let targetEl = this.findTarget(step.target);
            
            // Check if step requires data and table is empty
            if (step.requiresData && !this.hasTableData()) {
                this.showNoDataStep(step);
                return;
            }
            
            // If target not found and step is optional or requires data (and no data), skip to next
            while (!targetEl && (step.optional || step.requiresData) && stepIndex < steps.length - 1) {
                stepIndex++;
                step = steps[stepIndex];
                
                // Check again for requiresData on the new step
                if (step.requiresData && !this.hasTableData()) {
                    this.showNoDataStep(step);
                    return;
                }
                
                targetEl = this.findTarget(step.target);
            }
            
            // If still no target, try next non-optional or show completion
            if (!targetEl) {
                if (stepIndex < steps.length - 1) {
                    this.showStep(stepIndex + 1);
                    return;
                } else {
                    this.showCompletion();
                    return;
                }
            }
            
            this.currentStep = stepIndex;
            
            // Update UI
            this.updateStepIndicator();
            this.updateButtons();
            this.highlightElement(targetEl, step);
            this.positionTooltip(targetEl, step);
            
            // Update content
            this.tooltip.querySelector('.tutorial-tooltip-title').textContent = step.title;
            this.tooltip.querySelector('.tutorial-tooltip-content').textContent = step.content;
            
            // Scroll element into view
            this.scrollIntoView(targetEl);
        },
        
        /**
         * Find target element using selector
         */
        findTarget: function(selector) {
            if (!selector) return null;
            
            // Handle jQuery-like :contains selector
            if (selector.includes(':contains')) {
                const match = selector.match(/(.+):contains\("(.+)"\)/);
                if (match) {
                    const baseSelector = match[1];
                    const text = match[2];
                    const elements = document.querySelectorAll(baseSelector);
                    for (const el of elements) {
                        if (el.textContent.includes(text)) {
                            return el;
                        }
                    }
                }
            }
            
            // Handle multiple selectors (comma-separated)
            const selectors = selector.split(',').map(s => s.trim());
            for (const sel of selectors) {
                try {
                    const el = document.querySelector(sel);
                    if (el && this.isVisible(el)) {
                        return el;
                    }
                } catch (e) {
                    // Invalid selector, continue
                }
            }
            
            return null;
        },
        
        /**
         * Check if element is visible
         */
        isVisible: function(el) {
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            const style = window.getComputedStyle(el);
            return rect.width > 0 && 
                   rect.height > 0 && 
                   style.visibility !== 'hidden' && 
                   style.display !== 'none';
        },
        
        /**
         * Highlight the target element
         */
        highlightElement: function(el, step) {
            // Remove previous highlights
            document.querySelectorAll('.tutorial-highlight').forEach(e => {
                e.classList.remove('tutorial-highlight');
            });
            
            // Remove previous modal elevation classes
            document.querySelectorAll('.tutorial-active-modal').forEach(m => {
                m.classList.remove('tutorial-active-modal');
            });
            
            // Add highlight to current element
            el.classList.add('tutorial-highlight');
            
            // Check if element is inside a modal and elevate the modal
            const modal = el.closest('.modal');
            if (modal && modal.classList.contains('show')) {
                modal.classList.add('tutorial-active-modal');
            }
            
            // Position spotlight
            const rect = el.getBoundingClientRect();
            const padding = 8;
            
            this.spotlight.style.top = (rect.top + window.scrollY - padding) + 'px';
            this.spotlight.style.left = (rect.left + window.scrollX - padding) + 'px';
            this.spotlight.style.width = (rect.width + padding * 2) + 'px';
            this.spotlight.style.height = (rect.height + padding * 2) + 'px';
            this.spotlight.classList.add('active');
        },
        
        /**
         * Position the tooltip relative to target
         */
        positionTooltip: function(el, step) {
            const rect = el.getBoundingClientRect();
            const tooltipRect = this.tooltip.getBoundingClientRect();
            const position = step.position || 'bottom';
            const spacing = 16;
            
            let top, left;
            let actualPosition = position;
            
            // Calculate initial position based on preferred position
            switch (position) {
                case 'top':
                    top = rect.top + window.scrollY - tooltipRect.height - spacing;
                    left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                    break;
                case 'bottom':
                    top = rect.bottom + window.scrollY + spacing;
                    left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                    break;
                case 'left':
                    top = rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                    left = rect.left + window.scrollX - tooltipRect.width - spacing;
                    break;
                case 'right':
                    top = rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                    left = rect.right + window.scrollX + spacing;
                    break;
            }
            
            // Ensure tooltip stays within viewport
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const scrollY = window.scrollY;
            
            // Horizontal bounds
            if (left < 10) left = 10;
            if (left + tooltipRect.width > viewportWidth - 10) {
                left = viewportWidth - tooltipRect.width - 10;
            }
            
            // Vertical bounds - check if tooltip goes above viewport
            if (top < scrollY + 10) {
                // Try positioning below the element instead
                const bottomPosition = rect.bottom + window.scrollY + spacing;
                if (bottomPosition + tooltipRect.height <= scrollY + viewportHeight - 10) {
                    top = bottomPosition;
                    actualPosition = 'bottom';
                } else {
                    // Force it to stay at top of viewport with some padding
                    top = scrollY + 10;
                    actualPosition = 'bottom';
                }
            }
            
            // Check if tooltip goes below viewport
            if (top + tooltipRect.height > scrollY + viewportHeight - 10) {
                // Try positioning above the element instead
                const topPosition = rect.top + window.scrollY - tooltipRect.height - spacing;
                if (topPosition >= scrollY + 10) {
                    top = topPosition;
                    actualPosition = 'top';
                } else {
                    // Force it to fit within viewport
                    top = scrollY + viewportHeight - tooltipRect.height - 10;
                    actualPosition = 'top';
                }
            }
            
            this.tooltip.style.top = top + 'px';
            this.tooltip.style.left = left + 'px';
            
            // Set position class for arrow based on actual position
            this.tooltip.className = 'tutorial-tooltip active tutorial-tooltip-' + actualPosition;
        },
        
        /**
         * Scroll element into view smoothly
         */
        scrollIntoView: function(el) {
            const rect = el.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const tooltipHeight = this.tooltip ? this.tooltip.getBoundingClientRect().height : 150;
            
            // Check if element is inside a modal
            const modal = el.closest('.modal');
            if (modal) {
                const modalBody = modal.querySelector('.modal-body');
                if (modalBody) {
                    // Scroll within the modal to make element visible
                    const modalRect = modalBody.getBoundingClientRect();
                    const elRelativeTop = rect.top - modalRect.top;
                    
                    // If element is above or below visible area in modal, scroll modal
                    if (rect.top < modalRect.top + 50 || rect.bottom > modalRect.bottom - 50) {
                        // Scroll the modal body to show the element with some padding for tooltip
                        const scrollTarget = modalBody.scrollTop + elRelativeTop - (modalRect.height / 3);
                        modalBody.scrollTo({
                            top: Math.max(0, scrollTarget),
                            behavior: 'smooth'
                        });
                    }
                }
            }
            
            // Also handle page-level scrolling if needed
            // Account for tooltip space (either above or below)
            if (rect.top < 100 + tooltipHeight || rect.bottom > viewportHeight - 100) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        },
        
        /**
         * Update step indicator
         */
        updateStepIndicator: function() {
            const indicator = this.tooltip.querySelector('.tutorial-step-indicator');
            const total = this.currentTutorial.steps.length;
            indicator.textContent = `Step ${this.currentStep + 1} of ${total}`;
        },
        
        /**
         * Update navigation buttons
         */
        updateButtons: function() {
            const prevBtn = this.tooltip.querySelector('.tutorial-prev-btn');
            const nextBtn = this.tooltip.querySelector('.tutorial-next-btn');
            const total = this.currentTutorial.steps.length;
            
            prevBtn.style.display = this.currentStep === 0 ? 'none' : 'inline-flex';
            
            if (this.currentStep === total - 1) {
                nextBtn.innerHTML = 'Finish <i class="bi bi-check-lg"></i>';
            } else {
                nextBtn.innerHTML = 'Next <i class="bi bi-chevron-right"></i>';
            }
        },
        
        /**
         * Go to next step
         */
        nextStep: function() {
            if (!this.currentTutorial) return;
            
            if (this.currentStep < this.currentTutorial.steps.length - 1) {
                this.showStep(this.currentStep + 1);
            } else {
                this.showCompletion();
            }
        },
        
        /**
         * Go to previous step
         */
        prevStep: function() {
            if (this.currentStep > 0) {
                this.showStep(this.currentStep - 1);
            }
        },
        
        /**
         * Show completion message
         */
        showCompletion: function() {
            this.end();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Tutorial Complete!',
                    text: 'You\'ve completed the tutorial. You can restart it anytime using the help button in the header.',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Got it!'
                });
            }
        },
        
        /**
         * Update position on resize
         */
        updatePosition: function() {
            if (!this.isActive || !this.currentTutorial) return;
            this.showStep(this.currentStep);
        },
        
        /**
         * Check if tutorial was completed
         */
        hasCompletedTutorial: function(tutorialId) {
            return localStorage.getItem(this.STORAGE_PREFIX + tutorialId + '_completed') === 'true';
        },
        
        /**
         * Mark tutorial as completed
         */
        markTutorialCompleted: function(tutorialId) {
            localStorage.setItem(this.STORAGE_PREFIX + tutorialId + '_completed', 'true');
        },
        
        /**
         * Reset all tutorial progress (for testing)
         */
        resetAllProgress: function() {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith(this.STORAGE_PREFIX)) {
                    localStorage.removeItem(key);
                }
            });
            console.log('VPAA Tutorial progress reset');
        },
        
        /**
         * Reset specific tutorial progress
         */
        resetProgress: function(tutorialId) {
            localStorage.removeItem(this.STORAGE_PREFIX + tutorialId + '_completed');
            console.log('VPAA Tutorial progress reset for:', tutorialId);
        }
    };
})();
