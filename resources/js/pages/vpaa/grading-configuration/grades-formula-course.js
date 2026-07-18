/**
 * Admin Grades Formula Course Page JavaScript
 * Handles wildcard filtering and card click navigation
 */

export function initGradesFormulaCourse() {
  const filterButtons = document.querySelectorAll('.wildcard-filter-btn');
  const cards = document.querySelectorAll('#subject-wildcards .wildcard-card');

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.dataset.filter;

      filterButtons.forEach((btn) => btn.classList.remove('btn-success', 'active'));
      filterButtons.forEach((btn) => btn.classList.add('btn-outline-success'));
      button.classList.remove('btn-outline-success');
      button.classList.add('btn-success', 'active');

      cards.forEach((card) => {
        const status = card.dataset.status;
        const matches = filter === 'all' || status === filter;
        card.parentElement.classList.toggle('d-none', !matches);
      });
    });
  });

  cards.forEach((card) => {
    card.addEventListener('click', (event) => {
      const url = card.dataset.url;
      if (!url) {
        return;
      }

      const isInteractiveChild = event.target.closest('a, button, form, input, label');
      if (isInteractiveChild) {
        return;
      }

      window.location.href = url;
    });
  });
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaCourse);

// Expose function globally
window.initGradesFormulaCourse = initGradesFormulaCourse;
