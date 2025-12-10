/**
 * Admin Grades Formula Department Page JavaScript
 * Handles wildcard filtering and card click navigation
 */

export function initGradesFormulaDepartment() {
    const filterButtons = document.querySelectorAll('.wildcard-filter-btn');
    const cards = document.querySelectorAll('[data-wildcard-section] .wildcard-card');
    const catalogSection = document.querySelector('[data-wildcard-section="catalog"]');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;

            filterButtons.forEach(btn => btn.classList.remove('btn-success', 'active'));
            filterButtons.forEach(btn => btn.classList.add('btn-outline-success'));
            button.classList.remove('btn-outline-success');
            button.classList.add('btn-success', 'active');

            cards.forEach(card => {
                const status = card.dataset.status;
                let matches = false;

                if (filter === 'all') {
                    matches = status !== 'catalog';
                } else if (filter === 'custom') {
                    matches = status === 'catalog';
                } else {
                    matches = status === filter;
                }

                card.parentElement.classList.toggle('d-none', !matches);
            });

            if (catalogSection) {
                catalogSection.classList.toggle('d-none', filter !== 'custom');
            }
        });
    });

    cards.forEach(card => {
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

    // Initialize default state for catalog section visibility
    if (catalogSection) {
        catalogSection.classList.add('d-none');
    }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaDepartment);

// Expose function globally
window.initGradesFormulaDepartment = initGradesFormulaDepartment;
