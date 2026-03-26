/**
 * Guest auth password toggles for login/register pages.
 * Uses a single input per field and only activates on marked groups.
 */

function bindGuestPasswordToggle(group) {
  if (!group || group.dataset.passwordToggleBound === 'true') {
    return;
  }

  const input = group.querySelector('[data-password-toggle-input]');
  const button = group.querySelector('[data-password-toggle-button]');
  const icon = group.querySelector('[data-password-toggle-icon]');

  if (!input || !button || !icon) {
    return;
  }

  group.dataset.passwordToggleBound = 'true';

  const sync = () => {
    const hasValue = input.value.length > 0;
    const isHidden = input.type === 'password';

    if (!hasValue && !isHidden) {
      input.type = 'password';
    }

    const isCurrentlyHidden = input.type === 'password';

    button.classList.toggle('hidden', !hasValue);
    button.setAttribute('aria-label', isCurrentlyHidden ? 'Show password' : 'Hide password');
    button.setAttribute('title', isCurrentlyHidden ? 'Show password' : 'Hide password');
    button.setAttribute('aria-pressed', String(!isCurrentlyHidden));

    icon.classList.toggle('fa-eye-slash', isCurrentlyHidden);
    icon.classList.toggle('fa-eye', !isCurrentlyHidden);
  };

  button.addEventListener('mousedown', (event) => {
    event.preventDefault();
  });

  button.addEventListener('click', (event) => {
    event.preventDefault();

    if (!input.value) {
      sync();
      return;
    }

    input.type = input.type === 'password' ? 'text' : 'password';
    sync();
    input.focus({ preventScroll: true });

    if (typeof input.setSelectionRange === 'function') {
      const end = input.value.length;
      input.setSelectionRange(end, end);
    }
  });

  ['input', 'change', 'focus', 'blur'].forEach((eventName) => {
    input.addEventListener(eventName, sync);
  });

  sync();
  setTimeout(sync, 0);
  setTimeout(sync, 250);
}

export function initGuestPasswordToggles(root = document) {
  root.querySelectorAll('[data-password-toggle-group]').forEach(bindGuestPasswordToggle);
}

document.addEventListener('DOMContentLoaded', () => {
  initGuestPasswordToggles();
});

window.initGuestPasswordToggles = initGuestPasswordToggles;
