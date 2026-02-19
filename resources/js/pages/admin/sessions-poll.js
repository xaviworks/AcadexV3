/**
 * Admin Sessions & User Logs - Live Polling Component
 *
 * Provides Alpine.js components that poll for updated session and user log data
 * so tables update in real-time when users log in/out.
 *
 * Usage in Blade:
 *   window.sessionsPageConfig = {
 *     sessions: @json(...),
 *     userLogs: @json(...),
 *     pollSessionsUrl: '{{ route("admin.sessions.poll") }}',
 *     pollLogsUrl: '{{ route("admin.sessions.pollLogs") }}',
 *     selectedDate: '...',
 *   };
 *   <div x-data="sessionsLive()" x-init="init()">
 */

function sessionsLive() {
  const config = window.sessionsPageConfig || {};

  return {
    polling: false,
    pollInterval: null,
    sessions: config.sessions || [],
    userLogs: config.userLogs || [],
    selectedDate: config.selectedDate || '',
    _lastSessionsJson: '',
    _lastLogsJson: '',

    init() {
      this.polling = true;
      this.fetchSessions();
      this.fetchLogs();
      this.startPolling();
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          clearInterval(this.pollInterval);
        } else {
          this.fetchSessions();
          this.fetchLogs();
          this.startPolling();
        }
      });
    },

    destroy() {
      if (this.pollInterval) clearInterval(this.pollInterval);
    },

    startPolling() {
      if (this.pollInterval) clearInterval(this.pollInterval);
      this.pollInterval = setInterval(() => {
        this.fetchSessions();
        this.fetchLogs();
      }, 3000);
    },

    async fetchSessions() {
      try {
        const r = await fetch(config.pollSessionsUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });
        if (!r.ok) return;
        const text = await r.text();
        if (text === this._lastSessionsJson) return;
        this._lastSessionsJson = text;
        const d = JSON.parse(text);
        this.sessions = d.sessions;
      } catch (e) {
        console.error('Sessions poll error:', e);
      }
    },

    async fetchLogs() {
      try {
        let url = config.pollLogsUrl;
        if (this.selectedDate) {
          url += '?date=' + encodeURIComponent(this.selectedDate);
        }
        const r = await fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });
        if (!r.ok) return;
        const text = await r.text();
        if (text === this._lastLogsJson) return;
        this._lastLogsJson = text;
        const d = JSON.parse(text);
        this.userLogs = d.logs;
      } catch (e) {
        console.error('User logs poll error:', e);
      }
    },

    /**
     * Called when the date filter changes.
     */
    onDateChange() {
      this._lastLogsJson = '';
      this.fetchLogs();
    },

    /**
     * Returns device icon class for a given device type.
     */
    deviceIcon(type) {
      switch (type) {
        case 'Desktop':
          return 'fas fa-desktop';
        case 'Tablet':
          return 'fas fa-tablet-alt';
        case 'Mobile':
          return 'fas fa-mobile-alt';
        default:
          return 'fas fa-question-circle';
      }
    },
  };
}

window.sessionsLive = sessionsLive;
