/**
 * Select Academic Period Page Scripts
 *
 * JavaScript functionality for the academic period selection page
 * including custom year dropdown and form validation.
 */

document.addEventListener('DOMContentLoaded', function () {
  // Custom dropdown elements
  const yearDropdown = document.getElementById('yearDropdown');
  const yearDropdownBtn = document.getElementById('yearDropdownBtn');
  const yearDropdownMenu = document.getElementById('yearDropdownMenu');
  const dropdownItems = document.querySelectorAll('.dropdown-item');

  // Semester elements
  const semesterCards = document.querySelectorAll('.semester-card');
  const submitBtn = document.getElementById('submitBtn');
  const radioInputs = document.querySelectorAll('input[name="academic_period_id"]');
  const noResults = document.getElementById('noResults');
  const semesterCardsContainer = document.getElementById('semesterCards');

  let selectedYear = yearDropdown?.dataset.defaultYear || '';

  /**
   * Toggle dropdown open/close
   */
  function toggleDropdown() {
    yearDropdown?.classList.toggle('open');
  }

  /**
   * Close dropdown
   */
  function closeDropdown() {
    yearDropdown?.classList.remove('open');
  }

  /**
   * Select a year from dropdown
   * @param {string} year - The year to select
   */
  function selectYear(year) {
    selectedYear = year;

    // Update button text
    const valueSpan = yearDropdownBtn?.querySelector('.dropdown-value');
    if (valueSpan) {
      valueSpan.textContent = year;
    }

    // Update selected state on items
    dropdownItems.forEach((item) => {
      item.classList.toggle('selected', item.dataset.value === year);
    });

    // Close dropdown and filter semesters
    closeDropdown();
    filterByYear(year);
  }

  /**
   * Filter semester cards by selected year
   * @param {string} selectedYear - The academic year to filter by
   */
  function filterByYear(selectedYear) {
    let visibleCount = 0;
    let firstVisibleCard = null;

    semesterCards.forEach((card) => {
      const cardYear = card.dataset.year;
      const isMatch = cardYear === selectedYear;

      card.style.display = isMatch ? 'flex' : 'none';

      if (isMatch) {
        visibleCount++;
        if (!firstVisibleCard) {
          firstVisibleCard = card;
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

    // Auto-select first visible semester if current selection is hidden
    const currentSelected = document.querySelector('input[name="academic_period_id"]:checked');
    if (currentSelected) {
      const parentCard = currentSelected.closest('.semester-card');
      if (parentCard && parentCard.style.display === 'none') {
        // Current selection is hidden, select first visible
        if (firstVisibleCard) {
          const firstVisibleRadio = firstVisibleCard.querySelector('input[name="academic_period_id"]');
          if (firstVisibleRadio) {
            firstVisibleRadio.checked = true;
          }
        }
      }
    } else if (firstVisibleCard) {
      // No selection, select first visible
      const firstVisibleRadio = firstVisibleCard.querySelector('input[name="academic_period_id"]');
      if (firstVisibleRadio) {
        firstVisibleRadio.checked = true;
      }
    }

    // Update submit button state
    updateSubmitButton();
  }

  /**
   * Update submit button enabled/disabled state
   */
  function updateSubmitButton() {
    const checkedRadio = document.querySelector('input[name="academic_period_id"]:checked');
    if (submitBtn) {
      const parentCard = checkedRadio?.closest('.semester-card');
      const isVisible = parentCard && parentCard.style.display !== 'none';
      submitBtn.disabled = !checkedRadio || !isVisible;
    }
  }

  // Custom dropdown events
  if (yearDropdownBtn) {
    yearDropdownBtn.addEventListener('click', function (e) {
      e.preventDefault();
      toggleDropdown();
    });
  }

  // Dropdown item click
  dropdownItems.forEach((item) => {
    item.addEventListener('click', function () {
      selectYear(this.dataset.value);
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    if (yearDropdown && !yearDropdown.contains(e.target)) {
      closeDropdown();
    }
  });

  // Close dropdown on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeDropdown();
    }
  });

  // Initialize with default year
  if (selectedYear) {
    filterByYear(selectedYear);
  }

  // Enable submit button when radio is selected
  radioInputs.forEach((radio) => {
    radio.addEventListener('change', function () {
      updateSubmitButton();
    });
  });

  // Initial check for pre-selected radio
  updateSubmitButton();
});
