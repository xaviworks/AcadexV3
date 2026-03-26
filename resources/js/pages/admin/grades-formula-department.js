/**
 * Admin Grades Formula Department Page JavaScript
 * Handles wildcard filtering and card click navigation
 */

export function initGradesFormulaDepartment() {
  const filterButtons = document.querySelectorAll('.wildcard-filter-btn');
  const cards = document.querySelectorAll('[data-wildcard-section] .wildcard-card');

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.dataset.filter;

      filterButtons.forEach((btn) => btn.classList.remove('btn-success', 'active'));
      filterButtons.forEach((btn) => btn.classList.add('btn-outline-success'));
      button.classList.remove('btn-outline-success');
      button.classList.add('btn-success', 'active');

      cards.forEach((card) => {
        const status = card.dataset.status;
        const matches = filter === 'all' ? true : status === filter;

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
document.addEventListener('DOMContentLoaded', initGradesFormulaDepartment);

// Expose function globally
window.initGradesFormulaDepartment = initGradesFormulaDepartment;
