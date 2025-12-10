/**
 * Chairperson Reports CO Course Chooser Page JavaScript
 * Handles course card navigation
 */

export function initCOCourseChooserPage() {
    document.querySelectorAll('.course-card[data-url]').forEach(card => {
        card.addEventListener('click', function(e) {
            const url = this.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });
        
        // Add keyboard support
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const url = this.dataset.url;
                if (url) {
                    window.location.href = url;
                }
            }
        });
    });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="co-course-chooser"]') || 
        window.location.pathname.includes('/reports/course-outcome')) {
        initCOCourseChooserPage();
    }
});

window.initCOCourseChooserPage = initCOCourseChooserPage;
