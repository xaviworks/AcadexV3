/**
 * Instructor Grades Page - Live Polling for Subject Cards
 *
 * Provides the Alpine.js component that polls for updated subject data
 * (assigned subjects, student counts, grade status) so the cards update
 * in real-time when chairperson changes are made.
 *
 * Usage in Blade:
 *   window.gradesPageConfig = { subjects: @json(...), pollUrl: '...', indexUrl: '...' };
 *   <div x-data="gradesSubjectCards()" x-init="init()">
 */

function gradesSubjectCards() {
  const config = window.gradesPageConfig || {};

  return {
    polling: false,
    pollInterval: null,
    subjects: config.subjects || [],
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
        this.subjects = d.subjects;
      } catch (e) {
        console.error('Grades poll error:', e);
      }
    },

    navigateToSubject(subjectItem) {
      window.location.href = config.indexUrl + '?subject_id=' + subjectItem.id + '&term=prelim';
    },
  };
}

// Expose globally for Alpine.js
window.gradesSubjectCards = gradesSubjectCards;
