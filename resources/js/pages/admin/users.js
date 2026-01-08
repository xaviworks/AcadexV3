/**
 * Admin Users Page JavaScript
 * Handles user management functionality including:
 * - Enable/disable users
 * - Password validation
 * - User creation with duplicate checking
 * - Session management
 * - DataTables initialization
 */

// Make togglePasswordVisibility globally available
window.togglePasswordVisibility = function (inputId) {
  const input = document.getElementById(inputId);
  const button =
    inputId === 'password'
      ? document.getElementById('togglePassword')
      : document.getElementById('togglePasswordConfirmation');
  const icon = button?.querySelector('i');

  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye', 'bi-eye');
    icon.classList.add('fa-eye-slash', 'bi-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash', 'bi-eye-slash');
    icon.classList.add('fa-eye', 'bi-eye');
  }
};

/**
 * Enable a disabled user account
 */
window.enableUser = async function (userId, userName) {
  const confirmed = await window.confirm.ask({
    title: 'Re-enable Account?',
    message: `Are you sure you want to re-enable ${userName}?`,
    confirmText: 'Yes, Re-enable',
    cancelText: 'Cancel',
    type: 'info',
  });

  if (!confirmed) return;

  loading.start('enableUser');
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  fetch(`/admin/users/${userId}/enable`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': token,
    },
  })
    .then((response) => response.json())
    .then((data) => {
      loading.stop('enableUser');
      if (data.success) {
        // Store message in sessionStorage to show after reload
        sessionStorage.setItem('userActionMessage', data.message);
        sessionStorage.setItem('userActionType', 'success');
        location.reload();
      } else {
        notify.error(data.message || 'Failed to re-enable user');
      }
    })
    .catch((err) => {
      loading.stop('enableUser');
      console.error(err);
      notify.error('Failed to re-enable user');
    });
};

/**
 * Validate user creation form
 */
window.validateForm = function () {
  const form = document.getElementById('user-form');
  if (!form) return false;

  const password = form.querySelector('input[name="password"]')?.value;
  const confirmPassword = form.querySelector('input[name="password_confirmation"]')?.value;
  const firstName = form.querySelector('input[name="first_name"]')?.value;
  const lastName = form.querySelector('input[name="last_name"]')?.value;
  const email = form.querySelector('input[name="email"]')?.value;
  const role = form.querySelector('select[name="role"]')?.value;
  const departmentId = form.querySelector('select[name="department_id"]')?.value;
  const courseId = form.querySelector('select[name="course_id"]')?.value;

  // Check if required fields are filled
  const missingFields = [];
  if (!firstName) missingFields.push('First Name');
  if (!lastName) missingFields.push('Last Name');
  if (!email) missingFields.push('Email Username');
  if (!role) missingFields.push('User Role');

  // Only validate department and course if not Admin or VPAA
  if (role !== '3' && role !== '5') {
    if (!departmentId) missingFields.push('Department');
    // Only require course for Chairperson role
    if (role === '1' && !courseId) missingFields.push('Course');
  }

  if (!password) missingFields.push('Password');
  if (!confirmPassword) missingFields.push('Confirm Password');

  if (missingFields.length > 0) {
    notify.warning(`Please fill in the following fields: ${missingFields.join(', ')}`);
    return false;
  }

  // Validate email format (no @ or domain)
  if (email.includes('@')) {
    notify.error('Please enter only your username without @ or domain.');
    return false;
  }

  // Check password requirements
  const hasMinLength = password.length >= 8;
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

  if (!(hasMinLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecial)) {
    let missingRequirements = [];
    if (!hasMinLength) missingRequirements.push('Minimum 8 characters');
    if (!hasUpperCase || !hasLowerCase) missingRequirements.push('Both uppercase and lowercase letters');
    if (!hasNumber) missingRequirements.push('At least one number');
    if (!hasSpecial) missingRequirements.push('At least one special character');

    notify.error(`Password requirements not met: ${missingRequirements.join(', ')}`);
    return false;
  }

  // Check if passwords match
  if (password !== confirmPassword) {
    notify.error('Passwords do not match. Please try again.');
    return false;
  }

  return true;
};

/**
 * Open add user modal
 */
window.openModal = function () {
  const modalEl = document.getElementById('courseModal');
  if (modalEl) {
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
  }
};

/**
 * Close add user modal
 */
window.closeModal = function () {
  const modalEl = document.getElementById('courseModal');
  if (modalEl) {
    const bsModal = bootstrap.Modal.getInstance(modalEl);
    if (bsModal) bsModal.hide();
  }
};

/**
 * Open confirmation modal after validating form
 */
window.openConfirmModal = function () {
  if (validateForm()) {
    // Check for duplicate user
    const firstName = document.querySelector('input[name="first_name"]')?.value;
    const lastName = document.querySelector('input[name="last_name"]')?.value;
    const email = document.querySelector('input[name="email"]')?.value;

    fetch(
      `/api/check-duplicate-name?first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&email=${encodeURIComponent(email)}`
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.exists) {
          notify.error('A user with this name or email already exists in the system.');
        } else {
          // Proceed with confirmation modal if no duplicate
          const modalEl = document.getElementById('confirmModal');
          if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
          }
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        // Proceed with confirmation modal if check fails
        const modalEl = document.getElementById('confirmModal');
        if (modalEl) {
          const bsModal = new bootstrap.Modal(modalEl);
          bsModal.show();
        }
      });
  }
};

/**
 * Close confirmation modal
 */
window.closeConfirmModal = function () {
  const modalEl = document.getElementById('confirmModal');
  if (modalEl) {
    const bsModal = bootstrap.Modal.getInstance(modalEl);
    if (bsModal) bsModal.hide();
  }
};

/**
 * Check password requirements and update UI
 */
window.checkPassword = function (password) {
  const checks = {
    length: password.length >= 8,
    number: /[0-9]/.test(password),
    case: /[a-z]/.test(password) && /[A-Z]/.test(password),
    special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
  };

  const update = (id, valid) => {
    const el = document.getElementById(`circle-${id}`);
    if (!el) return;
    el.classList.remove('bg-danger', 'bg-success', 'bg-secondary');
    el.classList.add(valid ? 'bg-success' : 'bg-danger');
  };

  update('length', checks.length);
  update('number', checks.number);
  update('case', checks.case);
  update('special', checks.special);

  const requirementsBox = document.getElementById('password-requirements');
  const allValid = Object.values(checks).every(Boolean);
  if (requirementsBox) {
    requirementsBox.classList.toggle('d-none', allValid);
  }
};

/**
 * Submit user form
 */
window.submitUserForm = function () {
  const form = document.getElementById('user-form');
  if (form) form.submit();
};

/**
 * Initialize admin users page functionality
 */
function initAdminUsersPage() {
  // Duration option handlers for disable modal
  document.addEventListener('change', function (e) {
    if (e.target && e.target.name === 'duration_option') {
      const hiddenInput = document.getElementById('chooseDisableDuration');
      if (hiddenInput) hiddenInput.value = e.target.value;

      const customWrapper = document.getElementById('customDatetimeWrapper');
      const customInput = document.getElementById('customDisableDatetime');

      if (e.target.value === 'custom') {
        if (customWrapper) customWrapper.classList.add('show');
        if (customInput) customInput.required = true;
      } else {
        if (customWrapper) customWrapper.classList.remove('show');
        if (customInput) customInput.required = false;
      }
    }
  });

  // Card click handler for disable option selection
  document.addEventListener('click', function (e) {
    const card = e.target.closest('.disable-option-card');
    if (!card) return;

    const value = card.dataset.value;
    const radios = document.getElementsByName('duration_option');
    Array.from(radios).forEach((r) => (r.checked = r.value === value));

    // Toggle active class
    document.querySelectorAll('.disable-option-card').forEach((c) => c.classList.remove('active'));
    card.classList.add('active');

    // Trigger change event for radio
    const event = new Event('change', { bubbles: true });
    const selectedRadio = Array.from(radios).find((r) => r.value === value);
    if (selectedRadio) selectedRadio.dispatchEvent(event);
  });

  // Disable form AJAX submission
  const chooseDisableForm = document.getElementById('chooseDisableForm');
  if (chooseDisableForm) {
    chooseDisableForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const form = e.target;
      const selected = document.querySelector('input[name="duration_option"]:checked');

      if (!selected) {
        alert('Please select a duration.');
        return;
      }

      const duration = selected.value;
      const formData = new FormData();
      formData.append('duration', duration);

      const tokenInput = document.querySelector('input[name="_token"]');
      if (tokenInput) formData.append('_token', tokenInput.value);

      // Start loading state
      loading.start('disableUser');
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      if (duration === 'custom') {
        const customVal = document.getElementById('customDisableDatetime')?.value;
        if (!customVal) {
          notify.warning('Please select a custom date and time.');
          loading.stop('disableUser');
          if (submitBtn) submitBtn.disabled = false;
          return;
        }
        formData.append('custom_disable_datetime', customVal);
      }

      fetch(form.action, {
        method: 'POST',
        body: formData,
      })
        .then(async (response) => {
          let parsed = null;
          const contentType = response.headers.get('content-type') || '';
          if (contentType.includes('application/json')) {
            parsed = await response.json();
          }
          if (!response.ok) {
            const errMsg =
              parsed?.message || parsed || (await response.text().catch(() => null)) || response.statusText;
            throw errMsg;
          }
          return parsed;
        })
        .then((data) => {
          if (data && data.success) {
            modal.close();
            // Store message in sessionStorage to show after reload
            sessionStorage.setItem('userActionMessage', data.message);
            sessionStorage.setItem('userActionType', 'success');
            location.reload();
          } else {
            throw data?.message || 'Failed to disable user.';
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          const message =
            typeof error === 'string' ? error : error?.message || 'An error occurred while disabling the user.';
          notify.error(message);
        })
        .finally(() => {
          loading.stop('disableUser');
          if (submitBtn) submitBtn.disabled = false;
        });
    });
  }

  // Role change handler for user form
  const roleInput = document.querySelector('select[name="role"]');
  const departmentInput = document.querySelector('select[name="department_id"]');
  const courseInput = document.querySelector('select[name="course_id"]');
  const courseWrapper = document.getElementById('course-wrapper');
  const departmentWrapper = document.getElementById('department-wrapper');

  if (roleInput && departmentInput && courseInput && courseWrapper) {
    // Initially hide course wrapper
    courseWrapper.classList.add('d-none');

    // Role change handler
    roleInput.addEventListener('change', function () {
      if (roleInput.value == '3' || roleInput.value == '5') {
        // Admin or VPAA role
        departmentInput.value = '';
        courseInput.value = '';
        courseWrapper.classList.add('d-none');
        if (departmentWrapper) departmentWrapper.classList.add('d-none');
        courseInput.removeAttribute('required');
      } else if (roleInput.value == '2') {
        // Dean role
        departmentInput.value = '';
        courseInput.value = '';
        courseWrapper.classList.add('d-none');
        if (departmentWrapper) departmentWrapper.classList.remove('d-none');
        courseInput.removeAttribute('required');
      } else if (roleInput.value == '1') {
        // Chairperson role
        departmentInput.value = '';
        courseInput.value = '';
        courseWrapper.classList.remove('d-none');
        if (departmentWrapper) departmentWrapper.classList.remove('d-none');
        courseInput.setAttribute('required', 'required');
      }

      departmentInput.dispatchEvent(new Event('change'));
    });

    // Department change handler
    departmentInput.addEventListener('change', function () {
      const deptId = this.value;

      if (roleInput.value == '3' || roleInput.value == '5' || roleInput.value == '2') {
        courseWrapper.classList.add('d-none');
        if (roleInput.value == '3' || roleInput.value == '5') {
          if (departmentWrapper) departmentWrapper.classList.add('d-none');
        }
        return;
      }

      if (!deptId) {
        courseWrapper.classList.add('d-none');
        courseInput.innerHTML = '<option value="">-- Choose Course --</option>';
        return;
      }

      courseWrapper.classList.remove('d-none');
      courseInput.innerHTML = '<option value="">Loading...</option>';

      fetch(`/api/department/${deptId}/courses`)
        .then((response) => response.json())
        .then((data) => {
          if (data.length === 0) {
            courseInput.innerHTML = '<option value="">No courses available</option>';
            return;
          }

          if (data.length === 1) {
            courseInput.innerHTML = `<option value="${data[0].id}" selected>${data[0].name}</option>`;
            courseWrapper.classList.remove('d-none');
          } else {
            courseInput.innerHTML = '<option value="">-- Choose Course --</option>';
            data.forEach((course) => {
              courseInput.innerHTML += `<option value="${course.id}">${course.name}</option>`;
            });
            courseWrapper.classList.remove('d-none');
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          courseInput.innerHTML = '<option value="">Error loading courses</option>';
        });
    });

    // Initialize if department is pre-selected
    if (departmentInput.value) {
      departmentInput.dispatchEvent(new Event('change'));
    }
  }

  // Email validation
  const emailInput = document.querySelector('input[name="email"]');
  const emailWarning = document.getElementById('email-warning');

  if (emailInput && emailWarning) {
    emailInput.addEventListener('input', function () {
      if (this.value.includes('@')) {
        emailWarning.classList.remove('d-none');
        this.classList.add('is-invalid');
      } else {
        emailWarning.classList.add('d-none');
        this.classList.remove('is-invalid');
      }
    });
  }

  // Password visibility toggle
  const passwordField = document.getElementById('password');
  const confirmPasswordField = document.getElementById('password_confirmation');
  const togglePassword = document.getElementById('togglePassword');
  const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');

  if (togglePassword && passwordField) {
    togglePassword.addEventListener('click', function () {
      const icon = this.querySelector('i');
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon?.classList.remove('bi-eye');
        icon?.classList.add('bi-eye-slash');
      } else {
        passwordField.type = 'password';
        icon?.classList.remove('bi-eye-slash');
        icon?.classList.add('bi-eye');
      }
    });
  }

  if (togglePasswordConfirmation && confirmPasswordField) {
    togglePasswordConfirmation.addEventListener('click', function () {
      const icon = this.querySelector('i');
      if (confirmPasswordField.type === 'password') {
        confirmPasswordField.type = 'text';
        icon?.classList.remove('bi-eye');
        icon?.classList.add('bi-eye-slash');
      } else {
        confirmPasswordField.type = 'password';
        icon?.classList.remove('bi-eye-slash');
        icon?.classList.add('bi-eye');
      }
    });
  }

  // Confirm form submission
  const confirmForm = document.getElementById('confirm-form');
  if (confirmForm) {
    confirmForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      fetch(this.action || window.confirmUserCreationUrl, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': token,
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            closeConfirmModal();
            notify.success('Password verified. Creating user...');
            setTimeout(() => submitUserForm(), 500);
          } else {
            notify.error(data.message || 'Invalid password. Please try again.');
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          notify.error('There was an error processing your request. Please try again.');
        });
    });
  }

  // Load session counts
  const sessionBadges = document.querySelectorAll('.session-count');
  sessionBadges.forEach((badge) => {
    const userId = badge.dataset.userId;

    fetch(`/admin/users/${userId}/session-count`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const count = data.count;
          badge.innerHTML = `<i class="bi bi-circle-fill"></i> ${count} active`;

          badge.classList.remove('bg-info', 'bg-success', 'bg-warning', 'bg-secondary');
          if (count === 0) {
            badge.classList.add('bg-secondary');
          } else if (count === 1) {
            badge.classList.add('bg-success');
          } else {
            badge.classList.add('bg-warning');
          }
        }
      })
      .catch((error) => {
        console.error('Error fetching session count:', error);
        badge.innerHTML = '<i class="bi bi-x-circle"></i> Error';
        badge.classList.remove('bg-info');
        badge.classList.add('bg-danger');
      });
  });

  // Force logout buttons
  const forceLogoutButtons = document.querySelectorAll('.force-logout-btn');
  forceLogoutButtons.forEach((button) => {
    button.addEventListener('click', async function () {
      const userId = this.dataset.userId;
      const userName = this.dataset.userName;

      const confirmed = await window.confirm.ask({
        title: 'Force Logout User?',
        message: `Are you sure you want to log out ${userName} from all devices? This will end all their active sessions immediately.`,
        confirmText: 'Yes, Force Logout',
        type: 'danger',
      });

      if (!confirmed) return;

      loading.start('forceLogout');
      button.disabled = true;
      const originalHTML = button.innerHTML;
      button.innerHTML = '<i class="bi bi-hourglass-split"></i> Logging out...';

      const token = document.querySelector('meta[name="csrf-token"]')?.content;

      fetch(`/admin/users/${userId}/force-logout`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            notify.success(data.message);

            const sessionBadge = document.querySelector(`.session-count[data-user-id="${userId}"]`);
            if (sessionBadge) {
              sessionBadge.innerHTML = '<i class="bi bi-circle-fill"></i> 0 active';
              sessionBadge.classList.remove('bg-info', 'bg-success', 'bg-warning');
              sessionBadge.classList.add('bg-secondary');
            }
          } else {
            notify.error(data.message);
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          notify.error('Failed to force logout user. Please try again.');
        })
        .finally(() => {
          loading.stop('forceLogout');
          button.disabled = false;
          button.innerHTML = originalHTML;
        });
    });
  });

  // Initialize DataTables
  if (typeof $ !== 'undefined' && $.fn.DataTable) {
    $('#usersTable').DataTable({
      order: [
        [1, 'asc'],
        [0, 'asc'],
      ],
      language: {
        search: '_INPUT_',
        searchPlaceholder: 'Search users...',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ users',
        emptyTable: 'No users found',
      },
      columnDefs: [{ orderable: false, targets: 3 }],
    });
  }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  // Check for stored messages from page reload
  const storedMessage = sessionStorage.getItem('userActionMessage');
  const messageType = sessionStorage.getItem('userActionType');

  if (storedMessage) {
    // Display the message
    if (messageType === 'success') {
      notify.success(storedMessage);
    } else if (messageType === 'error') {
      notify.error(storedMessage);
    }

    // Clear the stored message
    sessionStorage.removeItem('userActionMessage');
    sessionStorage.removeItem('userActionType');
  }

  // Initialize the rest of the page
  initAdminUsersPage();
});

// Export for module usage
export { initAdminUsersPage };
