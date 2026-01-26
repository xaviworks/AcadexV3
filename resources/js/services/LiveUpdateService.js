/**
 * LiveUpdateService - Broadcaster-Agnostic Real-Time Updates
 *
 * A flexible service that provides real-time updates using either:
 * 1. WebSockets (when Echo is available and configured)
 * 2. Polling fallback (when WebSockets unavailable or fail)
 *
 * Security:
 * - All API requests include CSRF token and X-Requested-With header
 * - Polling uses exponential backoff on failures
 * - Debouncing prevents excessive API calls
 *
 * @author Acadex System
 */

class LiveUpdateService {
  constructor(options = {}) {
    // Configuration with sensible defaults
    this.config = {
      // Polling interval in milliseconds (default: 10 seconds)
      pollingInterval: options.pollingInterval || 10000,
      // Minimum polling interval (prevents too frequent requests)
      minPollingInterval: options.minPollingInterval || 5000,
      // Maximum polling interval for backoff (default: 60 seconds)
      maxPollingInterval: options.maxPollingInterval || 60000,
      // Enable adaptive polling (faster when tab is visible)
      adaptivePolling: options.adaptivePolling !== false,
      // Debounce delay for rapid events (default: 500ms)
      debounceDelay: options.debounceDelay || 500,
      // Whether to prefer WebSockets when available
      preferWebSockets: options.preferWebSockets !== false,
      // Enable debug logging
      debug: options.debug || false,
    };

    // State
    this.subscriptions = new Map();
    this.pollingTimers = new Map();
    this.debounceTimers = new Map();
    this.isTabVisible = !document.hidden;
    this.consecutiveFailures = 0;
    this.echoAvailable = this._checkEchoAvailability();

    // Bind visibility change handler
    this._handleVisibilityChange = this._handleVisibilityChange.bind(this);
    document.addEventListener('visibilitychange', this._handleVisibilityChange);

    this._log('LiveUpdateService initialized', {
      echoAvailable: this.echoAvailable,
      config: this.config,
    });
  }

  /**
   * Check if Laravel Echo is available and properly configured
   * @private
   */
  _checkEchoAvailability() {
    if (typeof window.Echo === 'undefined') {
      this._log('Echo not found in window');
      return false;
    }

    // Check if Echo connector is properly initialized
    try {
      // Try to access Echo's connector - if it throws, Echo isn't ready
      const connector = window.Echo.connector;
      if (!connector) {
        this._log('Echo connector not initialized');
        return false;
      }
      return true;
    } catch (e) {
      this._log('Echo check failed:', e);
      return false;
    }
  }

  /**
   * Subscribe to updates for a specific resource
   *
   * @param {string} resourceKey - Unique identifier for the subscription
   * @param {Object} options - Subscription options
   * @param {string} options.endpoint - API endpoint to poll
   * @param {Function} options.onUpdate - Callback when data updates
   * @param {Array<string>} options.channels - Echo channels to listen to (optional)
   * @param {Array<string>} options.events - Events to listen for on each channel
   * @param {number} options.pollingInterval - Custom polling interval (optional)
   * @param {Function} options.shouldUpdate - Function to determine if update needed
   */
  subscribe(resourceKey, options) {
    if (this.subscriptions.has(resourceKey)) {
      this._log(`Subscription ${resourceKey} already exists, updating...`);
      this.unsubscribe(resourceKey);
    }

    const subscription = {
      endpoint: options.endpoint,
      onUpdate: options.onUpdate,
      channels: options.channels || [],
      events: options.events || ['.row.created', '.row.updated', '.row.deleted'],
      pollingInterval: options.pollingInterval || this.config.pollingInterval,
      shouldUpdate: options.shouldUpdate || (() => true),
      lastData: null,
      echoSubscriptions: [],
    };

    this.subscriptions.set(resourceKey, subscription);

    // Try WebSocket first if available and preferred
    if (this.echoAvailable && this.config.preferWebSockets) {
      this._setupEchoListeners(resourceKey, subscription);
    }

    // Always set up polling as fallback (or primary if WebSockets unavailable)
    this._startPolling(resourceKey, subscription);

    this._log(`Subscribed to ${resourceKey}`, {
      useEcho: this.echoAvailable && this.config.preferWebSockets,
      channels: subscription.channels,
    });

    return this;
  }

  /**
   * Unsubscribe from a resource
   * @param {string} resourceKey
   */
  unsubscribe(resourceKey) {
    const subscription = this.subscriptions.get(resourceKey);
    if (!subscription) return;

    // Clear polling timer
    this._stopPolling(resourceKey);

    // Clear debounce timer
    if (this.debounceTimers.has(resourceKey)) {
      clearTimeout(this.debounceTimers.get(resourceKey));
      this.debounceTimers.delete(resourceKey);
    }

    // Unsubscribe from Echo channels
    subscription.echoSubscriptions.forEach((channelName) => {
      try {
        window.Echo.leave(channelName);
      } catch (e) {
        this._log(`Failed to leave channel ${channelName}:`, e);
      }
    });

    this.subscriptions.delete(resourceKey);
    this._log(`Unsubscribed from ${resourceKey}`);
  }

  /**
   * Unsubscribe from all resources
   */
  destroy() {
    this.subscriptions.forEach((_, key) => this.unsubscribe(key));
    document.removeEventListener('visibilitychange', this._handleVisibilityChange);
    this._log('LiveUpdateService destroyed');
  }

  /**
   * Force an immediate refresh for a subscription
   * @param {string} resourceKey
   */
  async refresh(resourceKey) {
    const subscription = this.subscriptions.get(resourceKey);
    if (subscription) {
      await this._fetchAndUpdate(resourceKey, subscription);
    }
  }

  /**
   * Set up Echo listeners for WebSocket updates
   * @private
   */
  _setupEchoListeners(resourceKey, subscription) {
    subscription.channels.forEach((channelName) => {
      try {
        const channel = window.Echo.channel(channelName);

        subscription.events.forEach((eventName) => {
          channel.listen(eventName, (data) => {
            this._log(`Echo event received: ${channelName}${eventName}`, data);
            this._debouncedUpdate(resourceKey, subscription);
          });
        });

        subscription.echoSubscriptions.push(channelName);
        this._log(`Listening on Echo channel: ${channelName}`);
      } catch (e) {
        this._log(`Failed to subscribe to Echo channel ${channelName}:`, e);
      }
    });
  }

  /**
   * Start polling for a subscription
   * @private
   */
  _startPolling(resourceKey, subscription) {
    // Don't poll if tab is hidden (saves resources)
    if (!this.isTabVisible && this.config.adaptivePolling) {
      this._log(`Skipping polling for ${resourceKey} (tab hidden)`);
      return;
    }

    // Calculate interval with backoff for failures
    let interval = subscription.pollingInterval;
    if (this.consecutiveFailures > 0) {
      interval = Math.min(
        interval * Math.pow(1.5, this.consecutiveFailures),
        this.config.maxPollingInterval
      );
    }

    const timer = setTimeout(async () => {
      if (!this.subscriptions.has(resourceKey)) return;

      await this._fetchAndUpdate(resourceKey, subscription);

      // Continue polling
      this._startPolling(resourceKey, subscription);
    }, interval);

    this.pollingTimers.set(resourceKey, timer);
  }

  /**
   * Stop polling for a subscription
   * @private
   */
  _stopPolling(resourceKey) {
    if (this.pollingTimers.has(resourceKey)) {
      clearTimeout(this.pollingTimers.get(resourceKey));
      this.pollingTimers.delete(resourceKey);
    }
  }

  /**
   * Debounce update to prevent rapid successive calls
   * @private
   */
  _debouncedUpdate(resourceKey, subscription) {
    if (this.debounceTimers.has(resourceKey)) {
      clearTimeout(this.debounceTimers.get(resourceKey));
    }

    const timer = setTimeout(() => {
      this.debounceTimers.delete(resourceKey);
      this._fetchAndUpdate(resourceKey, subscription);
    }, this.config.debounceDelay);

    this.debounceTimers.set(resourceKey, timer);
  }

  /**
   * Fetch data from endpoint and trigger update callback
   * @private
   */
  async _fetchAndUpdate(resourceKey, subscription) {
    try {
      const response = await fetch(subscription.endpoint, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
          'X-CSRF-TOKEN':
            document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();

      // Check if update should be applied
      if (!subscription.shouldUpdate(data, subscription.lastData)) {
        this._log(`Update skipped for ${resourceKey} (no changes)`);
        return;
      }

      // Store last data for comparison
      const previousData = subscription.lastData;
      subscription.lastData = data;

      // Reset failure counter on success
      this.consecutiveFailures = 0;

      // Trigger callback with data
      subscription.onUpdate(data, previousData);

      this._log(`Updated ${resourceKey}`, { dataKeys: Object.keys(data) });
    } catch (error) {
      this.consecutiveFailures++;
      this._log(`Failed to fetch ${resourceKey}:`, error);

      // Don't spam console on repeated failures
      if (this.consecutiveFailures <= 3) {
      }
    }
  }

  /**
   * Handle visibility change (pause/resume polling)
   * @private
   */
  _handleVisibilityChange() {
    this.isTabVisible = !document.hidden;

    this.subscriptions.forEach((subscription, resourceKey) => {
      if (this.isTabVisible) {
        // Tab became visible - restart polling and refresh immediately
        this._log(`Tab visible - refreshing ${resourceKey}`);
        this._fetchAndUpdate(resourceKey, subscription);
        this._startPolling(resourceKey, subscription);
      } else if (this.config.adaptivePolling) {
        // Tab hidden - stop polling to save resources
        this._stopPolling(resourceKey);
        this._log(`Tab hidden - paused polling for ${resourceKey}`);
      }
    });
  }

  /**
   * Debug logging
   * @private
   */
  _log(...args) {
  }
}

// Export singleton instance and class
const liveUpdateService = new LiveUpdateService({
  debug: import.meta.env.DEV,
});

export { LiveUpdateService, liveUpdateService };
export default liveUpdateService;
