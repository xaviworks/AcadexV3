/**
 * Admin Grades Formula Select Period Page JavaScript
 * Handles academic period selection sync
 */

export function initGradesFormulaSelectPeriod() {
  const select = document.getElementById('academic-period-select');
  const periodInput = document.getElementById('selected-academic-period');
  const yearInput = document.getElementById('selected-academic-year');
  const semesterInput = document.getElementById('selected-semester');
  const continueButton = document.getElementById('continue-button');

  if (!select) return;

  const syncSelection = () => {
    const option = select.options[select.selectedIndex];
    const hasSelection = option && option.value !== '';

    if (continueButton) {
      continueButton.disabled = !hasSelection;
    }

    if (!hasSelection) {
      if (periodInput) periodInput.value = '';
      if (yearInput) yearInput.value = '';
      if (semesterInput) semesterInput.value = '';
      return;
    }

    if (option.value === 'all') {
      if (periodInput) periodInput.value = 'all';
      if (yearInput) yearInput.value = '';
      if (semesterInput) semesterInput.value = '';
    } else {
      if (periodInput) periodInput.value = option.value;
      if (yearInput) yearInput.value = option.dataset.year ?? '';
      if (semesterInput) semesterInput.value = option.dataset.semester ?? '';
    }
  };

  select.addEventListener('change', syncSelection);
  syncSelection();
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaSelectPeriod);

// Expose function globally
window.initGradesFormulaSelectPeriod = initGradesFormulaSelectPeriod;
