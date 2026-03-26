import 'bootstrap/dist/css/bootstrap.min.css';
// Bootstrap Icons loaded via CDN for better caching
import '@fortawesome/fontawesome-free/css/all.min.css';

import './bootstrap';
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import { getDeviceFingerprint } from './fingerprint';
import './stores'; // Initialize Alpine stores
import './store-helpers'; // Global helper functions

// Import page-specific scripts (auto-initialize on DOMContentLoaded)
import './pages/index.js';

// Register Alpine plugins
Alpine.plugin(intersect);

window.Alpine = Alpine;

Alpine.start();

// Initialize device fingerprinting on auth screens and login links.
document.addEventListener('DOMContentLoaded', () => {
  const loginForms = document.querySelectorAll('form[action*="login"], form[action*="two-factor"]');
  const fingerprintLinks = document.querySelectorAll('[data-device-fingerprint-link]');

  if (loginForms.length === 0 && fingerprintLinks.length === 0) {
    return;
  }

  const fingerprintPromise = getDeviceFingerprint().catch(() => '');
  let resolvedFingerprint = '';
  let fingerprintResolved = false;

  const resolveFingerprint = async () => {
    if (!fingerprintResolved) {
      resolvedFingerprint = (await fingerprintPromise) || '';
      fingerprintResolved = true;
    }

    return resolvedFingerprint;
  };

  const updateFingerprintHref = (link, fingerprint) => {
    const baseHref = link.getAttribute('data-base-href') || link.getAttribute('href');
    if (!baseHref) {
      return;
    }

    const url = new URL(baseHref, window.location.origin);
    if (fingerprint) {
      url.searchParams.set('device_fingerprint', fingerprint);
    }
    link.setAttribute('href', url.toString());
  };

  const ensureFingerprintInput = (form) => {
    let input = form.querySelector('input[name="device_fingerprint"]');
    if (!input) {
      input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'device_fingerprint';
      form.appendChild(input);
    }

    return input;
  };

  loginForms.forEach((form) => {
    const input = ensureFingerprintInput(form);

    resolveFingerprint().then((fingerprint) => {
      input.value = fingerprint;
      form.dataset.fingerprintReady = 'true';
    });

    form.addEventListener('submit', async (event) => {
      if (form.dataset.fingerprintReady === 'true') {
        input.value = resolvedFingerprint;
        return;
      }

      event.preventDefault();

      if (form.dataset.fingerprintSubmitting === 'true') {
        return;
      }

      form.dataset.fingerprintSubmitting = 'true';

      try {
        input.value = await resolveFingerprint();
        form.dataset.fingerprintReady = 'true';

        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit(event.submitter);
        } else {
          HTMLFormElement.prototype.submit.call(form);
        }
      } finally {
        form.dataset.fingerprintSubmitting = 'false';
      }
    });
  });

  fingerprintLinks.forEach((link) => {
    resolveFingerprint().then((fingerprint) => {
      updateFingerprintHref(link, fingerprint);
      link.dataset.fingerprintReady = 'true';
    });

    link.addEventListener('click', async (event) => {
      if (link.dataset.fingerprintReady === 'true') {
        updateFingerprintHref(link, resolvedFingerprint);
        return;
      }

      event.preventDefault();

      const fingerprint = await resolveFingerprint();
      updateFingerprintHref(link, fingerprint);
      link.dataset.fingerprintReady = 'true';
      window.location.assign(link.getAttribute('href') || link.getAttribute('data-base-href') || window.location.href);
    });
  });
});
