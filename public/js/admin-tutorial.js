/**
 * Admin Tutorial System
 * Provides contextual step-based guided tours for admin pages
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
        
        /**
         * Tutorial definitions for each admin page
         * Each tutorial teaches one complete workflow
         */
        tutorials: {
            // Dashboard tutorial
            'admin-dashboard': {
                title: 'Admin Dashboard Overview',
                description: 'Learn how to monitor system activity and user management',
                steps: [
                    {
                        target: '.hover-lift:first-child',
                        title: 'User Statistics',
                        content: 'This card shows the total number of registered users in the system. Click on it to get more details.',
                        position: 'bottom'
                    },
                    {
                        target: '.hover-lift:nth-child(2)',
                        title: 'Login Activity',
                        content: 'Monitor successful logins for today. This helps you understand system usage patterns.',
                        position: 'bottom'
                    },
                    {
                        target: '.hover-lift:nth-child(3)',
                        title: 'Failed Attempts',
                        content: 'Keep an eye on failed login attempts. High numbers may indicate security concerns.',
                        position: 'bottom'
                    },
                    {
                        target: '.table-responsive',
                        title: 'Login Activity Table',
                        content: 'This table shows hourly login activity with success rates. Highlighted rows indicate peak hours.',
                        position: 'top'
                    },
                    {
                        target: 'select[name="year"]',
                        title: 'Year Selector',
                        content: 'Use this dropdown to view monthly login statistics for different years.',
                        position: 'left'
                    }
                ]
            },
            
            // Users management tutorial
            'admin-users': {
                title: 'User Management',
                description: 'Learn how to manage users and assign roles',
                steps: [
                    {
                        target: 'button.btn-success:contains("Add User"), button[onclick="openModal()"]',
                        title: 'Add New User',
                        content: 'Click this button to add a new administrator, instructor, or other system user.',
                        position: 'left'
                    },
                    {
                        target: '#usersTable thead',
                        title: 'User Table',
                        content: 'This table displays all users with their roles, status, and last activity. You can sort and search through users.',
                        position: 'bottom'
                    },
                    {
                        target: '#usersTable tbody tr:first-child',
                        title: 'User Row',
                        content: 'Each row shows a user\'s information including name, email, role, and status. Use the action buttons to manage each user.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.badge',
                        title: 'User Roles',
                        content: 'Role badges indicate user permissions: Admin, Chairperson, Dean, Instructor, etc. Each role has different access levels.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.alert-warning',
                        title: 'Important Notice',
                        content: 'This warning reminds you that users added here have elevated access. Be careful when granting permissions.',
                        position: 'bottom'
                    }
                ]
            },
            
            // Sessions & Activity tutorial
            'admin-sessions': {
                title: 'Session Management',
                description: 'Learn how to monitor and manage user sessions',
                steps: [
                    {
                        target: 'button.btn-danger',
                        title: 'Revoke All Sessions',
                        content: 'Use this button to terminate all active sessions except your own. This is useful for security emergencies.',
                        position: 'left'
                    },
                    {
                        target: '#sessions-tab',
                        title: 'Active Sessions Tab',
                        content: 'View all currently active user sessions. You can see device info, location, and last activity.',
                        position: 'bottom'
                    },
                    {
                        target: '#logs-tab',
                        title: 'User Logs Tab',
                        content: 'Switch to this tab to view detailed login history and user activity logs.',
                        position: 'bottom'
                    },
                    {
                        target: '.session-status-badge, .session-status-current',
                        title: 'Session Status',
                        content: 'Current session shows your active login. Active sessions are from other logged-in users.',
                        position: 'right',
                        optional: true
                    },
                    {
                        target: '.btn-revoke, button[onclick*="confirmRevoke"]',
                        title: 'Revoke Individual Session',
                        content: 'Click the revoke button to terminate a specific user\'s session. They will need to log in again.',
                        position: 'left',
                        optional: true
                    }
                ]
            },
            
            // Disaster Recovery tutorial
            'admin-disaster-recovery': {
                title: 'Disaster Recovery',
                description: 'Learn how to create and restore system backups',
                steps: [
                    {
                        target: 'button[data-bs-target="#backupModal"]',
                        title: 'Create Backup',
                        content: 'Click here to create a new system backup. You can choose between full backup or configuration-only backup.',
                        position: 'left'
                    },
                    {
                        target: 'a[href*="activity"]',
                        title: 'Activity Log',
                        content: 'View the backup activity log to see when backups were created, downloaded, or restored.',
                        position: 'left'
                    },
                    {
                        target: '.col-xl-6:first-child .card',
                        title: 'Storage Usage',
                        content: 'Monitor your backup storage usage. The progress bar shows how much space is being used.',
                        position: 'right'
                    },
                    {
                        target: '.table-responsive table thead',
                        title: 'Backup List',
                        content: 'All your backups are listed here. You can download, restore, or delete each backup.',
                        position: 'top'
                    },
                    {
                        target: '.btn-outline-warning, button[onclick*="showRestoreModal"]',
                        title: 'Restore Backup',
                        content: 'Click the restore button to revert your system to a previous state. Use with caution!',
                        position: 'left',
                        optional: true
                    }
                ]
            },
            
            // Departments tutorial
            'admin-departments': {
                title: 'Department Management',
                description: 'Learn how to manage academic departments',
                steps: [
                    {
                        target: 'button[onclick="showDepartmentModal()"]',
                        title: 'Add Department',
                        content: 'Click here to create a new academic department. You\'ll need to provide a code and description.',
                        position: 'left'
                    },
                    {
                        target: '#departmentsTable thead',
                        title: 'Departments Table',
                        content: 'All departments are listed here with their codes and descriptions. Use the search feature to find specific departments.',
                        position: 'bottom'
                    },
                    {
                        target: '#departmentsTable tbody tr:first-child',
                        title: 'Department Entry',
                        content: 'Each row shows a department with its unique code (e.g., CITE) and full description.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Programs/Courses tutorial
            'admin-courses': {
                title: 'Program Management',
                description: 'Learn how to manage academic programs',
                steps: [
                    {
                        target: 'button[onclick="showCourseModal()"]',
                        title: 'Add Program',
                        content: 'Click here to add a new academic program. Each program must be linked to a department.',
                        position: 'left'
                    },
                    {
                        target: '#coursesTable thead',
                        title: 'Programs Table',
                        content: 'View all academic programs with their codes, descriptions, and associated departments.',
                        position: 'bottom'
                    },
                    {
                        target: '#coursesTable tbody tr:first-child td:nth-child(4)',
                        title: 'Department Link',
                        content: 'Each program is associated with a department. The badge shows the department code.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Subjects/Courses tutorial
            'admin-subjects': {
                title: 'Course Management',
                description: 'Learn how to manage academic courses (subjects)',
                steps: [
                    {
                        target: 'button[data-bs-target="#subjectModal"]',
                        title: 'Add Course',
                        content: 'Click here to add a new course. You\'ll need to specify the academic period, department, program, and course details.',
                        position: 'left'
                    },
                    {
                        target: '#subjectsTable thead',
                        title: 'Courses Table',
                        content: 'All courses are listed with their codes, descriptions, units, and academic period.',
                        position: 'bottom'
                    },
                    {
                        target: '#subjectsTable thead th:nth-child(8)',
                        title: 'Academic Period',
                        content: 'Each course belongs to a specific academic period (semester and year). This helps organize courses by term.',
                        position: 'bottom'
                    }
                ]
            },
            
            // Academic Periods tutorial
            'admin-academic-periods': {
                title: 'Academic Period Management',
                description: 'Learn how to manage academic periods',
                steps: [
                    {
                        target: 'a[href*="createAcademicPeriod"], button:contains("Add")',
                        title: 'Add Academic Period',
                        content: 'Click here to create a new academic period. Define the academic year and semester.',
                        position: 'left'
                    },
                    {
                        target: 'table thead',
                        title: 'Academic Periods Table',
                        content: 'View all academic periods organized by year and semester. These are used to organize courses and grades.',
                        position: 'bottom'
                    }
                ]
            }
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
            
            // Map URL paths to tutorial IDs
            if (path.includes('/dashboard') && document.querySelector('.bi-sliders')) {
                return 'admin-dashboard';
            }
            if (path.includes('/admin/users')) {
                return 'admin-users';
            }
            if (path.includes('/admin/sessions')) {
                return 'admin-sessions';
            }
            if (path.includes('/admin/disaster-recovery') && !path.includes('/activity')) {
                return 'admin-disaster-recovery';
            }
            if (path.includes('/admin/departments')) {
                return 'admin-departments';
            }
            if (path.includes('/admin/courses') || path.includes('/admin/programs')) {
                return 'admin-courses';
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
         * Show a specific step
         */
        showStep: function(stepIndex) {
            if (!this.currentTutorial) return;
            
            const steps = this.currentTutorial.steps;
            
            // Handle optional steps that might not have targets
            let step = steps[stepIndex];
            let targetEl = this.findTarget(step.target);
            
            // If target not found and step is optional, skip to next
            while (!targetEl && step.optional && stepIndex < steps.length - 1) {
                stepIndex++;
                step = steps[stepIndex];
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
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AdminTutorial.init());
    } else {
        // Small delay to ensure all dynamic content is loaded
        setTimeout(() => AdminTutorial.init(), 500);
    }
})();
