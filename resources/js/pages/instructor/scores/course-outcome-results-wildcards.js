/**
 * Instructor Course Outcome Results Wildcards Page JavaScript
 * Handles subject card click navigation
 */

export function initCourseOutcomeResultsWildcardsPage() {
    // Subject card click handlers
    document.querySelectorAll('.subject-card[data-url]').forEach(card => {
        card.addEventListener('click', function() {
            window.location.href = this.dataset.url;
        });
    });
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initCourseOutcomeResultsWildcardsPage);

// Expose function globally
window.initCourseOutcomeResultsWildcardsPage = initCourseOutcomeResultsWildcardsPage;
