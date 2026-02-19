/**
 * Admin Structure Template Requests - Live Polling Component
 *
 * Provides the Alpine.js component that polls for updated formula request data
 * so the table updates in real-time when a chairperson submits a new request
 * or when another admin approves/rejects one.
 *
 * Usage in Blade:
 *   window.templateRequestsConfig = { requests: @json(...), pendingCount: ..., pollUrl: '...', status: '...' };
 *   <div x-data="templateRequestsAdmin()" x-init="init()">
 */

function templateRequestsAdmin() {
  const config = window.templateRequestsConfig || {};

  return {
    polling: false,
    pollInterval: null,
    requests: config.requests || [],
    pendingCount: config.pendingCount || 0,
    status: config.status || 'all',
    _lastJson: '',

    init() {
      this.polling = true;
      this.fetchData();
      this.startPolling();
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          clearInterval(this.pollInterval);
        } else {
          this.fetchData();
          this.startPolling();
        }
      });
    },

    destroy() {
      if (this.pollInterval) clearInterval(this.pollInterval);
    },

    startPolling() {
      if (this.pollInterval) clearInterval(this.pollInterval);
      this.pollInterval = setInterval(() => this.fetchData(), 2000);
    },

    async fetchData() {
      try {
        const url = config.pollUrl + '?status=' + encodeURIComponent(this.status);
        const r = await fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });
        if (!r.ok) return;
        const text = await r.text();
        if (text === this._lastJson) return;
        this._lastJson = text;
        const d = JSON.parse(text);
        this.requests = d.requests;
        this.pendingCount = d.pendingCount;
      } catch (e) {
        console.error('Template requests poll error:', e);
      }
    },

    getStatusBadgeClass(status) {
      switch (status) {
        case 'pending':
          return 'bg-warning text-dark';
        case 'approved':
          return 'bg-success';
        case 'rejected':
          return 'bg-danger';
        default:
          return 'bg-secondary';
      }
    },

    getStatusIcon(status) {
      switch (status) {
        case 'pending':
          return 'bi-clock-history';
        case 'approved':
          return 'bi-check-circle';
        case 'rejected':
          return 'bi-x-circle';
        default:
          return 'bi-question-circle';
      }
    },

    getStructureLabel(config) {
      const type = config?.type ?? 'unknown';
      switch (type) {
        case 'lecture_only':
          return 'Lecture Only';
        case 'lecture_lab':
          return 'Lecture + Lab';
        case 'custom':
          return 'Custom';
        default:
          return 'Unknown';
      }
    },

    truncate(text, length) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    },
  };
}

// Expose globally for Alpine.js
window.templateRequestsAdmin = templateRequestsAdmin;
