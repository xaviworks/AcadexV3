/**
 * Admin Grades Formula Select Period Page JavaScript
 * 
 * Handles academic period selection for the Grade Formula Management flow.
 * This is separate from the instructor's select-academic-period page.
 */

export function initGradesFormulaSelectPeriod() {
    const periodCards = document.querySelectorAll('.period-card');
    const semesterCards = document.querySelectorAll('.period-card[data-year]');
    const submitBtn = document.getElementById('submitBtn');
    const yearDropdownItems = document.querySelectorAll('#yearDropdownMenu .dropdown-item');
    const selectedYearText = document.getElementById('selectedYearText');
    const noResults = document.getElementById('noResults');
    const semesterCardsContainer = document.getElementById('semesterCards');

    // Exit early if not on the correct page
    if (!periodCards.length) return;

    /**
     * Handle card selection
     */
    periodCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;

                // Remove selected class from all cards
                periodCards.forEach(c => c.classList.remove('selected'));

                // Add selected class to clicked card
                this.classList.add('selected');

                // Enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });
    });

    /**
     * Handle year dropdown selection
     */
    yearDropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const year = this.dataset.year;

            // Update dropdown button text
            if (selectedYearText) {
                selectedYearText.textContent = year;
            }

            // Update active state
            yearDropdownItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            // Filter semester cards by year
            let visibleCount = 0;
            semesterCards.forEach(card => {
                if (card.dataset.year === year) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                    // Deselect hidden cards
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio && radio.checked) {
                        radio.checked = false;
                        card.classList.remove('selected');
                        if (submitBtn) {
                            submitBtn.disabled = !document.querySelector('.period-card input:checked');
                        }
                    }
                }
            });

            // Show/hide no results message
            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
            if (semesterCardsContainer) {
                semesterCardsContainer.style.display = visibleCount === 0 ? 'none' : 'flex';
            }
        });
    });

    /**
     * Check for initial selection (e.g., from old input)
     */
    const checkedRadio = document.querySelector('.period-card input:checked');
    if (checkedRadio) {
        checkedRadio.closest('.period-card').classList.add('selected');
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaSelectPeriod);

// Expose function globally for manual initialization
window.initGradesFormulaSelectPeriod = initGradesFormulaSelectPeriod;
