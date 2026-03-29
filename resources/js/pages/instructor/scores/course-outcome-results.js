/**
 * Instructor Course Outcome Results page logic
 * Restores display toggles, term switching, and print/export helpers.
 */

let currentTerm = null;
let targetLevelEditorInitialized = false;

const TARGET_LEVEL_INPUT_IDS = {
  level_3: 'target-level-3',
  level_2: 'target-level-2',
  level_1: 'target-level-1',
};

const TARGET_LEVEL_TEXT_CLASSES = ['text-muted', 'text-success', 'text-primary', 'text-warning', 'text-danger'];

const targetLevelState = {
  initialLevels: null,
  lastValidLevels: null,
};

const TERM_LABELS = {
  prelim: 'Prelim',
  midterm: 'Midterm',
  prefinal: 'Prefinal',
  final: 'Final',
};

const DISPLAY_TYPE_ICON_SVG = {
  score: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6l1 2h3a1 1 0 0 1 1 1v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a1 1 0 0 1 1-1h3l1-2z"></path><path d="M9 10h6"></path><path d="M9 14h6"></path></svg>`,
  percentage: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16"></path><path d="M7 16v-5"></path><path d="M12 16V8"></path><path d="M17 16v-3"></path></svg>`,
  passfail: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m9 12 2 2 4-4"></path></svg>`,
  copasssummary: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16"></path><path d="m6 15 4-4 3 3 5-6"></path></svg>`,
};

function isCourseOutcomeResultsPage() {
  return document.querySelector('[data-page="instructor.course-outcome-results"]');
}

function updateCurrentViewBadge(term = null) {
  const currentViewBadge = document.getElementById('current-view');
  if (!currentViewBadge) return;

  currentViewBadge.textContent = term ? `${TERM_LABELS[term] || term} Term` : 'All Terms';
}

function parseTargetLevelValue(rawValue) {
  if (typeof rawValue !== 'string') return null;
  const trimmed = rawValue.trim();
  if (trimmed === '') return null;

  const parsed = Number.parseFloat(trimmed);
  return Number.isFinite(parsed) ? parsed : null;
}

function clampTargetLevelValue(value) {
  if (!Number.isFinite(value)) return null;
  if (value > 100) return 100;
  if (value < 0) return 0;
  return value;
}

function normalizeTargetLevelInput(input) {
  if (!input || typeof input.value !== 'string') return;

  const parsed = parseTargetLevelValue(input.value);
  if (parsed === null) return;

  const clamped = clampTargetLevelValue(parsed);
  if (clamped !== null && clamped !== parsed) {
    input.value = String(clamped);
  }
}

function readTargetLevelsFromInputs() {
  const level3Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_3);
  const level2Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_2);
  const level1Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_1);

  return {
    level_3: parseTargetLevelValue(level3Input?.value || ''),
    level_2: parseTargetLevelValue(level2Input?.value || ''),
    level_1: parseTargetLevelValue(level1Input?.value || ''),
  };
}

function hasCompleteTargetLevels(levels) {
  return levels.level_3 !== null && levels.level_2 !== null && levels.level_1 !== null;
}

function hasTargetLevelsInRange(levels) {
  return [levels.level_3, levels.level_2, levels.level_1].every(
    (value) => value !== null && value >= 0 && value <= 100
  );
}

function hasValidTargetLevelOrder(levels) {
  return levels.level_3 >= levels.level_2 && levels.level_2 >= levels.level_1;
}

function isValidTargetLevelSet(levels) {
  return hasCompleteTargetLevels(levels) && hasTargetLevelsInRange(levels) && hasValidTargetLevelOrder(levels);
}

function resolveTargetLevelAchievedForPreview(metTargetPercentage, levels) {
  if (metTargetPercentage === null || Number.isNaN(metTargetPercentage)) {
    return null;
  }

  if (metTargetPercentage >= levels.level_3) return 3.0;
  if (metTargetPercentage >= levels.level_2) return 2.0;
  if (metTargetPercentage >= levels.level_1) return 1.0;
  return 0.0;
}

function getTargetLevelClass(value) {
  if (value === null) return 'text-muted';
  if (value >= 3.0) return 'text-success';
  if (value >= 2.0) return 'text-primary';
  if (value >= 1.0) return 'text-warning';
  return 'text-danger';
}

function getTargetLevelDisplayText(value) {
  return value === null ? '--' : value.toFixed(1);
}

function setTargetLevelValidationState(message = '', isInvalid = false) {
  const validationMessage = document.getElementById('target-level-validation-message');
  if (validationMessage) {
    validationMessage.textContent = message;
    validationMessage.classList.toggle('d-none', !isInvalid);
  }

  Object.values(TARGET_LEVEL_INPUT_IDS).forEach((id) => {
    const input = document.getElementById(id);
    if (input) {
      input.classList.toggle('is-invalid', isInvalid);
    }
  });
}

function renderTargetLevelAchievedCells(levels) {
  document.querySelectorAll('[data-target-level-cell="true"]').forEach((cell) => {
    const rawMetTargetPercentage = cell.getAttribute('data-met-target-percentage');
    const metTargetPercentage =
      rawMetTargetPercentage === '' || rawMetTargetPercentage === null
        ? null
        : Number.parseFloat(rawMetTargetPercentage);

    const targetLevelValue = resolveTargetLevelAchievedForPreview(metTargetPercentage, levels);
    const nextClass = getTargetLevelClass(targetLevelValue);

    cell.classList.remove(...TARGET_LEVEL_TEXT_CLASSES);
    cell.classList.add(nextClass);
    cell.textContent = getTargetLevelDisplayText(targetLevelValue);
  });
}

function applyLiveTargetLevelPreview() {
  const levels = readTargetLevelsFromInputs();

  if (!hasCompleteTargetLevels(levels)) {
    setTargetLevelValidationState('All target levels are required to preview changes.', true);
    return;
  }

  if (!hasTargetLevelsInRange(levels)) {
    setTargetLevelValidationState('Target levels must be between 0 and 100.', true);
    return;
  }

  if (!hasValidTargetLevelOrder(levels)) {
    setTargetLevelValidationState('Target levels must follow Level 3 >= Level 2 >= Level 1.', true);
    return;
  }

  setTargetLevelValidationState('', false);
  targetLevelState.lastValidLevels = { ...levels };
  renderTargetLevelAchievedCells(levels);
}

function resetTemporaryTargetLevels() {
  if (!targetLevelState.initialLevels) return;

  const level3Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_3);
  const level2Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_2);
  const level1Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_1);

  if (level3Input) level3Input.value = targetLevelState.initialLevels.level_3;
  if (level2Input) level2Input.value = targetLevelState.initialLevels.level_2;
  if (level1Input) level1Input.value = targetLevelState.initialLevels.level_1;

  setTargetLevelValidationState('', false);
  targetLevelState.lastValidLevels = { ...targetLevelState.initialLevels };
  renderTargetLevelAchievedCells(targetLevelState.initialLevels);
}

function initTargetLevelEditor() {
  if (targetLevelEditorInitialized) return;

  const summaryTargetLevelForm = document.getElementById('summary-target-level-form');
  if (!summaryTargetLevelForm) return;

  const level3Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_3);
  const level2Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_2);
  const level1Input = document.getElementById(TARGET_LEVEL_INPUT_IDS.level_1);
  const resetButton = document.getElementById('target-level-reset-btn');

  if (!level3Input || !level2Input || !level1Input) return;

  summaryTargetLevelForm.addEventListener('submit', (event) => {
    // Non-persistent by design: prevent accidental DB writes through Enter key submits.
    event.preventDefault();
  });

  [level3Input, level2Input, level1Input].forEach((input) => {
    input.removeAttribute('readonly');
    input.setAttribute('max', '100');
    input.addEventListener('input', () => {
      normalizeTargetLevelInput(input);
      applyLiveTargetLevelPreview();
    });
    input.addEventListener('change', () => {
      normalizeTargetLevelInput(input);
      applyLiveTargetLevelPreview();
    });
  });

  if (resetButton) {
    resetButton.addEventListener('click', resetTemporaryTargetLevels);
  }

  [level3Input, level2Input, level1Input].forEach(normalizeTargetLevelInput);

  const initialLevels = readTargetLevelsFromInputs();
  if (isValidTargetLevelSet(initialLevels)) {
    targetLevelState.initialLevels = { ...initialLevels };
    targetLevelState.lastValidLevels = { ...initialLevels };
    renderTargetLevelAchievedCells(initialLevels);
  }

  targetLevelEditorInitialized = true;
}

// ==================== CUSTOM PRINT MODAL (No Bootstrap dependency) ====================

/**
 * Open the print options modal using custom CSS-based modal
 */
function coOpenPrintModal() {
  const overlay = document.getElementById('coPrintModalOverlay');
  if (overlay) {
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
    return;
  }

  // Fallback to Bootstrap modal if custom one doesn't exist
  const modalEl = document.getElementById('printOptionsModal');
  if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}

/**
 * Close the print options modal
 */
function coClosePrintModal() {
  const overlay = document.getElementById('coPrintModalOverlay');
  if (overlay) {
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    return;
  }

  // Fallback to Bootstrap modal
  const modalEl = document.getElementById('printOptionsModal');
  if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
  }
}

// Expose globally IMMEDIATELY so onclick handlers work
window.coOpenPrintModal = coOpenPrintModal;
window.coClosePrintModal = coClosePrintModal;

function setDisplayType(type, iconKey, text) {
  const currentIcon = document.getElementById('currentIcon');
  const currentText = document.getElementById('currentText');
  if (currentIcon) {
    currentIcon.innerHTML = DISPLAY_TYPE_ICON_SVG[iconKey] || DISPLAY_TYPE_ICON_SVG[type] || '';
  }
  if (currentText) currentText.textContent = text;

  const scoreTypeSelect = document.getElementById('scoreType');
  if (scoreTypeSelect) scoreTypeSelect.value = type;

  document.querySelectorAll('.dropdown-item').forEach((item) => item.classList.remove('active'));
  document.querySelectorAll('.dropdown-item').forEach((item) => {
    const handler = item.getAttribute('onclick') || '';
    if (handler.includes(`'${type}'`)) item.classList.add('active');
  });

  const dropdownElement = document.getElementById('displayTypeDropdown');
  if (dropdownElement) {
    const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
    if (dropdown) dropdown.hide();
  }

  const termStepperContainer = document.getElementById('term-navigation-container');
  const compactStepper = document.querySelector('.compact-stepper');
  const stepperColumn = document.querySelector('.col-md-6.text-end');

  if (type === 'passfail') {
    if (termStepperContainer) {
      termStepperContainer.style.display = 'none';
      termStepperContainer.style.visibility = 'hidden';
    }
    if (compactStepper) {
      compactStepper.style.display = 'none';
      compactStepper.style.visibility = 'hidden';
    }
    if (stepperColumn) stepperColumn.style.display = 'none';
  } else {
    if (termStepperContainer) {
      termStepperContainer.style.display = 'flex';
      termStepperContainer.style.visibility = 'visible';
    }
    if (compactStepper) {
      compactStepper.style.display = 'flex';
      compactStepper.style.visibility = 'visible';
    }
    if (stepperColumn) stepperColumn.style.display = 'block';
  }

  toggleScoreTypeWithValue(type);
  updateCurrentViewBadge(currentTerm);
}

function toggleScoreTypeWithValue(type) {
  const passfailTable = document.getElementById('passfail-table');
  const copasssummaryTable = document.getElementById('copasssummary-table');
  const summaryTargetLevelControls = document.getElementById('summary-target-level-controls');
  const mainTables = document.querySelectorAll('.main-table');
  const termTables = document.querySelectorAll('.term-table');
  const summaryLabel = document.getElementById('summaryLabel');
  const termSummaryLabels = document.querySelectorAll('.term-summary-label');
  const termStepperContainer = document.getElementById('term-navigation-container');

  if (summaryTargetLevelControls) {
    summaryTargetLevelControls.style.display = type === 'copasssummary' ? 'block' : 'none';
  }

  if (type === 'passfail') {
    if (passfailTable) passfailTable.style.display = 'block';
    if (copasssummaryTable) copasssummaryTable.style.display = 'none';
    mainTables.forEach((tbl) => (tbl.style.display = 'none'));
    termTables.forEach((tbl) => (tbl.style.display = 'none'));
    if (termStepperContainer) {
      termStepperContainer.style.display = 'none';
      termStepperContainer.style.visibility = 'hidden';
    }
    document.querySelectorAll('.passfail-term-table').forEach((tbl) => (tbl.style.display = 'none'));
  } else if (type === 'copasssummary') {
    if (passfailTable) passfailTable.style.display = 'none';
    mainTables.forEach((tbl) => (tbl.style.display = 'none'));
    termTables.forEach((tbl) => (tbl.style.display = 'none'));
    if (termStepperContainer) {
      termStepperContainer.style.display = 'flex';
      termStepperContainer.style.visibility = 'visible';
    }
    document.querySelectorAll('.passfail-term-table').forEach((tbl) => (tbl.style.display = 'none'));
    document.querySelectorAll('.summary-term-table').forEach((tbl) => (tbl.style.display = 'none'));

    if (currentTerm) {
      if (copasssummaryTable) copasssummaryTable.style.display = 'none';
      const activeSummaryTable = document.getElementById(`summary-term-${currentTerm}`);
      if (activeSummaryTable) activeSummaryTable.style.display = 'block';
    } else if (copasssummaryTable) {
      copasssummaryTable.style.display = 'block';
    }
  } else {
    if (passfailTable) passfailTable.style.display = 'none';
    if (copasssummaryTable) copasssummaryTable.style.display = 'none';
    if (termStepperContainer) {
      termStepperContainer.style.display = 'flex';
      termStepperContainer.style.visibility = 'visible';
    }
    document.querySelectorAll('.passfail-term-table').forEach((tbl) => (tbl.style.display = 'none'));
    document.querySelectorAll('.summary-term-table').forEach((tbl) => (tbl.style.display = 'none'));

    if (!currentTerm) {
      mainTables.forEach((tbl) => (tbl.style.display = 'block'));
      termTables.forEach((tbl) => (tbl.style.display = 'none'));
    } else {
      mainTables.forEach((tbl) => (tbl.style.display = 'none'));
      termTables.forEach((tbl) => (tbl.style.display = 'none'));
      const activeTerm = document.getElementById(`term-${currentTerm}`);
      if (activeTerm) activeTerm.style.display = 'block';
    }

    document.querySelectorAll('.score-value').forEach((el) => {
      el.style.display = 'inline';
      const score = el.getAttribute('data-score');
      const percent = el.getAttribute('data-percentage');
      el.classList.remove('text-success', 'text-danger');
      if (type === 'score') {
        el.textContent = score;
      } else {
        el.textContent = percent !== '' && percent !== null ? `${percent}%` : '-';
        if (type === 'percentage' && percent !== '' && percent !== null && percent !== '-') {
          const percentValue = parseFloat(percent);
          if (percentValue >= 75) {
            el.classList.add('text-success');
          } else {
            el.classList.add('text-danger');
          }
        }
      }
    });
  }

  if (type === 'percentage') {
    if (summaryLabel && summaryLabel.closest('tr')) summaryLabel.closest('tr').style.display = 'none';
    termSummaryLabels.forEach((label) => (label.closest('tr').style.display = 'none'));
  } else {
    if (summaryLabel && summaryLabel.closest('tr')) {
      summaryLabel.closest('tr').style.display = '';
      summaryLabel.textContent = 'Total number of items';
    }
    termSummaryLabels.forEach((label) => {
      if (label.closest('tr')) {
        label.closest('tr').style.display = '';
        label.textContent = 'Total number of items';
      }
    });
  }
}

function toggleScoreType() {
  const scoreTypeEl = document.getElementById('scoreType');
  if (!scoreTypeEl) return;
  toggleScoreTypeWithValue(scoreTypeEl.value);
}

function switchTerm(term, index) {
  currentTerm = term;
  const scoreTypeEl = document.getElementById('scoreType');
  const scoreType = scoreTypeEl ? scoreTypeEl.value : 'raw';

  const combinedTable = document.getElementById('combined-table');
  const termTables = document.querySelectorAll('.term-table');
  const passfailTable = document.getElementById('passfail-table');
  const copasssummaryTable = document.getElementById('copasssummary-table');
  const passfailTermTables = document.querySelectorAll('.passfail-term-table');
  const summaryTermTables = document.querySelectorAll('.summary-term-table');

  if (combinedTable) combinedTable.style.display = 'none';
  termTables.forEach((tbl) => (tbl.style.display = 'none'));

  if (scoreType === 'passfail') {
    if (passfailTable) passfailTable.style.display = 'none';
    passfailTermTables.forEach((tbl) => (tbl.style.display = 'none'));
    const activePassfailTable = document.getElementById(`passfail-term-${term}`);
    if (activePassfailTable) activePassfailTable.style.display = 'block';
  } else if (scoreType === 'copasssummary') {
    if (copasssummaryTable) copasssummaryTable.style.display = 'none';
    summaryTermTables.forEach((tbl) => (tbl.style.display = 'none'));
    const activeSummaryTable = document.getElementById(`summary-term-${term}`);
    if (activeSummaryTable) activeSummaryTable.style.display = 'block';
  } else {
    const activeTable = document.getElementById(`term-${term}`);
    if (activeTable) activeTable.style.display = 'block';
  }

  const allSteps = document.querySelectorAll('.compact-step');
  allSteps.forEach((step, i) => {
    step.classList.remove('active', 'completed', 'upcoming');
    if (i === 0) {
      step.classList.add('completed');
    } else {
      const termIndex = i - 1;
      if (termIndex < index) {
        step.classList.add('completed');
      } else if (termIndex === index) {
        step.classList.add('active');
      } else {
        step.classList.add('upcoming');
      }
    }
  });

  const type = document.getElementById('scoreType')?.value || 'score';
  document.querySelectorAll('.score-value').forEach((el) => {
    const score = el.getAttribute('data-score');
    const percent = el.getAttribute('data-percentage');
    el.textContent = type === 'score' ? score : percent !== '' && percent !== null ? `${percent}%` : '-';
  });

  updateCurrentViewBadge(term);
}

function showAllTerms() {
  currentTerm = null;
  const scoreType = document.getElementById('scoreType')?.value || 'score';

  const termTables = document.querySelectorAll('.term-table');
  const passfailTermTables = document.querySelectorAll('.passfail-term-table');
  const summaryTermTables = document.querySelectorAll('.summary-term-table');

  termTables.forEach((tbl) => (tbl.style.display = 'none'));
  passfailTermTables.forEach((tbl) => (tbl.style.display = 'none'));
  summaryTermTables.forEach((tbl) => (tbl.style.display = 'none'));

  if (scoreType === 'passfail') {
    const passfailTable = document.getElementById('passfail-table');
    if (passfailTable) passfailTable.style.display = 'block';
  } else if (scoreType === 'copasssummary') {
    const copasssummaryTable = document.getElementById('copasssummary-table');
    if (copasssummaryTable) copasssummaryTable.style.display = 'block';
  } else {
    const combinedTable = document.getElementById('combined-table');
    if (combinedTable) combinedTable.style.display = 'block';
  }

  const allSteps = document.querySelectorAll('.compact-step');
  allSteps.forEach((step, i) => {
    step.classList.remove('active', 'completed', 'upcoming');
    if (i === 0) {
      step.classList.add('active');
    } else {
      step.classList.add('upcoming');
    }
  });

  toggleScoreType();
  updateCurrentViewBadge(null);
}

function coPrintTable() {
  coPrintSpecificTable('combined');
}

function coPrintSpecificTable(tableType) {
  const bannerUrl = window.bannerUrl || '/images/banner-header.png';
  const academicPeriod = window.academicPeriod || 'N/A';
  const semester = window.semester || 'N/A';
  const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  const subjectInfo = window.subjectInfo || 'Course Outcome Results';
  const courseCode = window.courseCode || 'N/A';
  const subjectDescription = window.subjectDescription || 'N/A';
  const units = window.units || 'N/A';
  const courseSection = window.courseSection || 'N/A';

  let content = '';
  let reportTitle = '';
  switch (tableType) {
    case 'prelim':
      content = getPrintTableContent('prelim');
      reportTitle = 'Course Outcome Attainment Results - Prelim Term';
      break;
    case 'midterm':
      content = getPrintTableContent('midterm');
      reportTitle = 'Course Outcome Attainment Results - Midterm';
      break;
    case 'prefinal':
      content = getPrintTableContent('prefinal');
      reportTitle = 'Course Outcome Attainment Results - Prefinal Term';
      break;
    case 'final':
      content = getPrintTableContent('final');
      reportTitle = 'Course Outcome Attainment Results - Final Term';
      break;
    case 'combined':
      content = getPrintTableContent('combined');
      reportTitle = 'Course Outcome Attainment Results - All Terms Combined';
      break;
    case 'passfail':
      content = getPassFailContent();
      reportTitle = 'Course Outcome Pass/Fail Analysis Report';
      break;
    case 'copasssummary':
      content = getCourseOutcomeSummaryContent();
      reportTitle = 'Course Outcomes Summary Dashboard Report';
      break;
    case 'summary-prelim':
      content = getCourseOutcomeSummaryContent('prelim');
      reportTitle = 'Course Outcomes Summary Dashboard - Prelim Term';
      break;
    case 'summary-midterm':
      content = getCourseOutcomeSummaryContent('midterm');
      reportTitle = 'Course Outcomes Summary Dashboard - Midterm Term';
      break;
    case 'summary-prefinal':
      content = getCourseOutcomeSummaryContent('prefinal');
      reportTitle = 'Course Outcomes Summary Dashboard - Prefinal Term';
      break;
    case 'summary-final':
      content = getCourseOutcomeSummaryContent('final');
      reportTitle = 'Course Outcomes Summary Dashboard - Final Term';
      break;
    case 'all':
      content = getAllTablesContent();
      reportTitle = 'Complete Course Outcome Attainment Report';
      break;
    default:
      content = getPrintTableContent('combined');
      reportTitle = 'Course Outcome Attainment Results';
  }

  const printWindow = window.open('', '', 'width=900,height=650');
  if (!printWindow) return;

  printWindow.document.write(`
        <html>
            <head>
                <title>${reportTitle}</title>
                <style>
                    @media print {
                        @page { size: A4 portrait; margin: 0.75in 0.5in; }
                        body { font-size: 10px; }
                        table { font-size: 9px; }
                        .banner { max-height: 100px; }
                        .report-title { font-size: 16px; }
                        .percentage-value { color: #000000 !important; }
                        .text-success, .text-danger { color: #000000 !important; }
                    }
                    body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; color: #333; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; line-height: 1.6; }
                    .banner { width: 100%; max-height: 130px; object-fit: contain; margin-bottom: 15px; }
                    .header-content { margin-bottom: 20px; }
                    .report-title { font-size: 20px; font-weight: bold; text-align: center; margin: 15px 0; text-transform: uppercase; letter-spacing: 1px; color: #4a7c59; border-bottom: 2px solid #4a7c59; padding-bottom: 8px; }
                    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; background-color: #fff; font-size: 11px; border: 2px solid #4a7c59; }
                    .header-table td { padding: 8px 12px; border: 1px solid #2d4a35; }
                    .header-label { font-weight: bold; width: 120px; background-color: #4a7c59; color: #fff; }
                    .header-value { font-family: 'Arial', sans-serif; font-weight: 500; }
                    .print-table { width: 100%; border-collapse: collapse; border: 2px solid #4a7c59; background-color: #fff; margin-top: 15px; font-size: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                    .print-table th, .print-table td { border: 1px solid #2d4a35; padding: 6px 4px; text-align: center; vertical-align: middle; }
                    .print-table th { background-color: #4a7c59; color: #fff; font-weight: bold; text-transform: uppercase; white-space: nowrap; font-size: 9px; }
                    .print-table th:first-child { background-color: #2d4a35; text-align: left; }
                    .print-table .table-success th, .print-table th.table-success { background-color: #4a7c59 !important; color: white !important; }
                    .print-table .bg-primary, .print-table th.bg-primary, .print-table td.bg-primary { background-color: #2d4a35 !important; color: white !important; font-weight: bold !important; }
                    .print-table thead tr:first-child th { background-color: #4a7c59 !important; color: white !important; font-size: 10px !important; padding: 8px 4px !important; text-align: center !important; font-weight: bold !important; }
                    .print-table thead tr:nth-child(2) th { background-color: #4a7c59 !important; color: white !important; font-size: 8px !important; padding: 6px 2px !important; }
                    .print-table thead tr:nth-child(2) th.bg-primary { background-color: #2d4a35 !important; }
                    .print-table tbody td { background-color: white !important; font-size: 8px !important; padding: 4px 2px !important; }
                    .print-table tbody td:first-child { text-align: left; background-color: #f8f9fa !important; font-weight: normal !important; padding-left: 6px !important; }
                    .print-table tbody tr:first-child td { background-color: #e8f5e8 !important; font-weight: bold !important; }
                    .print-table .score-value { font-weight: bold !important; color: #000 !important; }
                    .print-table .bg-light { background-color: #f8f9fa !important; }
                    .print-table tr:nth-child(even) { background-color: #f0f7f4; }
                    .print-table td:first-child { text-align: left; font-weight: 500; background-color: #f8f9fa; }
                    .score-value { font-weight: bold; color: #1a5f38; }
                    .percentage-value { color: #000000; font-weight: 500; }
                    .average-cell { background-color: #e8f5e8 !important; font-weight: bold; color: #1a5f38; }
                    .term-section { margin-bottom: 30px; page-break-inside: avoid; }
                    .term-title { font-size: 16px; font-weight: bold; color: #1a5f38; margin: 20px 0 10px 0; padding: 8px 12px; background-color: #f0f7f4; border-left: 4px solid #1a5f38; }
                    .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6; font-size: 11px; color: #666; text-align: center; }
                    .page-break { page-break-before: always; }
                </style>
            </head>
            <body>
                <img src="${bannerUrl}" alt="Banner Header" class="banner">
                <div class="header-content">
                    <div class="report-title">${reportTitle}</div>
                    <table class="header-table">
                        <tr><td class="header-label">Course Code:</td><td class="header-value">${courseCode}</td><td class="header-label">Units:</td><td class="header-value">${units}</td></tr>
                        <tr><td class="header-label">Description:</td><td class="header-value">${subjectDescription}</td><td class="header-label">Semester:</td><td class="header-value">${semester}</td></tr>
                        <tr><td class="header-label">Course/Section:</td><td class="header-value">${courseSection}</td><td class="header-label">School Year:</td><td class="header-value">${academicPeriod}</td></tr>
                    </table>
                </div>
                ${content}
                <div class="footer">This is a computer-generated document. No signature is required.<br>Printed via ACADEX - Academic Grade System on ${currentDate}</div>
            </body>
        </html>
    `);
  printWindow.document.close();
  setTimeout(() => printWindow.print(), 500);
}

function getPrintTableContent(termType) {
  let tableSelector = '';
  let termTitle = '';
  switch (termType) {
    case 'prelim':
      tableSelector = '#term-prelim table';
      termTitle = 'Prelim Term Results';
      break;
    case 'midterm':
      tableSelector = '#term-midterm table';
      termTitle = 'Midterm Results';
      break;
    case 'prefinal':
      tableSelector = '#term-prefinal table';
      termTitle = 'Prefinal Term Results';
      break;
    case 'final':
      tableSelector = '#term-final table';
      termTitle = 'Final Term Results';
      break;
    case 'combined':
      tableSelector = '#combined-table table';
      termTitle = 'All Terms Combined';
      break;
  }

  const table = document.querySelector(tableSelector);
  if (!table) return '<p>No data available for the selected term.</p>';

  let tableHTML = '<div class="term-section">';
  if (termType !== 'combined') tableHTML += `<h3 class="term-title">${termTitle}</h3>`;
  tableHTML += '<table class="print-table">';

  const rows = table.querySelectorAll('tr');
  const currentScoreType = document.getElementById('scoreType')?.value || 'score';

  rows.forEach((row) => {
    const isHeader = row.closest('thead') !== null;
    const tag = isHeader ? 'th' : 'td';
    if (currentScoreType === 'percentage' && !isHeader) {
      const firstCell = row.querySelector('td');
      if (firstCell && firstCell.textContent.includes('Total number of items')) return;
    }

    tableHTML += '<tr>';
    const cells = row.querySelectorAll(isHeader ? 'th' : 'td');
    cells.forEach((cell) => {
      let cellContent = cell.textContent.trim();
      let cellClass = '';
      let cellAttrs = '';
      if (cell.hasAttribute('colspan')) cellAttrs += ` colspan="${cell.getAttribute('colspan')}"`;
      if (cell.hasAttribute('rowspan')) cellAttrs += ` rowspan="${cell.getAttribute('rowspan')}"`;
      if (cell.classList.contains('bg-primary') || cell.classList.contains('text-white'))
        cellClass += ' bg-primary text-white';
      if (cell.classList.contains('table-success')) cellClass += ' table-success';
      if (cell.classList.contains('align-middle')) cellClass += ' align-middle';
      if (cell.classList.contains('text-center')) cellClass += ' text-center';
      if (cell.classList.contains('fw-bold')) cellClass += ' fw-bold';
      if (cell.classList.contains('bg-light')) cellClass += ' bg-light';
      if (cell.classList.contains('score-value') || /^\d+$/.test(cellContent)) cellClass += ' score-value';
      else if (cellContent.includes('%')) cellClass += ' percentage-value';
      else if (cell.textContent.includes('Average') || cell.classList.contains('average-cell'))
        cellClass += ' average-cell';
      if (cell.style && cell.style.cssText) cellAttrs += ` style="${cell.style.cssText}"`;
      tableHTML += `<${tag}${cellAttrs} class="${cellClass.trim()}">${cellContent}</${tag}>`;
    });
    tableHTML += '</tr>';
  });

  tableHTML += '</table></div>';
  return tableHTML;
}

function getAllTablesContent() {
  const terms = ['prelim', 'midterm', 'prefinal', 'final'];
  let content = '';
  terms.forEach((term, index) => {
    if (index > 0) content += '<div class="page-break"></div>';
    content += getPrintTableContent(term);
  });
  content += '<div class="page-break"></div>';
  content += getPrintTableContent('combined');
  content += '<div class="page-break"></div>';
  content += getPassFailContent();
  content += '<div class="page-break"></div>';
  content += getCourseOutcomeSummaryContent();
  return content;
}

function getPassFailContent() {
  const passFailTable = document.querySelector('#passfail-table table');
  if (!passFailTable) return '<p>No Pass/Fail analysis data available.</p>';

  let content = '<div class="term-section">';
  content += '<h3 class="term-title">Pass/Fail Analysis Summary</h3>';
  content += '<table class="print-table">';

  const rows = passFailTable.querySelectorAll('tr');
  rows.forEach((row) => {
    const isHeader = row.closest('thead') !== null;
    const tag = isHeader ? 'th' : 'td';
    content += '<tr>';
    const cells = row.querySelectorAll(isHeader ? 'th' : 'td');
    cells.forEach((cell) => {
      let cellContent = cell.textContent.trim();
      let cellClass = '';
      let cellAttrs = '';
      if (cell.hasAttribute('colspan')) cellAttrs += ` colspan="${cell.getAttribute('colspan')}"`;
      if (cell.hasAttribute('rowspan')) cellAttrs += ` rowspan="${cell.getAttribute('rowspan')}"`;
      if (cell.classList.contains('table-success')) cellClass += ' table-success';
      if (cell.classList.contains('text-center')) cellClass += ' text-center';
      if (cell.classList.contains('fw-bold')) cellClass += ' fw-bold';
      if (cell.classList.contains('bg-light')) cellClass += ' bg-light';
      content += `<${tag}${cellAttrs} class="${cellClass.trim()}">${cellContent}</${tag}>`;
    });
    content += '</tr>';
  });

  content += '</table></div>';
  return content;
}

function getCourseOutcomeSummaryContent(termType = 'combined') {
  const selector = termType === 'combined' ? '#copasssummary-table table' : `#summary-term-${termType} table`;
  const summaryTable = document.querySelector(selector);
  if (!summaryTable) return '<p>No Course Outcomes Summary data available.</p>';

  const termLabel = TERM_LABELS[termType] || termType;
  const sectionTitle =
    termType === 'combined'
      ? 'Course Outcomes Summary Dashboard'
      : `${termLabel} Term - Course Outcomes Summary Dashboard`;

  let content = '<div class="term-section">';
  content += `<h3 class="term-title">${sectionTitle}</h3>`;
  content += '<table class="print-table">';

  const rows = summaryTable.querySelectorAll('tr');
  rows.forEach((row) => {
    const isHeader = row.closest('thead') !== null;
    const tag = isHeader ? 'th' : 'td';
    content += '<tr>';
    const cells = row.querySelectorAll(isHeader ? 'th' : 'td');
    cells.forEach((cell) => {
      let cellContent = cell.textContent.trim();
      let cellClass = '';
      let cellAttrs = '';
      if (cell.hasAttribute('colspan')) cellAttrs += ` colspan="${cell.getAttribute('colspan')}"`;
      if (cell.hasAttribute('rowspan')) cellAttrs += ` rowspan="${cell.getAttribute('rowspan')}"`;
      if (cell.classList.contains('table-success')) cellClass += ' table-success';
      if (cell.classList.contains('text-center')) cellClass += ' text-center';
      if (cell.classList.contains('fw-bold')) cellClass += ' fw-bold';
      if (cell.classList.contains('bg-light')) cellClass += ' bg-light';
      if (cell.classList.contains('average-cell')) cellClass += ' average-cell';
      if (cellContent.includes('%')) cellClass += ' percentage-value';
      content += `<${tag}${cellAttrs} class="${cellClass.trim()}">${cellContent}</${tag}>`;
    });
    content += '</tr>';
  });

  content += '</table></div>';
  return content;
}

function dismissWarning() {
  const warningAlert = document.querySelector('.alert-warning');
  if (!warningAlert) return;
  warningAlert.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
  warningAlert.style.opacity = '0';
  warningAlert.style.transform = 'translateY(-10px)';
  setTimeout(() => {
    warningAlert.style.display = 'none';
  }, 300);
}

function refreshData() {
  const refreshButton = document.querySelector('button[onclick="refreshData()"]');
  if (!refreshButton) return;
  const originalHTML = refreshButton.innerHTML;
  refreshButton.innerHTML = '<i class="bi bi-arrow-clockwise me-2 spin"></i>Refreshing...';
  refreshButton.disabled = true;
  const style = document.createElement('style');
  style.textContent = `.spin { animation: spin 1s linear infinite; } @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }`;
  document.head.appendChild(style);
  setTimeout(() => window.location.reload(), 1000);
}

export function initCourseOutcomeResultsPage() {
  if (!isCourseOutcomeResultsPage()) return;

  // Expose remaining functions for inline handlers in Blade
  Object.assign(window, {
    setDisplayType,
    toggleScoreType,
    toggleScoreTypeWithValue,
    switchTerm,
    showAllTerms,
    coPrintTable,
    coPrintSpecificTable,
    dismissWarning,
    refreshData,
  });

  toggleScoreType();
  document.querySelectorAll('.term-step').forEach((step) => {
    step.addEventListener('click', () => {
      setTimeout(() => {
        const tableContainer = document.querySelector('.results-card:not([style*="display: none"])');
        if (tableContainer) {
          tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
    });
  });

  const viewQuery = new URLSearchParams(window.location.search).get('view');
  const validViews = ['score', 'percentage', 'passfail', 'copasssummary'];
  const requestedView = validViews.includes(viewQuery) ? viewQuery : 'percentage';

  const viewLabelMap = {
    score: 'Scores',
    percentage: 'Percentage',
    passfail: 'Pass/Fail',
    copasssummary: 'Summary',
  };

  setDisplayType(requestedView, requestedView, viewLabelMap[requestedView]);
  initTargetLevelEditor();
}

document.addEventListener('DOMContentLoaded', initCourseOutcomeResultsPage);

// Expose globally for initPage registry
window.initCourseOutcomeResultsPage = initCourseOutcomeResultsPage;
