/**
 * Admin Grades Formula Form Page JavaScript
 * Handles Alpine.js structured formula editor and password modal
 */

// Register Alpine.js component when Alpine initializes
document.addEventListener('alpine:init', () => {
  Alpine.data('structuredFormulaEditor', ({ initial, catalog }) => ({
    structureType: initial?.type ?? 'lecture_only',
    catalog: catalog ?? {},
    structure: null,
    init() {
      this.catalog = this.catalog ?? {};
      this.loadStructure(initial);
    },
    loadStructure(payload) {
      const template = payload?.structure ??
        this.catalog[this.structureType]?.structure ?? {
          key: 'period_grade',
          label: 'Period Grade',
          type: 'composite',
          children: [],
        };

      this.structure = this.decorateNode(this.cloneStructure(template), true, 100);
      this.syncTotals();
    },
    switchStructure() {
      this.loadStructure({ type: this.structureType, structure: this.catalog[this.structureType]?.structure });
    },
    decorateNode(node, isRoot = false, parentOverall = 100) {
      node = node ?? {};
      node.type = node.type ?? ((node.children ?? []).length ? 'composite' : 'activity');
      node.label = node.label ?? this.titleCase(node.key ?? 'component');
      node.weight_percent = isRoot ? 100 : Number(node.weight_percent ?? (node.weight ?? 0) * 100);
      node.max_assessments = node.max_assessments ?? null;
      node.uid = this.generateUid();
      node.overall_percent = isRoot ? 100 : Number(((parentOverall ?? 0) * (node.weight_percent ?? 0)) / 100);
      node.children = (node.children ?? []).map((child) => this.decorateNode(child, false, node.overall_percent));
      return node;
    },
    orderedNodes() {
      const items = [];
      if (!this.structure) {
        return items;
      }
      let index = 0;
      const walk = (parent, depth = 0) => {
        (parent.children ?? []).forEach((child) => {
          items.push({ ref: child, parent, depth, index: index++ });
          walk(child, depth + 1);
        });
      };
      walk(this.structure, 0);
      return items;
    },
    syncWeight(node) {
      const numeric = Number(node.weight_percent);
      node.weight_percent = Number.isFinite(numeric) ? Math.max(0, Math.min(100, numeric)) : 0;
      this.syncTotals();
    },
    updateMaxAssessments(node) {
      const numeric = Number(node.max_assessments);
      if (Number.isFinite(numeric) && numeric >= 1 && numeric <= 5) {
        node.max_assessments = Math.round(numeric);
      } else if (node.max_assessments === '' || node.max_assessments === null) {
        node.max_assessments = null;
      } else {
        node.max_assessments = Math.max(1, Math.min(5, Math.round(numeric)));
      }
    },
    syncTotals() {
      this.collectCompositeNodes().forEach((composite) => {
        composite.total_percent = (composite.children ?? []).reduce(
          (sum, child) => sum + Number(child.weight_percent ?? 0),
          0
        );
      });
      this.recalculateOverall();
    },
    collectCompositeNodes() {
      const nodes = [];
      const walk = (item) => {
        if (item && (item.children ?? []).length) {
          nodes.push(item);
          item.children.forEach(walk);
        }
      };
      if (this.structure) {
        walk(this.structure);
      }
      return nodes;
    },
    compositeWarning(node) {
      return Math.abs(Number(node.total_percent ?? 0) - 100) > 0.1;
    },
    structureIsBalanced() {
      return this.collectCompositeNodes().every((node) => !this.compositeWarning(node));
    },
    formIsComplete() {
      const required = this.$el.querySelectorAll('[required]');
      return Array.from(required).every((field) => {
        if (field.type === 'number') {
          return field.value !== '' && Number.isFinite(parseFloat(field.value));
        }
        return field.value.trim() !== '';
      });
    },
    formIsValid() {
      return this.formIsComplete() && this.structureIsBalanced();
    },
    handleSubmit(event) {
      const banner = this.$el.querySelector('.validation-error');
      if (!this.formIsValid()) {
        event.preventDefault();
        if (banner) {
          banner.classList.remove('d-none');
          banner.textContent = 'Please complete all required fields and ensure each component group totals 100%.';
        }
      } else if (banner) {
        banner.classList.add('d-none');
      }
    },
    serializeStructure() {
      const clone = this.cloneStructure(this.structure);
      this.stripRuntimeFields(clone, true);
      return JSON.stringify(clone);
    },
    stripRuntimeFields(node, isRoot = false) {
      if (!node) {
        return;
      }
      delete node.uid;
      delete node.total_percent;
      delete node.overall_percent;
      if (!isRoot) {
        node.weight_percent = Number(node.weight_percent ?? 0);
        delete node.weight;
      }
      if (node.children) {
        node.children = node.children.map((child) => {
          this.stripRuntimeFields(child, false);
          return child;
        });
      }
    },
    cloneStructure(value) {
      return JSON.parse(JSON.stringify(value ?? {}));
    },
    generateUid() {
      return window.crypto?.randomUUID?.() ?? Math.random().toString(36).slice(2);
    },
    isComposite(node) {
      return (node.type ?? '') === 'composite';
    },
    formatPercent(value) {
      return `${Number(value ?? 0).toFixed(1)}%`;
    },
    displayMaxAssessments(node) {
      return node.max_assessments ? node.max_assessments : 'Flexible';
    },
    titleCase(value) {
      return (value ?? '')
        .toString()
        .replace(/[._]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/\b\w/g, (match) => match.toUpperCase());
    },
    recalculateOverall() {
      const assign = (node, parentOverall = 100) => {
        if (!node) {
          return;
        }
        node.overall_percent = parentOverall;
        (node.children ?? []).forEach((child) => {
          const childRelative = Number(child.weight_percent ?? 0);
          const childOverall = (parentOverall * childRelative) / 100;
          assign(child, childOverall);
        });
      };
      if (this.structure) {
        assign(this.structure, 100);
      }
    },
  }));
});

export function initGradesFormulaForm() {
  const form = document.getElementById('gradesFormulaEditorForm');
  if (!form || form.dataset.requiresPassword !== '1') {
    return;
  }

  const hiddenField = document.getElementById('formulaCurrentPasswordField');
  const modalElement = document.getElementById('formulaPasswordModal');
  const confirmBtn = document.getElementById('confirmFormulaPasswordBtn');
  const modalCtor = window.bootstrap && typeof window.bootstrap.Modal === 'function' ? window.bootstrap.Modal : null;

  if (!hiddenField) {
    return;
  }

  if (!modalElement || !confirmBtn || !modalCtor) {
    form.addEventListener('submit', (event) => {
      if (form.dataset.passwordBypass === '1' || event.defaultPrevented) {
        return;
      }

      event.preventDefault();
      const response = window.prompt('Enter your password to confirm this change:');
      if (!response || !response.trim()) {
        return;
      }

      hiddenField.value = response.trim();
      form.dataset.passwordError = '0';
      form.dataset.passwordErrorMessage = '';
      form.dataset.passwordBypass = '1';
      form.requestSubmit();
      setTimeout(() => {
        delete form.dataset.passwordBypass;
        hiddenField.value = '';
      }, 0);
    });
    return;
  }

  const passwordInput = document.getElementById('formulaPasswordInput');
  const inlineError = document.getElementById('formulaPasswordInlineError');
  const serverError = document.getElementById('formulaPasswordServerError');
  const modal = modalCtor.getOrCreateInstance(modalElement);

  const resetInlineError = () => {
    if (inlineError) {
      inlineError.textContent = '';
      inlineError.classList.remove('d-block');
    }
    if (passwordInput) {
      passwordInput.classList.remove('is-invalid');
    }
  };

  const showInlineError = (message) => {
    if (!inlineError) {
      return;
    }
    inlineError.textContent = message;
    inlineError.classList.add('d-block');
    if (passwordInput) {
      passwordInput.classList.add('is-invalid');
    }
  };

  const setServerError = (message) => {
    if (!serverError) {
      return;
    }
    if (message) {
      serverError.textContent = message;
      serverError.classList.remove('d-none');
    } else {
      serverError.textContent = '';
      serverError.classList.add('d-none');
    }
  };

  const getServerErrorMessage = () => form.dataset.passwordErrorMessage || '';

  setServerError(getServerErrorMessage());

  const openModal = () => {
    resetInlineError();
    if (passwordInput) {
      passwordInput.value = '';
      passwordInput.focus();
    }
    setServerError(getServerErrorMessage());
    modal.show();
  };

  form.addEventListener('submit', (event) => {
    if (form.dataset.passwordBypass === '1') {
      return;
    }

    if (event.defaultPrevented) {
      return;
    }

    event.preventDefault();
    openModal();
  });

  confirmBtn.addEventListener('click', () => {
    const password = passwordInput ? passwordInput.value.trim() : '';
    resetInlineError();

    if (!password) {
      showInlineError('Password is required.');
      if (passwordInput) {
        passwordInput.focus();
      }
      return;
    }

    hiddenField.value = password;
    form.dataset.passwordError = '0';
    form.dataset.passwordErrorMessage = '';
    setServerError('');
    form.dataset.passwordBypass = '1';
    modal.hide();

    setTimeout(() => {
      form.requestSubmit();
      setTimeout(() => {
        delete form.dataset.passwordBypass;
        hiddenField.value = '';
      }, 0);
    }, 150);
  });

  modalElement.addEventListener('hidden.bs.modal', () => {
    if (form.dataset.passwordBypass === '1') {
      return;
    }

    if (hiddenField) {
      hiddenField.value = '';
    }
    resetInlineError();
  });

  if (form.dataset.passwordError === '1' && getServerErrorMessage()) {
    setTimeout(() => {
      modal.show();
      if (passwordInput) {
        passwordInput.focus();
      }
    }, 200);
  }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initGradesFormulaForm);

// Expose function globally
window.initGradesFormulaForm = initGradesFormulaForm;
