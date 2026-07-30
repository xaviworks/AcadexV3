const JSON_HEADERS = {
  Accept: 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
};

function notify(type, message) {
  const method = type === 'danger' ? 'error' : type;
  if (window.notify && typeof window.notify[method] === 'function') {
    window.notify[method](message);
    return;
  }

  if (message) {
    window.alert(message);
  }
}

function getSubmitButton(form, submitter = null) {
  if (submitter && submitter.matches?.('button, input[type="submit"]')) {
    return submitter;
  }

  return form.querySelector('button[type="submit"], input[type="submit"]');
}

function setButtonLoading(button, loadingText = 'Processing...') {
  if (!button) return;

  if (!button.dataset.defaultHtml) {
    button.dataset.defaultHtml = button.innerHTML;
  }

  button.disabled = true;
  button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${loadingText}`;
}

function restoreButton(button) {
  if (!button) return;

  button.disabled = false;
  if (button.dataset.defaultHtml) {
    button.innerHTML = button.dataset.defaultHtml;
  }
}

function closeModal(form) {
  const modalId = form.dataset.closeModal;
  const modalEl = modalId
    ? document.getElementById(modalId.replace(/^#/, ''))
    : form.closest('.modal');

  if (modalEl && window.bootstrap?.Modal) {
    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
  }

  if (window.modal?.close && form.closest('[x-data], [x-show]')) {
    window.modal.close();
  }
}

function clearErrors(form) {
  form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
  form.querySelectorAll('[data-ajax-error], .ajax-validation-errors').forEach((node) => node.remove());
}

function renderValidationErrors(form, errors) {
  clearErrors(form);

  const messages = [];
  Object.entries(errors || {}).forEach(([name, fieldMessages]) => {
    const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
    const message = Array.isArray(fieldMessages) ? fieldMessages[0] : fieldMessages;

    if (message) messages.push(message);

    if (field) {
      field.classList.add('is-invalid');
      const feedback = document.createElement('div');
      feedback.className = 'invalid-feedback d-block';
      feedback.dataset.ajaxError = 'true';
      feedback.textContent = message;
      field.insertAdjacentElement('afterend', feedback);
    }
  });

  if (!messages.length) return;

  const summary = document.createElement('div');
  summary.className = 'alert alert-danger ajax-validation-errors';
  summary.textContent = messages[0];
  const body = form.querySelector('.modal-body') || form;
  body.prepend(summary);
}

function parseHtml(html) {
  return new DOMParser().parseFromString(html, 'text/html');
}

function reinitializeUi(root = document) {
  if (window.bootstrap?.Tooltip) {
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      window.bootstrap.Tooltip.getOrCreateInstance(el);
    });
  }

  document.dispatchEvent(new CustomEvent('ajax-actions:refreshed', { detail: { root } }));
}

async function replaceTargetFromResponse(targetSelector, responseText) {
  const currentTarget = document.querySelector(targetSelector);
  if (!currentTarget) return false;

  const activeTabs = Array.from(currentTarget.querySelectorAll('[data-bs-toggle="tab"].active'))
    .map((tab) => tab.getAttribute('href') || tab.dataset.bsTarget)
    .filter(Boolean);

  const doc = parseHtml(responseText);
  const nextTarget = doc.querySelector(targetSelector);
  if (!nextTarget) return false;

  currentTarget.replaceWith(nextTarget);

  activeTabs.forEach((tabSelector) => {
    const tab = Array.from(nextTarget.querySelectorAll('[data-bs-toggle="tab"]')).find(
      (candidate) => candidate.getAttribute('href') === tabSelector || candidate.dataset.bsTarget === tabSelector
    );
    const pane = nextTarget.querySelector(tabSelector);
    if (!tab || !pane) return;

    nextTarget.querySelectorAll('[data-bs-toggle="tab"]').forEach((candidate) => {
      candidate.classList.remove('active');
      candidate.setAttribute('aria-selected', 'false');
    });
    nextTarget.querySelectorAll('.tab-pane').forEach((candidate) => {
      candidate.classList.remove('active', 'show');
    });

    tab.classList.add('active');
    tab.setAttribute('aria-selected', 'true');
    pane.classList.add('active', 'show');
  });

  reinitializeUi(nextTarget);
  return true;
}

async function refreshTarget(targetSelector, refreshUrl = null) {
  if (!targetSelector) return false;

  const scrollX = window.scrollX;
  const scrollY = window.scrollY;
  const url = refreshUrl || window.location.href;

  const response = await fetch(url, {
    method: 'GET',
    headers: {
      Accept: 'text/html',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
  });

  if (!response.ok) {
    throw new Error('The section could not be refreshed.');
  }

  const html = await response.text();
  const replaced = await replaceTargetFromResponse(targetSelector, html);
  window.scrollTo(scrollX, scrollY);
  return replaced;
}

async function refreshTargetsFromForm(form, payload = {}) {
  const targets = (form.dataset.refreshTarget || '')
    .split(',')
    .map((target) => target.trim())
    .filter(Boolean);

  const refreshUrl = payload.refresh_url || payload.redirect_url || form.dataset.refreshUrl || null;
  for (const target of targets) {
    await refreshTarget(target, refreshUrl);
  }

  if (payload.redirect_url) {
    window.history.pushState({}, '', payload.redirect_url);
  }
}

function removeRows(form, payload = {}) {
  const selectors = [form.dataset.removeRow, payload.remove_row].filter(Boolean);
  selectors.forEach((selector) => {
    document.querySelectorAll(selector).forEach((row) => row.remove());
  });
}

async function parseResponse(response) {
  const contentType = response.headers.get('content-type') || '';
  if (contentType.includes('application/json')) {
    return response.json();
  }

  return {
    success: response.ok,
    html: await response.text(),
  };
}

async function submitForm(form, submitter = null) {
  if (form.dataset.processing === 'true') return null;

  const button = getSubmitButton(form, submitter);
  form.dataset.processing = 'true';
  document.body.classList.add('loaded');
  clearErrors(form);
  setButtonLoading(button, form.dataset.loadingText || 'Processing...');

  try {
    const response = await fetch(form.action, {
      method: (form.method || 'POST').toUpperCase(),
      body: new FormData(form),
      headers: JSON_HEADERS,
      credentials: 'same-origin',
    });
    const payload = await parseResponse(response);

    if (!response.ok || payload.success === false) {
      if (response.status === 422 && payload.errors) {
        renderValidationErrors(form, payload.errors);
      }

      const message = payload.message || 'The action could not be completed.';
      throw new Error(message);
    }

    removeRows(form, payload);

    if (payload.html && form.dataset.refreshTarget) {
      const target = form.dataset.refreshTarget.split(',')[0].trim();
      await replaceTargetFromResponse(target, payload.html);
    } else {
      await refreshTargetsFromForm(form, payload);
    }

    closeModal(form);
    if (form.dataset.resetOnSuccess === 'true') {
      form.reset();
    }
    notify('success', payload.message || form.dataset.successMessage || 'Action completed successfully.');
    form.dispatchEvent(new CustomEvent('ajax-actions:success', { bubbles: true, detail: payload }));
    return payload;
  } catch (error) {
    notify('error', error?.message || 'A network error occurred. Please try again.');
    form.dispatchEvent(new CustomEvent('ajax-actions:error', { bubbles: true, detail: { error } }));
    return null;
  } finally {
    form.dataset.processing = 'false';
    document.body.classList.add('loaded');
    restoreButton(button);
  }
}

function initAjaxActions() {
  if (document.documentElement.dataset.ajaxActionsBound === 'true') return;
  document.documentElement.dataset.ajaxActionsBound = 'true';

  document.addEventListener('submit', (event) => {
    const form = event.target.closest('form.ajax-action-form');
    if (!form) return;

    event.preventDefault();
    event.stopImmediatePropagation();
    document.body.classList.add('loaded');
    submitForm(form, event.submitter);
  }, true);

  document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-ajax-refresh-target]');
    if (!link) return;

    event.preventDefault();
    try {
      await refreshTarget(link.dataset.ajaxRefreshTarget, link.href);
      window.history.pushState({}, '', link.href);
    } catch (error) {
      notify('error', error?.message || 'The section could not be refreshed.');
    }
  });

  document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-ajax-pagination-target] .pagination a');
    if (!link) return;

    const container = link.closest('[data-ajax-pagination-target]');
    const target = container?.dataset.ajaxPaginationTarget;
    if (!target) return;

    event.preventDefault();
    try {
      await refreshTarget(target, link.href);
      window.history.pushState({}, '', link.href);
    } catch (error) {
      notify('error', error?.message || 'The table could not be refreshed.');
    }
  });
}

window.ajaxActions = {
  init: initAjaxActions,
  submitForm,
  refreshTarget,
  reinitializeUi,
};

document.addEventListener('DOMContentLoaded', initAjaxActions);
