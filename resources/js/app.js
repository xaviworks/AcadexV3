import 'bootstrap/dist/css/bootstrap.min.css';
// Bootstrap Icons loaded via CDN for better caching
import '@fortawesome/fontawesome-free/css/all.min.css';

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

// Initialize device fingerprinting on login page
document.addEventListener('DOMContentLoaded', async () => {
  // Target both login and 2FA forms
  const loginForms = document.querySelectorAll('form[action*="login"], form[action*="two-factor"]');

  if (loginForms.length > 0) {
    // Get fingerprint immediately
    const fingerprint = await getDeviceFingerprint();

    loginForms.forEach((form) => {
      // Check if input already exists
      let input = form.querySelector('input[name="device_fingerprint"]');
      if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'device_fingerprint';
        form.appendChild(input);
      }
      input.value = fingerprint;

      // Also add to form submit handler to ensure it's always set
      form.addEventListener('submit', function (e) {
        input.value = fingerprint;
      });
    });
  }
});
