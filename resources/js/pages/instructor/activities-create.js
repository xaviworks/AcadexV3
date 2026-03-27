/**
 * Instructor Activities Create Page JavaScript
 * Handles tooltips, delete modal, and form validation
 */

export function initActivitiesCreatePage() {
  // Initialize Bootstrap tooltips
  const bootstrapLib = window.bootstrap ?? null;
  if (bootstrapLib) {
    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach((triggerEl) => new bootstrapLib.Tooltip(triggerEl));
  }

  // Delete modal handler
  const deleteModal = document.getElementById('confirmDeleteModal');
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const activityId = button?.getAttribute('data-activity-id');
      const activityTitle = button?.getAttribute('data-activity-title') ?? 'this activity';

      const form = deleteModal.querySelector('#deleteActivityForm');
      if (form && activityId) {
        form.action = `/instructor/activities/${activityId}`;
      }

      const placeholder = deleteModal.querySelector('#activityTitlePlaceholder');
      if (placeholder) {
        placeholder.textContent = activityTitle;
      }
    });
  }

  // Create modal auto-focus
  const createActivityModal = document.getElementById('createActivityModal');
  if (createActivityModal) {
    createActivityModal.addEventListener('shown.bs.modal', () => {
      const firstInput = createActivityModal.querySelector('input[name="title"]');
      if (firstInput) {
        firstInput.focus();
      }
    });
  }

  initializeCreateModalComponentGuard();

  // Form validation
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach((form) => {
    form.addEventListener(
      'submit',
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      },
      false
    );
  });

  // Add hover effects to table rows
  const tableRows = document.querySelectorAll('table tbody tr');
  tableRows.forEach((row) => {
    row.addEventListener('mouseenter', () => {
      row.style.backgroundColor = '#f8f9fa';
    });
    row.addEventListener('mouseleave', () => {
      row.style.backgroundColor = '';
    });
  });
}

function initializeCreateModalComponentGuard() {
  const modal = document.getElementById('createActivityModal');
  if (!modal) {
    return;
  }

  const form = modal.querySelector('form');
  const typeSelect = modal.querySelector('[data-component-type-select]');
  const termSelect = modal.querySelector('select[name="term"]');
  const subjectSelect = modal.querySelector('select[name="subject_id"]');
  const notice = modal.querySelector('[data-component-notice]');
  const emptyAlert = modal.querySelector('[data-component-empty]');
  const saveButton = modal.querySelector('[data-component-save]');

  if (!form || !typeSelect || !termSelect || !saveButton) {
    return;
  }

  const optionsByTerm = JSON.parse(form.dataset.componentOptionsByTerm ?? '{}');
  const optionsSubjectId = (form.dataset.componentOptionsSubjectId ?? '').toString();
  const fallbackOptions = Array.from(typeSelect.children).map((child) => child.cloneNode(true));

  const termOptionsForCurrentContext = () => {
    const selectedSubjectId = (subjectSelect?.value ?? optionsSubjectId).toString();
    if (optionsSubjectId !== '' && selectedSubjectId !== optionsSubjectId) {
      return [];
    }

    const term = termSelect.value;
    if (!term || !Object.prototype.hasOwnProperty.call(optionsByTerm, term)) {
      return [];
    }

    return Array.isArray(optionsByTerm[term]) ? optionsByTerm[term] : [];
  };

  const restoreFallbackOptions = () => {
    typeSelect.innerHTML = '';
    fallbackOptions.forEach((optionNode) => {
      typeSelect.appendChild(optionNode.cloneNode(true));
    });
  };

  const renderComponentOptions = (componentOptions) => {
    if (!componentOptions.length) {
      restoreFallbackOptions();
      return;
    }

    const previousValue = typeSelect.value;
    typeSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Select Component';
    typeSelect.appendChild(placeholder);

    componentOptions.forEach((component) => {
      const option = document.createElement('option');
      const max = component.max === null || component.max === undefined ? '' : String(component.max);
      const available = Number(component.available ?? 0);
      const count = Number(component.count ?? 0);
      const isDisabled = max !== '' && available <= 0;

      option.value = component.value;
      option.disabled = isDisabled;
      option.textContent = `${component.label} — ${component.helper}`;
      option.dataset.label = component.label;
      option.dataset.count = String(count);
      option.dataset.max = max;
      option.dataset.available = String(available);
      option.dataset.status = component.status ?? 'ok';

      typeSelect.appendChild(option);
    });

    const previousOption = typeSelect.querySelector(`option[value="${CSS.escape(previousValue)}"]`);
    if (previousValue && previousOption && !previousOption.disabled) {
      typeSelect.value = previousValue;
    }
  };

  const hasAvailableOptions = () => {
    return Array.from(typeSelect.options).some((option) => option.value && !option.disabled);
  };

  const renderNotice = () => {
    if (!notice) {
      return;
    }

    const option = typeSelect.options[typeSelect.selectedIndex];
    if (!option || !option.value) {
      notice.classList.add('d-none');
      notice.textContent = '';
      notice.classList.remove('text-success', 'text-warning', 'text-danger');
      return;
    }

    const label = option.dataset.label || option.textContent.trim();
    const count = option.dataset.count ?? '0';
    const max = option.dataset.max && option.dataset.max !== '' ? option.dataset.max : '∞';
    const available = option.dataset.available ?? '';
    const status = option.dataset.status || 'ok';

    let helper = `${label} · ${count}/${max} used`;
    if (available !== '' && max !== '∞') {
      helper += ` · ${available} slot${available === '1' ? '' : 's'} left`;
    }

    notice.textContent = helper;
    notice.classList.remove('d-none');
    notice.classList.toggle('text-success', status === 'ok');
    notice.classList.toggle('text-warning', status === 'missing');
    notice.classList.toggle('text-danger', status === 'full');
  };

  const updateSaveState = () => {
    const option = typeSelect.options[typeSelect.selectedIndex];
    const optionAvailable = option && option.dataset
      ? parseInt(option.dataset.available ?? '1', 10)
      : 1;

    if (!hasAvailableOptions()) {
      saveButton.disabled = true;
    } else if (!typeSelect.value) {
      saveButton.disabled = true;
    } else if (!Number.isNaN(optionAvailable) && optionAvailable <= 0) {
      saveButton.disabled = true;
    } else {
      saveButton.disabled = false;
    }

    if (emptyAlert) {
      emptyAlert.classList.toggle('d-none', hasAvailableOptions());
    }
  };

  const syncComponentOptions = () => {
    const options = termOptionsForCurrentContext();
    renderComponentOptions(options);
    renderNotice();
    updateSaveState();
  };

  typeSelect.addEventListener('change', () => {
    renderNotice();
    updateSaveState();
  });

  termSelect.addEventListener('change', syncComponentOptions);

  if (subjectSelect) {
    subjectSelect.addEventListener('change', syncComponentOptions);
  }

  modal.addEventListener('shown.bs.modal', syncComponentOptions);

  syncComponentOptions();
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (
    document.querySelector('[data-page="instructor-activities-create"]') ||
    window.location.pathname.includes('/instructor/activities')
  ) {
    initActivitiesCreatePage();
  }
});

window.initActivitiesCreatePage = initActivitiesCreatePage;
