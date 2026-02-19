/**
 * Chairperson Structure Template Requests - Live Polling Component
 *
 * Provides the Alpine.js component that polls for updated formula request data
 * so the cards update in real-time when an admin approves/rejects a request.
 *
 * Usage in Blade:
 *   window.chairpersonTemplateRequestsConfig = { requests: @json(...), counts: {...}, pollUrl: '...' };
 *   <div x-data="templateRequestsChairperson()" x-init="init()">
 */

function templateRequestsChairperson() {
  const config = window.chairpersonTemplateRequestsConfig || {};

  return {
    polling: false,
    pollInterval: null,
    requests: config.requests || [],
    counts: config.counts || { pending: 0, approved: 0, rejected: 0 },
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
        const r = await fetch(config.pollUrl, {
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
        this.counts = d.counts;
      } catch (e) {
        console.error('Chairperson template requests poll error:', e);
      }
    },

    getStatusBadge(status) {
      switch (status) {
        case 'pending':
          return { class: 'bg-warning text-dark', icon: 'bi-clock-history', label: 'Pending Review' };
        case 'approved':
          return { class: 'bg-success', icon: 'bi-check-circle', label: 'Approved' };
        case 'rejected':
          return { class: 'bg-danger', icon: 'bi-x-circle', label: 'Rejected' };
        default:
          return { class: 'bg-secondary', icon: 'bi-question-circle', label: 'Unknown' };
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
          return 'Custom Structure';
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
window.templateRequestsChairperson = templateRequestsChairperson;
