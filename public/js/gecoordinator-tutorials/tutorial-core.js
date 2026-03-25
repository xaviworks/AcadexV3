/**
 * GE Coordinator Tutorial System - Core Manager
 */

(function() {
    'use strict';

    window.GECoordinatorTutorial = {
        currentTutorial: null,
        currentStep: 0,
        overlay: null,
        spotlight: null,
        tooltip: null,
        isActive: false,

        STORAGE_PREFIX: 'acadex_gecoordinator_tutorial_',
        tutorials: {},

        registerTutorial: function(id, tutorial) {
            this.tutorials[id] = tutorial;
        },

        init: function() {
            this.createOverlayElements();
            this.bindEvents();
            this.createTutorialButton();
        },

        getCurrentPageId: function() {
            const path = window.location.pathname;
            const urlParams = new URLSearchParams(window.location.search);

            if (path.includes('/gecoordinator/reports/co-student')) {
                if (urlParams.get('student_id') && urlParams.get('subject_id')) {
                    return 'gecoordinator-reports-co-student-detail';
                }
                return 'gecoordinator-reports-co-student';
            }

            if (path.includes('/gecoordinator/reports/co-course')) {
                if (urlParams.get('course_id')) {
                    return 'gecoordinator-reports-co-course-detail';
                }
                return 'gecoordinator-reports-co-course';
            }

            if (path.includes('/gecoordinator/reports/co-program')) {
                return 'gecoordinator-reports-co-program';
            }

            if (path.includes('/gecoordinator/reports')) {
                return 'gecoordinator-reports-overview';
            }

            if (path.includes('/gecoordinator/grades')) {
                if (urlParams.get('subject_id')) {
                    return 'gecoordinator-grades-students';
                }
                if (urlParams.get('instructor_id')) {
                    return 'gecoordinator-grades-subjects';
                }
                return 'gecoordinator-grades';
            }

            if (path.includes('/gecoordinator/course_outcomes')) {
                if (urlParams.get('subject_id')) {
                    return 'gecoordinator-course-outcomes-table';
                }
                return 'gecoordinator-course-outcomes';
            }

            if (path.includes('/gecoordinator/students-by-year')) {
                if (urlParams.get('subject_id')) {
                    return 'gecoordinator-students-list';
                }
                return 'gecoordinator-students';
            }

            if (path.includes('/curriculum/select-subjects')) {
                return 'gecoordinator-import-courses';
            }

            if (path.includes('/gecoordinator/assign-subjects')) {
                return 'gecoordinator-manage-courses';
            }

            if (path.includes('/gecoordinator/manage-schedule')) {
                return 'gecoordinator-manage-schedule';
            }

            if (path.includes('/gecoordinator/help-guides')) {
                return 'gecoordinator-manage-help-guides';
            }

            if (path.includes('/help-guides/') && !path.includes('/preview') && !path.includes('/download') && !path.includes('/attachment/')) {
                return 'gecoordinator-help-guides-detail';
            }

            if (path === '/help-guides') {
                return 'gecoordinator-help-guides';
            }

            if (path.includes('/gecoordinator/instructors/create')) {
                return 'gecoordinator-instructors-create';
            }

            if (path.includes('/gecoordinator/instructors')) {
                return 'gecoordinator-instructors';
            }

            if (path === '/dashboard' || path === '/') {
                const isGECoordinatorDashboard = !!document.querySelector('a[href*="/gecoordinator/assign-subjects"]');
                if (isGECoordinatorDashboard) {
                    return 'gecoordinator-dashboard';
                }
            }

            return null;
        },

        createOverlayElements: function() {
            this.overlay = document.createElement('div');
            this.overlay.className = 'tutorial-overlay';
            this.overlay.id = 'tutorial-overlay';
            document.body.appendChild(this.overlay);

            this.spotlight = document.createElement('div');
            this.spotlight.className = 'tutorial-spotlight';
            this.spotlight.id = 'tutorial-spotlight';
            document.body.appendChild(this.spotlight);

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

        createTutorialButton: function() {
            const fab = document.createElement('button');
            fab.id = 'tutorial-fab';
            fab.className = 'tutorial-fab';
            fab.setAttribute('aria-label', 'Start Page Tutorial');
            fab.setAttribute('title', 'Page Tutorial');
            fab.innerHTML = '<i class="bi bi-question-lg"></i><span class="tutorial-fab-tooltip">Page Tutorial</span>';

            document.body.appendChild(fab);

            fab.addEventListener('click', () => {
                const pageId = this.getCurrentPageId();
                if (pageId && this.tutorials[pageId]) {
                    this.start(pageId);
                } else {
                    this.showNoTutorialMessage();
                }
            });
        },

        bindEvents: function() {
            this.tooltip.querySelector('.tutorial-close-btn').addEventListener('click', () => this.end());
            this.tooltip.querySelector('.tutorial-skip-btn').addEventListener('click', () => this.end());
            this.tooltip.querySelector('.tutorial-prev-btn').addEventListener('click', () => this.prevStep());
            this.tooltip.querySelector('.tutorial-next-btn').addEventListener('click', () => this.nextStep());

            document.addEventListener('keydown', (e) => {
                if (!this.isActive) return;
                if (e.key === 'Escape') this.end();
                else if (e.key === 'ArrowRight' || e.key === 'Enter') this.nextStep();
                else if (e.key === 'ArrowLeft') this.prevStep();
            });

            let resizeTimeout;
            window.addEventListener('resize', () => {
                if (!this.isActive) return;
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => this.updatePosition(), 100);
            });
        },

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

        start: function(tutorialId) {
            const tutorial = this.tutorials[tutorialId];
            if (!tutorial) {
                console.warn('Tutorial not found:', tutorialId);
                return;
            }

            this.currentTutorial = { id: tutorialId, ...tutorial };
            this.currentStep = 0;
            this.isActive = true;

            const fab = document.getElementById('tutorial-fab');
            if (fab) fab.classList.add('hidden');

            this.overlay.classList.add('active');
            this.tooltip.classList.add('active');

            this.showStep(0);
        },

        end: function() {
            this.isActive = false;
            this.overlay.classList.remove('active');
            this.tooltip.classList.remove('active');
            this.spotlight.classList.remove('active');

            const fab = document.getElementById('tutorial-fab');
            if (fab) fab.classList.remove('hidden');

            if (this.currentTutorial) {
                this.markTutorialCompleted(this.currentTutorial.id);
            }

            this.currentTutorial = null;
            this.currentStep = 0;

            document.querySelectorAll('.tutorial-highlight').forEach(el => el.classList.remove('tutorial-highlight'));
            document.querySelectorAll('.tutorial-active-modal').forEach(el => el.classList.remove('tutorial-active-modal'));
        },

        showStep: function(stepIndex) {
            if (!this.currentTutorial) return;

            const steps = this.currentTutorial.steps;
            let step = steps[stepIndex];
            let targetEl = this.findTarget(step.target);

            while (!targetEl && step.optional && stepIndex < steps.length - 1) {
                stepIndex++;
                step = steps[stepIndex];
                targetEl = this.findTarget(step.target);
            }

            if (!targetEl) {
                if (stepIndex < steps.length - 1) this.showStep(stepIndex + 1);
                else this.showCompletion();
                return;
            }

            this.currentStep = stepIndex;
            this.updateStepIndicator();
            this.updateButtons();
            this.highlightElement(targetEl);
            this.positionTooltip(targetEl, step);

            this.tooltip.querySelector('.tutorial-tooltip-title').textContent = step.title;
            this.tooltip.querySelector('.tutorial-tooltip-content').textContent = step.content;

            this.scrollIntoView(targetEl);
        },

        findTarget: function(selector) {
            if (!selector) return null;
            const selectors = selector.split(',').map(s => s.trim());
            for (const sel of selectors) {
                try {
                    const el = document.querySelector(sel);
                    if (el && this.isVisible(el)) return el;
                } catch (e) {
                    // Ignore invalid selectors.
                }
            }
            return null;
        },

        isVisible: function(el) {
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            const style = window.getComputedStyle(el);
            return rect.width > 0 && rect.height > 0 && style.visibility !== 'hidden' && style.display !== 'none';
        },

        highlightElement: function(el) {
            document.querySelectorAll('.tutorial-highlight').forEach(node => node.classList.remove('tutorial-highlight'));
            document.querySelectorAll('.tutorial-active-modal').forEach(node => node.classList.remove('tutorial-active-modal'));

            el.classList.add('tutorial-highlight');

            const modal = el.closest('.modal');
            if (modal && modal.classList.contains('show')) {
                modal.classList.add('tutorial-active-modal');
            }

            const rect = el.getBoundingClientRect();
            const padding = 8;
            this.spotlight.style.top = (rect.top + window.scrollY - padding) + 'px';
            this.spotlight.style.left = (rect.left + window.scrollX - padding) + 'px';
            this.spotlight.style.width = (rect.width + (padding * 2)) + 'px';
            this.spotlight.style.height = (rect.height + (padding * 2)) + 'px';
            this.spotlight.classList.add('active');
        },

        positionTooltip: function(el, step) {
            const rect = el.getBoundingClientRect();
            const tooltipRect = this.tooltip.getBoundingClientRect();
            const position = step.position || 'bottom';
            const spacing = 16;

            let top;
            let left;
            let actualPosition = position;

            switch (position) {
                case 'top':
                    top = rect.top + window.scrollY - tooltipRect.height - spacing;
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
                default:
                    top = rect.bottom + window.scrollY + spacing;
                    left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                    actualPosition = 'bottom';
                    break;
            }

            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const scrollY = window.scrollY;

            if (left < 10) left = 10;
            if (left + tooltipRect.width > viewportWidth - 10) {
                left = viewportWidth - tooltipRect.width - 10;
            }

            if (top < scrollY + 10) {
                const bottomPosition = rect.bottom + window.scrollY + spacing;
                if (bottomPosition + tooltipRect.height <= scrollY + viewportHeight - 10) {
                    top = bottomPosition;
                    actualPosition = 'bottom';
                } else {
                    top = scrollY + 10;
                }
            }

            if (top + tooltipRect.height > scrollY + viewportHeight - 10) {
                const topPosition = rect.top + window.scrollY - tooltipRect.height - spacing;
                if (topPosition >= scrollY + 10) {
                    top = topPosition;
                    actualPosition = 'top';
                } else {
                    top = scrollY + viewportHeight - tooltipRect.height - 10;
                }
            }

            this.tooltip.style.top = top + 'px';
            this.tooltip.style.left = left + 'px';
            this.tooltip.className = 'tutorial-tooltip active tutorial-tooltip-' + actualPosition;
        },

        scrollIntoView: function(el) {
            const rect = el.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const tooltipHeight = this.tooltip ? this.tooltip.getBoundingClientRect().height : 150;

            if (rect.top < 100 + tooltipHeight || rect.bottom > viewportHeight - 100) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        updateStepIndicator: function() {
            const indicator = this.tooltip.querySelector('.tutorial-step-indicator');
            const total = this.currentTutorial.steps.length;
            indicator.textContent = 'Step ' + (this.currentStep + 1) + ' of ' + total;
        },

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

        nextStep: function() {
            if (!this.currentTutorial) return;
            if (this.currentStep < this.currentTutorial.steps.length - 1) this.showStep(this.currentStep + 1);
            else this.showCompletion();
        },

        prevStep: function() {
            if (this.currentStep > 0) this.showStep(this.currentStep - 1);
        },

        showCompletion: function() {
            this.end();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Tutorial Complete!',
                    text: 'You have completed the tutorial. You can restart it anytime using the tutorial button.',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Got it!'
                });
            }
        },

        updatePosition: function() {
            if (!this.isActive || !this.currentTutorial) return;
            this.showStep(this.currentStep);
        },

        hasCompletedTutorial: function(tutorialId) {
            return localStorage.getItem(this.STORAGE_PREFIX + tutorialId + '_completed') === 'true';
        },

        markTutorialCompleted: function(tutorialId) {
            localStorage.setItem(this.STORAGE_PREFIX + tutorialId + '_completed', 'true');
        }
    };
})();
