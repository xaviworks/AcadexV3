/**
 * ACADEX - Offline-Ready Application Bundle
 * All assets loaded locally for offline use
 */

// CSS Frameworks & Libraries
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import 'summernote/dist/summernote-bs5.min.css';
import '../css/fonts.css'; // Local fonts

// JavaScript Libraries - Order matters!
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap';
import 'datatables.net';
import 'datatables.net-bs5';
import Swal from 'sweetalert2';
import { Chart } from 'chart.js/auto';

// Make libraries globally available BEFORE importing Summernote
window.Swal = Swal;
window.Chart = Chart;

// Summernote depends on jQuery and Bootstrap being available globally
import 'summernote/dist/summernote-bs5.min.js';

// Alpine.js - Initialize AFTER all other libraries
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import { getDeviceFingerprint } from './fingerprint';
import './stores'; // Initialize Alpine stores
import './store-helpers'; // Global helper functions

// Import page-specific scripts (auto-initialize on DOMContentLoaded)
import './pages/index.js';

// Register Alpine plugins and start
Alpine.plugin(intersect);
window.Alpine = Alpine;
Alpine.start();

// Initialize device fingerprinting on login page
document.addEventListener('DOMContentLoaded', async () => {
  const loginForms = document.querySelectorAll('form[action*="login"]');

  if (loginForms.length > 0) {
    // Get fingerprint immediately
    const fingerprint = await getDeviceFingerprint();
    console.log('Device fingerprint generated:', fingerprint);

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
        console.log('Submitting with fingerprint:', fingerprint);
      });
    });
  }
});
