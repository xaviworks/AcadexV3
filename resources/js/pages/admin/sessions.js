/**
 * Admin Sessions Page JavaScript
 * Handles session management and user activity logs
 */

/**
 * Confirm single session revocation
 */
window.confirmRevoke = function (sessionId, userName) {
  const modalEl = document.getElementById('revokeModal');
  if (!modalEl) return;

  document.getElementById('revoke-session-id').value = sessionId;
  document.getElementById('revoke-user-name').textContent = userName;

  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();

  // Focus on password input after modal opens
  setTimeout(() => {
    const passwordInput = document.getElementById('revoke-password');
    if (passwordInput) {
      passwordInput.value = '';
      passwordInput.focus();
    }
  }, 100);
};

/**
 * Confirm all sessions revocation for a user
 */
window.confirmRevokeUser = function (userId, userName) {
  const modalEl = document.getElementById('revokeUserModal');
  if (!modalEl) return;

  document.getElementById('revoke-user-id').value = userId;
  document.getElementById('revoke-all-user-name').textContent = userName;

  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();

  // Focus on password input after modal opens
  setTimeout(() => {
    const passwordInput = document.getElementById('revoke-user-password');
    if (passwordInput) {
      passwordInput.value = '';
      passwordInput.focus();
    }
  }, 100);
};

/**
 * Confirm revocation of all sessions
 */
window.confirmRevokeAll = function () {
  const modalEl = document.getElementById('revokeAllModal');
  if (!modalEl) return;

  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();

  // Focus on password input after modal opens
  setTimeout(() => {
    const passwordInput = modalEl.querySelector('input[type="password"]');
    if (passwordInput) {
      passwordInput.focus();
    }
  }, 100);
};

/**
 * Initialize sessions page functionality
 */
function initSessionsPage() {
  // Handle tab switching from URL parameter
  const urlParams = new URLSearchParams(window.location.search);
  const activeTab = urlParams.get('tab');
  if (activeTab === 'logs') {
    const logsTabButton = document.getElementById('logs-tab');
    if (logsTabButton) {
      const logsTab = new bootstrap.Tab(logsTabButton);
      logsTab.show();
    }
  }

  // Update URL when tab is clicked
  document.querySelectorAll('#sessionTabs button[data-bs-toggle="tab"]').forEach((button) => {
    button.addEventListener('shown.bs.tab', (event) => {
      const tabId = event.target.getAttribute('id');
      const tabName = tabId.replace('-tab', '');
      const url = new URL(window.location);
      if (tabName !== 'sessions') {
        url.searchParams.set('tab', tabName);
      } else {
        url.searchParams.delete('tab');
      }
      window.history.pushState({}, '', url);
      // Ensure pagination links include the updated tab param
      updatePaginationLinksWithTab();
    });
  });

  /**
   * Update pagination links to include current tab parameter
   */
  function updatePaginationLinksWithTab() {
    const currentTabParam = new URLSearchParams(window.location.search).get('tab');
    if (!currentTabParam) return;

    document.querySelectorAll('.tab-pane').forEach((pane) => {
      const paneName = pane.id === 'logs-pane' ? 'logs' : 'sessions';
      pane.querySelectorAll('.pagination a').forEach((link) => {
        try {
          const url = new URL(link.href, window.location.origin);
          if (url.searchParams.get('tab') !== paneName) {
            url.searchParams.set('tab', paneName);
            link.href = url.toString();
          }
        } catch (e) {
          // ignore invalid URLs
        }
      });
    });
  }

  // Update pagination links on load and history changes
  updatePaginationLinksWithTab();
  window.addEventListener('popstate', updatePaginationLinksWithTab);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initSessionsPage);

// Export for module usage
export { initSessionsPage };
