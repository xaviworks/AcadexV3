/**
 * Helper utilities for Alpine stores
 * Easy access to common store operations
 * 
 * NOTE: These helpers use lazy evaluation - they access Alpine.store() 
 * only when called, not when defined. This ensures Alpine is initialized first.
 */

// Notification helpers that can be called from anywhere
window.notify = {
  success: (message, duration) => window.Alpine?.store('notifications').success(message, duration),
  error: (message, duration) => window.Alpine?.store('notifications').error(message, duration),
  warning: (message, duration) => window.Alpine?.store('notifications').warning(message, duration),
  info: (message, duration) => window.Alpine?.store('notifications').info(message, duration),
};

// Grade state helpers
window.gradeState = {
  setTerm: (term) => window.Alpine?.store('grades').setTerm(term),
  markChanged: () => window.Alpine?.store('grades').markChanged(),
  hasUnsaved: () => window.Alpine?.store('grades').unsavedChanges,
};

// Modal helpers
window.modal = {
  open: (id, data) => window.Alpine?.store('modals').open(id, data),
  close: () => window.Alpine?.store('modals').close(),
  isOpen: (id) => window.Alpine?.store('modals').isOpen(id),
};

// Dashboard filter helpers
window.filters = {
  set: (key, value) => window.Alpine?.store('dashboard').setFilter(key, value),
  clear: () => window.Alpine?.store('dashboard').clearFilters(),
  get: () => window.Alpine?.store('dashboard').filters,
};

// Loading state helpers
window.loading = {
  start: (key) => window.Alpine?.store('loading').start(key),
  stop: (key) => window.Alpine?.store('loading').stop(key),
  isLoading: (key) => window.Alpine?.store('loading').isLoading(key),
  stopAll: () => window.Alpine?.store('loading').stopAll(),
};

// Confirmation dialog helpers
window.confirm = {
  ask: (options) => window.Alpine?.store('confirm').ask(options),
};

// Search helpers
window.search = {
  set: (context, query) => window.Alpine?.store('search').set(context, query),
  get: (context) => window.Alpine?.store('search').get(context),
  clear: (context) => window.Alpine?.store('search').clear(context),
};
