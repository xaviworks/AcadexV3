/**
 * Helper utilities for Alpine stores
 * Easy access to common store operations
 */

// Notification helpers that can be called from anywhere
window.notify = {
  success: (message, duration) => Alpine.store('notifications').success(message, duration),
  error: (message, duration) => Alpine.store('notifications').error(message, duration),
  warning: (message, duration) => Alpine.store('notifications').warning(message, duration),
  info: (message, duration) => Alpine.store('notifications').info(message, duration),
};

// Grade state helpers
window.gradeState = {
  setTerm: (term) => Alpine.store('grades').setTerm(term),
  markChanged: () => Alpine.store('grades').markChanged(),
  hasUnsaved: () => Alpine.store('grades').unsavedChanges,
};

// Modal helpers
window.modal = {
  open: (id, data) => Alpine.store('modals').open(id, data),
  close: () => Alpine.store('modals').close(),
  isOpen: (id) => Alpine.store('modals').isOpen(id),
};

// Dashboard filter helpers
window.filters = {
  set: (key, value) => Alpine.store('dashboard').setFilter(key, value),
  clear: () => Alpine.store('dashboard').clearFilters(),
  get: () => Alpine.store('dashboard').filters,
};

// Loading state helpers
window.loading = {
  start: (key) => Alpine.store('loading').start(key),
  stop: (key) => Alpine.store('loading').stop(key),
  isLoading: (key) => Alpine.store('loading').isLoading(key),
  stopAll: () => Alpine.store('loading').stopAll(),
};

// Confirmation dialog helpers
window.confirm = {
  ask: (options) => Alpine.store('confirm').ask(options),
};

// Search helpers
window.search = {
  set: (context, query) => Alpine.store('search').set(context, query),
  get: (context) => Alpine.store('search').get(context),
  clear: (context) => Alpine.store('search').clear(context),
};
