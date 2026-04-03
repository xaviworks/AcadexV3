/**
 * Chairperson Reports Co-Program Page JavaScript
 * Handles PLO definition and CO-to-PLO mapping interactions.
 */

export function initChairpersonCoProgramPage() {
  const workspaceElement = document.getElementById('coProgramPloWorkspace');
  const rowsContainer = document.getElementById('ploDefinitionRows');
  const addButton = document.getElementById('addPloRowButton');
  const template = document.getElementById('ploDefinitionRowTemplate');
  const matrixSubjectFilter = document.getElementById('poMatrixSubjectFilter');
  const matrixSearchInput = document.getElementById('poMatrixSearch');
  const matrixStateFilter = document.getElementById('poMatrixStateFilter');
  const matrixClearButton = document.getElementById('poMatrixClearFilters');
  const matrixExpandButton = document.getElementById('poMatrixExpandToggle');
  const matrixContextChip = document.getElementById('poMatrixContextChip');
  const matrixWrap = document.querySelector('.po-matrix-wrap');
  const matrixResizeHandle = document.getElementById('poMatrixResizeHandle');
  const matrixResizeValue = document.getElementById('poMatrixResizeValue');
  const matrixRows = Array.from(document.querySelectorAll('.po-matrix-data-row'));
  const matrixGroupRows = Array.from(document.querySelectorAll('.po-matrix-group-row'));
  const matrixInputs = Array.from(document.querySelectorAll('.po-matrix-input'));
  const matrixSubjectJumpButtons = Array.from(document.querySelectorAll('.po-matrix-subject-jump-btn'));
  const matrixOutcomeHeaders = Array.from(document.querySelectorAll('.po-matrix-outcome-col[data-plo-key]'));
  const matrixLegendItems = Array.from(document.querySelectorAll('.po-matrix-legend-item[data-plo-key]'));
  const definitionsForm = document.getElementById('ploDefinitionsForm');
  const mappingForm = document.getElementById('ploMappingForm');
  const definitionsSaveButtons = definitionsForm
    ? Array.from(definitionsForm.querySelectorAll('button[type="submit"][data-save-scope="definitions"]'))
    : [];
  const mappingSaveButtons = mappingForm
    ? Array.from(mappingForm.querySelectorAll('button[type="submit"][data-save-scope="mapping"]'))
    : [];
  const outcomePrefix = String(workspaceElement?.dataset.outcomePrefix || 'PO');

  if (!workspaceElement || !rowsContainer || !addButton || !template) {
    return;
  }

  const matrixWorkspace = workspaceElement.querySelector('.po-matrix-workspace');
  const matrixBackdropId = 'poMatrixExpandBackdrop';
  const definitionsFieldPattern = /^plos\[\d+]\[(id|delete|code|title|is_active)]$/;
  const mappingFieldPattern = /^mappings\[\d+]\[\]$/;
  let hasShownExpandHint = false;
  let hasUserInteractedWithPage = false;
  let initialDefinitionsSnapshot = '';
  let initialMappingSnapshot = '';
  let definitionsDirtyStateReady = false;
  let mappingDirtyStateReady = false;
  let isDefinitionsDirty = false;
  let isMappingDirty = false;
  let matrixUserHeight = null;
  let matrixResizeStartY = 0;
  let matrixResizeStartHeight = 0;
  let isMatrixResizing = false;

  const serializeTrackedFormState = (form, includeKey) => {
    if (!form) {
      return '';
    }

    const entries = [];
    const formData = new FormData(form);
    formData.forEach((value, key) => {
      if (!includeKey(key)) {
        return;
      }

      entries.push([String(key), String(value)]);
    });

    entries.sort((left, right) => {
      if (left[0] === right[0]) {
        return left[1].localeCompare(right[1]);
      }

      return left[0].localeCompare(right[0]);
    });

    return JSON.stringify(entries);
  };

  const serializeDefinitionsState = () =>
    serializeTrackedFormState(definitionsForm, (key) => {
      return definitionsFieldPattern.test(String(key));
    });

  const serializeMappingState = () =>
    serializeTrackedFormState(mappingForm, (key) => {
      return mappingFieldPattern.test(String(key));
    });

  const setSaveButtonsDisabledState = (buttons, isDisabled) => {
    buttons.forEach((button) => {
      button.disabled = isDisabled;
      button.setAttribute('aria-disabled', isDisabled ? 'true' : 'false');
    });
  };

  const markUserInteraction = () => {
    hasUserInteractedWithPage = true;
  };

  const clampValue = (value, minValue, maxValue) => {
    return Math.min(Math.max(value, minValue), maxValue);
  };

  const getMatrixResizeBounds = () => {
    if (!matrixWrap) {
      return { minHeight: 220, maxHeight: 720 };
    }

    const computedStyles = window.getComputedStyle(matrixWrap);
    const minHeight = Math.max(160, Math.round(Number.parseFloat(computedStyles.minHeight) || 220));
    const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 900;
    const maxHeight = Math.max(minHeight + 80, Math.round(viewportHeight * 0.9));

    return { minHeight, maxHeight };
  };

  const updateMatrixResizeMetadata = () => {
    if (!matrixResizeHandle || !matrixWrap) {
      return;
    }

    const { minHeight, maxHeight } = getMatrixResizeBounds();
    const currentHeight = Math.round(matrixWrap.getBoundingClientRect().height);

    matrixResizeHandle.setAttribute('aria-valuemin', String(minHeight));
    matrixResizeHandle.setAttribute('aria-valuemax', String(maxHeight));
    matrixResizeHandle.setAttribute('aria-valuenow', String(currentHeight));

    if (matrixResizeValue) {
      matrixResizeValue.textContent = `${currentHeight}px`;
    }
  };

  const resetMatrixUserHeightStyles = () => {
    if (!matrixWrap) {
      return;
    }

    matrixWrap.style.removeProperty('height');
    matrixWrap.style.removeProperty('max-height');
  };

  const applyMatrixUserHeight = () => {
    if (!matrixWrap || isMatrixExpanded() || matrixUserHeight === null) {
      return;
    }

    const { minHeight, maxHeight } = getMatrixResizeBounds();
    const nextHeight = clampValue(Math.round(matrixUserHeight), minHeight, maxHeight);
    matrixUserHeight = nextHeight;
    matrixWrap.style.height = `${nextHeight}px`;
    matrixWrap.style.maxHeight = `${nextHeight}px`;
    updateMatrixResizeMetadata();
  };

  const beginMatrixResize = (event) => {
    if (!matrixWrap || !matrixResizeHandle || isMatrixExpanded()) {
      return;
    }

    if (event.button !== undefined && event.button !== 0) {
      return;
    }

    event.preventDefault();
    markUserInteraction();

    isMatrixResizing = true;
    matrixResizeStartY = event.clientY;
    matrixResizeStartHeight = matrixWrap.getBoundingClientRect().height;
    matrixResizeHandle.classList.add('is-resizing');
  };

  const continueMatrixResize = (event) => {
    if (!isMatrixResizing) {
      return;
    }

    const deltaY = event.clientY - matrixResizeStartY;
    matrixUserHeight = matrixResizeStartHeight + deltaY;
    applyMatrixUserHeight();
    event.preventDefault();
  };

  const endMatrixResize = () => {
    if (!isMatrixResizing) {
      return;
    }

    isMatrixResizing = false;
    matrixResizeHandle?.classList.remove('is-resizing');
  };

  const isDirectFieldInteraction = (event, targetElement) => {
    if (!event.isTrusted || !hasUserInteractedWithPage) {
      return false;
    }

    if (!(targetElement instanceof HTMLElement)) {
      return false;
    }

    return document.activeElement === targetElement;
  };

  const setDefinitionsDirtyState = (isDirty) => {
    isDefinitionsDirty = isDirty;
    setSaveButtonsDisabledState(definitionsSaveButtons, !isDirty);
  };

  const setMappingDirtyState = (isDirty) => {
    isMappingDirty = isDirty;
    setSaveButtonsDisabledState(mappingSaveButtons, !isDirty);
  };

  const initializeDefinitionsDirtyState = () => {
    initialDefinitionsSnapshot = serializeDefinitionsState();
    definitionsDirtyStateReady = true;
    setDefinitionsDirtyState(false);
  };

  const initializeMappingDirtyState = () => {
    initialMappingSnapshot = serializeMappingState();
    mappingDirtyStateReady = true;
    setMappingDirtyState(false);
  };

  const refreshDefinitionsDirtyState = () => {
    if (!definitionsForm || !definitionsDirtyStateReady) {
      return;
    }

    const isDirty = serializeDefinitionsState() !== initialDefinitionsSnapshot;
    setDefinitionsDirtyState(isDirty);
  };

  const refreshMappingDirtyState = () => {
    if (!mappingForm || !mappingDirtyStateReady) {
      return;
    }

    const isDirty = serializeMappingState() !== initialMappingSnapshot;
    setMappingDirtyState(isDirty);
  };

  const removeMatrixBackdrop = () => {
    const existingBackdrop = document.getElementById(matrixBackdropId);
    if (!existingBackdrop) {
      return;
    }

    existingBackdrop.classList.remove('is-visible');

    const removeAfterTransition = () => {
      existingBackdrop.remove();
    };

    existingBackdrop.addEventListener('transitionend', removeAfterTransition, { once: true });
    window.setTimeout(removeAfterTransition, 220);
  };

  const createMatrixBackdrop = () => {
    if (document.getElementById(matrixBackdropId)) {
      return;
    }

    const backdrop = document.createElement('div');
    backdrop.id = matrixBackdropId;
    backdrop.className = 'po-matrix-expand-backdrop';
    backdrop.addEventListener('click', function () {
      setMatrixExpandedState(false, { silent: true });
    });

    document.body.appendChild(backdrop);
    requestAnimationFrame(function () {
      backdrop.classList.add('is-visible');
    });
  };

  const isMatrixExpanded = () => Boolean(matrixWorkspace && matrixWorkspace.classList.contains('is-table-expanded'));

  const setMatrixExpandedState = (isExpanded, options = {}) => {
    if (!matrixWorkspace || !matrixExpandButton) {
      return;
    }

    const wasExpanded = matrixWorkspace.classList.contains('is-table-expanded');

    matrixWorkspace.classList.toggle('is-table-expanded', isExpanded);
    matrixExpandButton.setAttribute('aria-pressed', isExpanded ? 'true' : 'false');
    document.body.classList.toggle('po-matrix-expanded-lock', isExpanded);

    if (isExpanded) {
      endMatrixResize();
      resetMatrixUserHeightStyles();
    }

    if (isExpanded) {
      createMatrixBackdrop();
    } else {
      removeMatrixBackdrop();
      applyMatrixUserHeight();
    }

    const label = matrixExpandButton.querySelector('.po-matrix-expand-label');
    const icon = matrixExpandButton.querySelector('i');
    const defaultLabel = matrixExpandButton.dataset.defaultLabel || 'Expand table';
    const expandedLabel = matrixExpandButton.dataset.expandedLabel || 'Exit expanded table view';

    if (label) {
      label.textContent = isExpanded ? expandedLabel : defaultLabel;
    }

    if (icon) {
      icon.classList.toggle('bi-arrows-angle-expand', !isExpanded);
      icon.classList.toggle('bi-arrows-angle-contract', isExpanded);
    }

    if (isExpanded && !wasExpanded && !options.silent && !hasShownExpandHint) {
      hasShownExpandHint = true;
      window.notify?.info('Press "Esc" to exit expanded table view');
    }
  };

  if (matrixExpandButton && matrixWorkspace) {
    matrixExpandButton.addEventListener('click', function () {
      setMatrixExpandedState(!isMatrixExpanded());
    });
  }

  if (matrixResizeHandle && matrixWrap) {
    matrixResizeHandle.addEventListener('pointerdown', beginMatrixResize);
    matrixResizeHandle.addEventListener('keydown', function (event) {
      if (isMatrixExpanded()) {
        return;
      }

      const step = event.shiftKey ? 56 : 28;
      const { minHeight, maxHeight } = getMatrixResizeBounds();

      if (event.key === 'ArrowUp') {
        event.preventDefault();
        matrixUserHeight = (matrixUserHeight ?? matrixWrap.getBoundingClientRect().height) - step;
        applyMatrixUserHeight();
        return;
      }

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        matrixUserHeight = (matrixUserHeight ?? matrixWrap.getBoundingClientRect().height) + step;
        applyMatrixUserHeight();
        return;
      }

      if (event.key === 'Home') {
        event.preventDefault();
        matrixUserHeight = minHeight;
        applyMatrixUserHeight();
        return;
      }

      if (event.key === 'End') {
        event.preventDefault();
        matrixUserHeight = maxHeight;
        applyMatrixUserHeight();
      }
    });
  }

  document.addEventListener('pointermove', continueMatrixResize, true);
  document.addEventListener('pointerup', endMatrixResize, true);
  document.addEventListener('pointercancel', endMatrixResize, true);
  window.addEventListener('resize', function () {
    if (!isMatrixExpanded()) {
      applyMatrixUserHeight();
    }

    updateMatrixResizeMetadata();
  });

  document.addEventListener('pointerdown', markUserInteraction, true);
  document.addEventListener('keydown', markUserInteraction, true);

  document.addEventListener(
    'keydown',
    function (event) {
      if (event.key !== 'Escape') {
        return;
      }

      if (!isMatrixExpanded()) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      setMatrixExpandedState(false, { silent: true });
    },
    true
  );

  const tabButtons = Array.from(document.querySelectorAll('#ploConfigTabs [data-bs-toggle="tab"]'));
  tabButtons.forEach((tabButton) => {
    tabButton.addEventListener('shown.bs.tab', function (event) {
      const selectedTarget = event.target ? event.target.getAttribute('data-bs-target') : '';
      if (selectedTarget !== '#plo-mapping-panel') {
        setMatrixExpandedState(false, { silent: true });
      }
    });
  });

  const getVisibleRows = () =>
    Array.from(rowsContainer.querySelectorAll('.plo-definition-row')).filter(
      (row) => !row.classList.contains('d-none')
    );

  const escapedPrefix = String(outcomePrefix).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const codePattern = new RegExp(`^${escapedPrefix}(\\d{2})$`);

  const getUsedNumbers = () =>
    getVisibleRows()
      .map((row) => row.querySelector('input[name$="[code]"]'))
      .filter(Boolean)
      .map((input) => {
        const match = (input.value || '').toUpperCase().match(codePattern);
        return match ? Number(match[1]) : null;
      })
      .filter((value) => Number.isInteger(value));

  const nextPloNumber = () => {
    const used = new Set(getUsedNumbers());
    for (let i = 1; i <= 20; i += 1) {
      if (!used.has(i)) {
        return i;
      }
    }

    return null;
  };

  const refreshAddButtonState = () => {
    addButton.disabled = getVisibleRows().length >= 20;
  };

  addButton.addEventListener('click', function () {
    const nextNumber = nextPloNumber();
    if (nextNumber === null) {
      refreshAddButtonState();
      return;
    }

    const index = rowsContainer.querySelectorAll('.plo-definition-row').length;
    const paddedNumber = String(nextNumber).padStart(2, '0');
    const generatedCode = `${outcomePrefix}${paddedNumber}`;
    const html = template.innerHTML
      .replaceAll('__INDEX__', String(index))
      .replaceAll('__NUMBER__', paddedNumber)
      .replaceAll('__CODE__', generatedCode);

    rowsContainer.insertAdjacentHTML('beforeend', html);
    refreshAddButtonState();
    refreshDefinitionsDirtyState();
  });

  rowsContainer.addEventListener('click', function (event) {
    const removeButton = event.target.closest('.remove-plo-row');
    if (!removeButton) {
      return;
    }

    const row = removeButton.closest('.plo-definition-row');
    if (!row) {
      return;
    }

    const idInput = row.querySelector('input[name$="[id]"]');
    const deleteInput = row.querySelector('.plo-delete-input');

    if (idInput && idInput.value) {
      if (deleteInput) {
        deleteInput.value = '1';
      }
      row.classList.add('d-none');
    } else {
      row.remove();
    }

    refreshAddButtonState();
    refreshDefinitionsDirtyState();
  });

  if (definitionsForm) {
    const handleDefinitionsDirtyEvent = (event) => {
      const eventTarget = event.target;
      if (!(eventTarget instanceof Element)) {
        return;
      }

      if (!isDirectFieldInteraction(event, eventTarget)) {
        return;
      }

      const fieldName = eventTarget.getAttribute('name') || '';
      if (!definitionsFieldPattern.test(fieldName)) {
        return;
      }

      const isCheckboxField = eventTarget instanceof HTMLInputElement && eventTarget.type === 'checkbox';
      if (isCheckboxField && event.type !== 'change') {
        return;
      }

      if (!isCheckboxField && event.type !== 'input') {
        return;
      }

      refreshDefinitionsDirtyState();
    };

    definitionsForm.addEventListener('input', handleDefinitionsDirtyEvent);
    definitionsForm.addEventListener('change', handleDefinitionsDirtyEvent);
    definitionsForm.addEventListener('submit', function (event) {
      if (isDefinitionsDirty) {
        return;
      }

      event.preventDefault();
    });
  }

  if (mappingForm) {
    mappingForm.addEventListener('submit', function (event) {
      if (isMappingDirty) {
        return;
      }

      event.preventDefault();
    });
  }

  const normalize = (value) =>
    String(value || '')
      .trim()
      .toLowerCase();

  const setPloHighlight = (ploKey, shouldHighlight) => {
    const normalizedKey = normalize(ploKey);

    matrixOutcomeHeaders.forEach((header) => {
      const isMatch = normalize(header.dataset.ploKey) === normalizedKey;
      header.classList.toggle('is-highlighted', shouldHighlight && isMatch);
    });

    matrixLegendItems.forEach((item) => {
      const isMatch = normalize(item.dataset.ploKey) === normalizedKey;
      item.classList.toggle('is-highlighted', shouldHighlight && isMatch);
    });
  };

  const updateMappedRowState = () => {
    matrixRows.forEach((row) => {
      const checkedCount = row.querySelectorAll('.po-matrix-input:checked').length;
      const mappedState = checkedCount > 0 ? 'mapped' : 'unmapped';
      row.dataset.rowMapped = mappedState;
      row.classList.toggle('is-mapped', mappedState === 'mapped');
    });
  };

  const getVisibleMatrixRows = () => matrixRows.filter((row) => !row.classList.contains('d-none'));

  const focusMatrixInput = (targetRowIndex, targetColIndex) => {
    const visibleRows = getVisibleMatrixRows();
    if (visibleRows.length === 0) {
      return;
    }

    const boundedRowIndex = Math.min(Math.max(targetRowIndex, 0), visibleRows.length - 1);
    const rowInputs = Array.from(visibleRows[boundedRowIndex].querySelectorAll('.po-matrix-input'));
    if (rowInputs.length === 0) {
      return;
    }

    const boundedColIndex = Math.min(Math.max(targetColIndex, 0), rowInputs.length - 1);
    rowInputs[boundedColIndex].focus();
  };

  const initializeMatrixNavigation = () => {
    matrixRows.forEach((row) => {
      const rowInputs = Array.from(row.querySelectorAll('.po-matrix-input'));
      rowInputs.forEach((input, columnIndex) => {
        input.dataset.matrixColIndex = String(columnIndex);
      });
    });
  };

  const updateSubjectJumpState = () => {
    if (matrixSubjectJumpButtons.length === 0) {
      return;
    }

    const selectedSubject = normalize(matrixSubjectFilter ? matrixSubjectFilter.value : '');
    const visibleCountsBySubject = matrixRows.reduce((carry, row) => {
      if (row.classList.contains('d-none')) {
        return carry;
      }

      const subjectKey = normalize(row.dataset.subject);
      carry[subjectKey] = (carry[subjectKey] || 0) + 1;
      return carry;
    }, {});
    const visibleTotal = Object.values(visibleCountsBySubject).reduce((total, count) => total + Number(count), 0);

    matrixSubjectJumpButtons.forEach((button) => {
      const buttonSubject = normalize(button.dataset.subject);
      const isActive = buttonSubject === selectedSubject;
      const totalCountRaw = Number.parseInt(button.dataset.totalCount || '0', 10);
      const totalCount = Number.isNaN(totalCountRaw) ? (buttonSubject === '' ? matrixRows.length : 0) : totalCountRaw;
      const visibleCount = buttonSubject === '' ? visibleTotal : Number(visibleCountsBySubject[buttonSubject] || 0);
      const countElement = button.querySelector('.po-matrix-subject-jump-count');

      if (countElement) {
        countElement.textContent = visibleCount === totalCount ? String(totalCount) : `${visibleCount}/${totalCount}`;
      }

      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      button.disabled = totalCount <= 0;
    });
  };

  const jumpToSubject = (subjectValue) => {
    const selectedSubject = normalize(subjectValue);

    if (matrixSubjectFilter) {
      matrixSubjectFilter.value = selectedSubject;
    }

    applyMatrixFilters();

    if (!matrixWrap) {
      return;
    }

    if (selectedSubject === '') {
      matrixWrap.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    const targetGroupRow = matrixGroupRows.find((groupRow) => {
      return !groupRow.classList.contains('d-none') && normalize(groupRow.dataset.subject) === selectedSubject;
    });

    if (targetGroupRow) {
      matrixWrap.scrollTo({
        top: Math.max(0, targetGroupRow.offsetTop - 8),
        behavior: 'smooth',
      });
    }
  };

  const applyMatrixFilters = () => {
    if (matrixRows.length === 0) {
      return;
    }

    updateMappedRowState();

    const selectedSubject = normalize(matrixSubjectFilter ? matrixSubjectFilter.value : '');
    const searchValue = normalize(matrixSearchInput ? matrixSearchInput.value : '');
    const selectedState = matrixStateFilter ? matrixStateFilter.value : 'all';
    const visibleBySubject = new Map();

    matrixRows.forEach((row) => {
      const rowSubject = normalize(row.dataset.subject);
      const rowSearch = normalize(row.dataset.search);
      const rowState = row.dataset.rowMapped || 'unmapped';
      const matchesSubject = selectedSubject === '' || rowSubject === selectedSubject;
      const matchesSearch = searchValue === '' || rowSearch.includes(searchValue);
      const matchesState = selectedState === 'all' || rowState === selectedState;
      const isVisible = matchesSubject && matchesSearch && matchesState;

      row.classList.toggle('d-none', !isVisible);

      const subjectKey = row.dataset.subject || '';
      if (!visibleBySubject.has(subjectKey)) {
        visibleBySubject.set(subjectKey, false);
      }

      if (isVisible) {
        visibleBySubject.set(subjectKey, true);
      }
    });

    const visibleGroupRows = [];
    matrixGroupRows.forEach((groupRow) => {
      const subjectKey = groupRow.dataset.subject || '';
      const isVisible = visibleBySubject.get(subjectKey) === true;
      groupRow.classList.toggle('d-none', !isVisible);
      groupRow.classList.remove('is-first-visible');

      if (isVisible) {
        visibleGroupRows.push(groupRow);
      }
    });

    if (visibleGroupRows.length > 0) {
      visibleGroupRows[0].classList.add('is-first-visible');
    }

    if (matrixContextChip) {
      const visibleRows = matrixRows.filter((row) => !row.classList.contains('d-none'));
      const firstVisibleRow = visibleRows[0];
      matrixContextChip.textContent = firstVisibleRow
        ? `Viewing: ${
            firstVisibleRow.dataset.subjectLabel || firstVisibleRow.dataset.subject || 'All subjects'
          } (${visibleRows.length}/${matrixRows.length})`
        : 'Viewing: No matching course outcomes';
    }

    updateSubjectJumpState();

    const activeElement = document.activeElement;
    if (activeElement && activeElement.classList && activeElement.classList.contains('po-matrix-input')) {
      const activeRow = activeElement.closest('.po-matrix-data-row');
      if (activeRow && activeRow.classList.contains('d-none')) {
        const firstVisibleInput = matrixRows
          .find((row) => !row.classList.contains('d-none'))
          ?.querySelector('.po-matrix-input');

        if (firstVisibleInput) {
          firstVisibleInput.focus();
        }
      }
    }
  };

  if (matrixSubjectFilter) {
    matrixSubjectFilter.addEventListener('change', applyMatrixFilters);
  }

  if (matrixSearchInput) {
    matrixSearchInput.addEventListener('input', applyMatrixFilters);
  }

  if (matrixStateFilter) {
    matrixStateFilter.addEventListener('change', applyMatrixFilters);
  }

  if (matrixClearButton) {
    matrixClearButton.addEventListener('click', function () {
      if (matrixSubjectFilter) {
        matrixSubjectFilter.value = '';
      }

      if (matrixSearchInput) {
        matrixSearchInput.value = '';
      }

      if (matrixStateFilter) {
        matrixStateFilter.value = 'all';
      }

      applyMatrixFilters();
    });
  }

  matrixSubjectJumpButtons.forEach((button) => {
    button.addEventListener('click', function () {
      jumpToSubject(button.dataset.subject || '');
    });
  });

  matrixInputs.forEach((input) => {
    input.addEventListener('change', function (event) {
      if (!isDirectFieldInteraction(event, input)) {
        return;
      }

      applyMatrixFilters();
      refreshMappingDirtyState();
    });

    input.addEventListener('keydown', function (event) {
      const allowedKeys = new Set(['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Home', 'End']);
      if (!allowedKeys.has(event.key)) {
        return;
      }

      const currentRow = input.closest('.po-matrix-data-row');
      if (!currentRow) {
        return;
      }

      const visibleRows = getVisibleMatrixRows();
      const currentVisibleRowIndex = visibleRows.indexOf(currentRow);
      if (currentVisibleRowIndex === -1) {
        return;
      }

      const currentColumnIndex = Number.parseInt(input.dataset.matrixColIndex || '0', 10);
      const rowInputs = Array.from(currentRow.querySelectorAll('.po-matrix-input'));
      let targetRowIndex = currentVisibleRowIndex;
      let targetColumnIndex = Number.isNaN(currentColumnIndex) ? 0 : currentColumnIndex;

      switch (event.key) {
        case 'ArrowRight':
          targetColumnIndex += 1;
          break;
        case 'ArrowLeft':
          targetColumnIndex -= 1;
          break;
        case 'ArrowDown':
          targetRowIndex += 1;
          break;
        case 'ArrowUp':
          targetRowIndex -= 1;
          break;
        case 'Home':
          targetColumnIndex = 0;
          break;
        case 'End':
          targetColumnIndex = rowInputs.length - 1;
          break;
        default:
          return;
      }

      event.preventDefault();
      focusMatrixInput(targetRowIndex, targetColumnIndex);
    });
  });

  matrixLegendItems.forEach((item) => {
    const ploKey = item.dataset.ploKey;
    if (!ploKey) {
      return;
    }

    item.addEventListener('mouseenter', function () {
      setPloHighlight(ploKey, true);
    });

    item.addEventListener('mouseleave', function () {
      setPloHighlight(ploKey, false);
    });

    item.addEventListener('focus', function () {
      setPloHighlight(ploKey, true);
    });

    item.addEventListener('blur', function () {
      setPloHighlight(ploKey, false);
    });
  });

  matrixOutcomeHeaders.forEach((header) => {
    const ploKey = header.dataset.ploKey;
    if (!ploKey) {
      return;
    }

    header.addEventListener('mouseenter', function () {
      setPloHighlight(ploKey, true);
    });

    header.addEventListener('mouseleave', function () {
      setPloHighlight(ploKey, false);
    });
  });

  setSaveButtonsDisabledState(definitionsSaveButtons, true);
  setSaveButtonsDisabledState(mappingSaveButtons, true);
  initializeMatrixNavigation();
  refreshAddButtonState();
  applyMatrixFilters();
  initializeDefinitionsDirtyState();
  initializeMappingDirtyState();
  updateMatrixResizeMetadata();
}

document.addEventListener('DOMContentLoaded', function () {
  initChairpersonCoProgramPage();
});

window.initChairpersonCoProgramPage = initChairpersonCoProgramPage;
