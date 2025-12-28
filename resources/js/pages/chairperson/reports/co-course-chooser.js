/**
 * Chairperson Reports CO Course Chooser Page JavaScript
 * Handles course card navigation
 */

export function initCOCourseChooserPage() {
  document.querySelectorAll('.course-card[data-url]').forEach((card) => {
    card.addEventListener('click', function (e) {
      const url = this.dataset.url;
      if (url) {
        window.location.href = url;
      }
    });

    // Add keyboard support
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');

    card.addEventListener('keydown', function (e) {
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
document.addEventListener('DOMContentLoaded', function () {
  // Initialize if we have course cards with data-url attributes
  if (document.querySelector('.course-card[data-url]')) {
    initCOCourseChooserPage();
  }
});

window.initCOCourseChooserPage = initCOCourseChooserPage;
