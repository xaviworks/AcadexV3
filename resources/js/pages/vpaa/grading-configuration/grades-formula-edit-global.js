/**
 * Admin Grades Formula Edit Global Page JavaScript
 * Handles structure template selection and weight display
 */

function flattenWeights(obj, prefix = '') {
  const weights = [];
  for (const [key, value] of Object.entries(obj)) {
    if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
      weights.push(...flattenWeights(value, key + ' '));
    } else if (typeof value === 'number') {
      const label = (prefix + key)
        .trim()
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (l) => l.toUpperCase());
      weights.push({ label, percent: Math.round(value) });
    }
  }
  return weights;
}

function updateWeightDisplay(structure) {
  const weightDisplay = document.getElementById('weight-display');
  if (!weightDisplay) return;

  if (!structure || typeof structure !== 'object') {
    weightDisplay.innerHTML = '<p class="text-muted small mb-0">No weights available</p>';
    return;
  }

  let html = '<div class="d-flex flex-wrap gap-2">';
  const weights = flattenWeights(structure);
  weights.forEach((weight) => {
    html += `<span class="badge bg-info-subtle text-info">${weight.label} ${weight.percent}%</span>`;
  });
  html += '</div>';
  weightDisplay.innerHTML = html;
}

export function initGradesFormulaEditGlobal() {
  const structureTypeInput = document.getElementById('structure-type-input');
  const structureConfigInput = document.getElementById('structure-config-input');
  const templateRadios = document.querySelectorAll('.structure-template-radio');

  templateRadios.forEach((radio) => {
    radio.addEventListener('change', function () {
      if (this.checked) {
        const templateKey = this.value;
        const structureData = JSON.parse(this.dataset.structure || '{}');

        if (structureTypeInput) structureTypeInput.value = templateKey;
        if (structureConfigInput) structureConfigInput.value = JSON.stringify(structureData);

        updateWeightDisplay(structureData);
      }
    });
  });

  // Initialize on page load
  const checkedRadio = document.querySelector('.structure-template-radio:checked');
  if (checkedRadio) {
    const structureData = JSON.parse(checkedRadio.dataset.structure || '{}');
    updateWeightDisplay(structureData);
  }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaEditGlobal);

// Expose function globally
window.initGradesFormulaEditGlobal = initGradesFormulaEditGlobal;
