/**
 * Help Guides Viewer - Live Polling Component
 *
 * Provides the Alpine.js component that polls for updated help-guide data
 * so guides appear / disappear in real-time when admin publishes changes.
 * Handles role-specific filtering server-side.
 *
 * Usage in Blade:
 *   window.helpGuidesPageConfig = { guides: @json(...), firstGuideId: ..., pollUrl: '...' };
 *   <div x-data="helpGuidesViewer()" x-init="init()">
 */

function helpGuidesViewer() {
  const config = window.helpGuidesPageConfig || {};

  return {
    polling: false,
    pollInterval: null,
    guides: config.guides || [],
    _lastJson: '',
    search: '',
    openGuide: config.firstGuideId ?? null,
    currentPdfUrl: '',
    currentPdfName: '',
    pdfModal: null,

    init() {
      this.pdfModal = new bootstrap.Modal(this.$refs.pdfModal);
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

        // Keep the currently open guide open after update
        const previousOpen = this.openGuide;
        this.guides = d.guides;

        // Restore open state if the guide still exists
        if (previousOpen && this.guides.some((g) => g.id === previousOpen)) {
          this.openGuide = previousOpen;
        } else if (this.guides.length > 0 && !previousOpen) {
          this.openGuide = this.guides[0].id;
        }
      } catch (e) {
        console.error('Help guides poll error:', e);
      }
    },

    toggleGuide(guideId) {
      this.openGuide = this.openGuide === guideId ? null : guideId;
    },

    openPdfViewer(url, name) {
      this.currentPdfUrl = url;
      this.currentPdfName = name;
      this.pdfModal.show();
    },

    matchesSearch(guide) {
      if (this.search === '') return true;
      const searchLower = this.search.toLowerCase();
      const titleMatch = guide.title.toLowerCase().includes(searchLower);
      const contentText = this.stripHtml(guide.content).toLowerCase();
      const contentMatch = contentText.includes(searchLower);
      return titleMatch || contentMatch;
    },

    hasSearchResults() {
      if (this.search === '') return true;
      return this.guides.some((guide) => this.matchesSearch(guide));
    },

    stripHtml(html) {
      const tmp = document.createElement('div');
      tmp.innerHTML = html;
      return tmp.textContent || tmp.innerText || '';
    },

    limitString(str, limit) {
      if (!str) return '';
      return str.length > limit ? str.substring(0, limit) + '...' : str;
    },
  };
}

// Expose globally for Alpine.js
window.helpGuidesViewer = helpGuidesViewer;
