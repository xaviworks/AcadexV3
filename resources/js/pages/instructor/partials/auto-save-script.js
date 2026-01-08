/**
 * Auto-Save Script JavaScript for Instructor Scores
 * Handles AJAX score saving with keyboard navigation
 */

export function initAutoSaveScript(options = {}) {
  // Get route from page data or options
  const pageData = window.pageData || {};
  const saveScoreUrl = pageData.saveScoreUrl || options.saveScoreUrl || '/grades/ajax-save-score';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const inputs = Array.from(document.querySelectorAll('.grade-input'));

  if (!inputs.length) return;

  // Auto-save on change
  inputs.forEach((input) => {
    input.addEventListener('change', function () {
      const studentId = this.dataset.student;
      const activityId = this.dataset.activity;
      const subjectIdEl = document.querySelector('input[name="subject_id"]');
      const termEl = document.querySelector('input[name="term"]');

      if (!subjectIdEl || !termEl) {
        console.error('Missing subject_id or term input');
        return;
      }

      const subjectId = subjectIdEl.value;
      const term = termEl.value;
      const score = this.value;

      fetch(saveScoreUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          student_id: studentId,
          activity_id: activityId,
          subject_id: subjectId,
          term: term,
          score: score,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status !== 'success') {
            const errorMsg = data?.message || 'Failed to save score.';
            if (typeof window.notify !== 'undefined' && window.notify.error) {
              window.notify.error(errorMsg);
            } else {
              alert(errorMsg);
            }
          }
        })
        .catch((error) => {
          const errorMsg = error?.message || 'Error saving score.';
          if (typeof window.notify !== 'undefined' && window.notify.error) {
            window.notify.error(errorMsg);
          } else {
            alert(errorMsg);
          }
        });
    });
  });

  // Keyboard navigation (Tab/Enter)
  const inputGrid = {};
  inputs.forEach((input) => {
    const student = input.dataset.student;
    const activity = input.dataset.activity;
    if (!inputGrid[activity]) inputGrid[activity] = {};
    inputGrid[activity][student] = input;
  });

  const activityIds = Object.keys(inputGrid);
  const studentIds = [...new Set(inputs.map((i) => i.dataset.student))];

  const sequence = [];
  activityIds.forEach((activityId) => {
    studentIds.forEach((studentId) => {
      const el = inputGrid[activityId]?.[studentId];
      if (el) sequence.push(el);
    });
  });

  sequence.forEach((input, idx) => {
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Tab' || e.key === 'Enter') {
        e.preventDefault();
        const next = sequence[idx + 1];
        if (next) {
          next.focus();
          next.select();
        }
      }
    });
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  // Only initialize if we have grade inputs and pageData with saveScoreUrl
  const hasGradeInputs = document.querySelectorAll('.grade-input').length > 0;
  const hasPageData = typeof window.pageData !== 'undefined' && window.pageData.saveScoreUrl;

  if (hasGradeInputs && hasPageData) {
    initAutoSaveScript();
  }
});

window.initAutoSaveScript = initAutoSaveScript;
