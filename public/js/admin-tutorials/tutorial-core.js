/**
 * Admin Tutorial System - Core Manager
 * Provides the base tutorial manager functionality
 * 
 * Features:
 * - Step-based guided tours with spotlight highlighting
 * - Auto-advance on correct actions
 * - First-visit detection
 * - Accessible via header button
 */

(function() {
    'use strict';

    // Tutorial Manager
    window.AdminTutorial = {
        currentTutorial: null,
        currentStep: 0,
        overlay: null,
        spotlight: null,
        tooltip: null,
        isActive: false,
        
        // Storage key prefix for tracking completed tutorials
        STORAGE_PREFIX: 'acadex_admin_tutorial_',
        
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
            
            // Check for first-time visit to current page
            const pageId = this.getCurrentPageId();
            if (pageId && this.tutorials[pageId] && !this.hasCompletedTutorial(pageId)) {
                // Add pulse animation to FAB to draw attention
                const fab = document.getElementById('tutorial-fab');
                if (fab) {
                    fab.classList.add('pulse');
                    // Remove pulse after animation completes
                    setTimeout(() => fab.classList.remove('pulse'), 6000);
                }
                
                // Small delay to let page render
                setTimeout(() => {
                    this.promptTutorial(pageId);
                }, 1000);
            }
        },
        
        /**
         * Get current page identifier
         */
        getCurrentPageId: function() {
            const path = window.location.pathname;
            
            // Map URL paths to tutorial IDs - Order matters (more specific first)
            
            // Admin Dashboard
            if (path.includes('/dashboard') && document.querySelector('.bi-sliders')) {
                return 'admin-dashboard';
            }
            
            // User Management
            if (path.includes('/admin/users')) {
                return 'admin-users';
            }
            
            // Session Management
            if (path.includes('/admin/sessions')) {
                return 'admin-sessions';
            }
            
            // Disaster Recovery - Activity Log (more specific, check first)
            if (path.includes('/admin/disaster-recovery/activity')) {
                return 'admin-disaster-recovery-activity';
            }
            
            // Disaster Recovery - Main
            if (path.includes('/admin/disaster-recovery')) {
                return 'admin-disaster-recovery';
            }
            
            // Structure Template Requests
            if (path.includes('/admin/structure-template-requests')) {
                return 'admin-structure-template-requests';
            }
            
            // Grades Formula - Edit forms (most specific first)
            if (path.includes('/grades-formula') && path.includes('/edit')) {
                return 'admin-grades-formula-edit';
            }
            
            // Grades Formula - Subject level
            if (path.includes('/grades-formula/subject/')) {
                return 'admin-grades-formula-subject';
            }
            
            // Grades Formula - Course level
            if (path.includes('/grades-formula/department/') && path.includes('/course/')) {
                return 'admin-grades-formula-course';
            }
            
            // Grades Formula - Department level
            if (path.includes('/grades-formula/department/')) {
                return 'admin-grades-formula-department';
            }
            
            // Grades Formula - Select Period
            if (path.includes('/grades-formula') && document.querySelector('#academic-period-select')) {
                return 'admin-grades-formula-select';
            }
            
            // Grades Formula - Formulas section (check for view=formulas or active formulas tab)
            if (path.includes('/admin/grades-formula')) {
                const urlParams = new URLSearchParams(window.location.search);
                const viewParam = urlParams.get('view');
                const formulasSection = document.querySelector('[data-section="formulas"]:not(.d-none)');
                const formulasTabActive = document.querySelector('.wildcard-section-btn[data-section-target="formulas"].active');
                
                if (viewParam === 'formulas' || formulasSection || formulasTabActive) {
                    return 'admin-grades-formula-formulas';
                }
                return 'admin-grades-formula';
            }
            
            // Academic Structure
            if (path.includes('/admin/departments')) {
                return 'admin-departments';
            }
            
            if (path.includes('/admin/courses') || path.includes('/admin/programs')) {
                return 'admin-programs';
            }
            
            if (path.includes('/admin/subjects')) {
                return 'admin-subjects';
            }
            
            if (path.includes('/admin/academic-periods')) {
                return 'admin-academic-periods';
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
         * Prompt user to start tutorial (first visit)
         */
        promptTutorial: function(pageId) {
            const tutorial = this.tutorials[pageId];
            if (!tutorial) return;
            
            // Use SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '👋 Welcome!',
                    html: `
                        <div class="text-start">
                            <h5 class="mb-2">${tutorial.title}</h5>
                            <p class="text-muted">${tutorial.description}</p>
                            <p class="mb-0"><small>Would you like a quick tour of this page?</small></p>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Start Tutorial',
                    cancelButtonText: 'Maybe Later'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.start(pageId);
                    } else {
                        // Mark as seen but not completed
                        this.markTutorialSeen(pageId);
                    }
                });
            } else {
                // Fallback to confirm dialog
                if (confirm(`Welcome! Would you like a quick tour of ${tutorial.title}?`)) {
                    this.start(pageId);
                } else {
                    this.markTutorialSeen(pageId);
                }
            }
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
                content += `There are no ${entityName} to display yet. ${entityName.charAt(0).toUpperCase() + entityName.slice(1)} will appear here when chairpersons submit formula requests.`;
            } else {
                content += `To see more features of this page, please add some ${entityName} first using the "Add" button, then restart this tutorial.`;
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
            
            // Add highlight to current element
            el.classList.add('tutorial-highlight');
            
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
            
            if (left < 10) left = 10;
            if (left + tooltipRect.width > viewportWidth - 10) {
                left = viewportWidth - tooltipRect.width - 10;
            }
            if (top < window.scrollY + 10) {
                top = rect.bottom + window.scrollY + spacing;
            }
            if (top + tooltipRect.height > window.scrollY + viewportHeight - 10) {
                top = rect.top + window.scrollY - tooltipRect.height - spacing;
            }
            
            this.tooltip.style.top = top + 'px';
            this.tooltip.style.left = left + 'px';
            
            // Set position class for arrow
            this.tooltip.className = 'tutorial-tooltip active tutorial-tooltip-' + position;
        },
        
        /**
         * Scroll element into view smoothly
         */
        scrollIntoView: function(el) {
            const rect = el.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            
            if (rect.top < 100 || rect.bottom > viewportHeight - 100) {
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
                    title: '🎉 Tutorial Complete!',
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
            localStorage.setItem(this.STORAGE_PREFIX + tutorialId + '_seen', 'true');
        },
        
        /**
         * Mark tutorial as seen (but not completed)
         */
        markTutorialSeen: function(tutorialId) {
            localStorage.setItem(this.STORAGE_PREFIX + tutorialId + '_seen', 'true');
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
            console.log('Tutorial progress reset');
        },
        
        /**
         * Reset specific tutorial progress
         */
        resetProgress: function(tutorialId) {
            localStorage.removeItem(this.STORAGE_PREFIX + tutorialId + '_completed');
            localStorage.removeItem(this.STORAGE_PREFIX + tutorialId + '_seen');
            console.log('Tutorial progress reset for:', tutorialId);
        }
    };
})();
