/**
 * Alpine.js Store Configuration
 * Centralized state management for UI components
 */

import Alpine from 'alpinejs';

// Dashboard state store
Alpine.store('dashboard', {
  loading: false,
  filters: {
    academicPeriod: null,
    department: null,
    course: null,
    yearLevel: null,
  },

  setFilter(key, value) {
    this.filters[key] = value;
    localStorage.setItem('dashboard_filters', JSON.stringify(this.filters));
  },

  loadFilters() {
    const saved = localStorage.getItem('dashboard_filters');
    if (saved) {
      this.filters = { ...this.filters, ...JSON.parse(saved) };
    }
  },

  clearFilters() {
    this.filters = {
      academicPeriod: null,
      department: null,
      course: null,
      yearLevel: null,
    };
    localStorage.removeItem('dashboard_filters');
  },
});

// Grade management state
Alpine.store('grades', {
  selectedTerm: 'prelim',
  editMode: false,
  unsavedChanges: false,

  setTerm(term) {
    if (this.unsavedChanges) {
      if (!confirm('You have unsaved changes. Are you sure you want to switch terms?')) {
        return false;
      }
    }
    this.selectedTerm = term;
    this.unsavedChanges = false;
    return true;
  },

  markChanged() {
    this.unsavedChanges = true;
  },

  clearUnsaved() {
    this.unsavedChanges = false;
  },

  resetChanges() {
    this.unsavedChanges = false;
  },
});

// Modal state management
Alpine.store('modals', {
  active: null,
  data: {},

  open(modalId, data = {}) {
    this.active = modalId;
    this.data = data;
  },

  close() {
    this.active = null;
    this.data = {};
  },

  isOpen(modalId) {
    return this.active === modalId;
  },
});

// Notification/Toast state
Alpine.store('notifications', {
  items: [],

  add(message, type = 'info', duration = 5000) {
    const id = Date.now();
    this.items.push({ id, message, type, duration });

    if (duration > 0) {
      setTimeout(() => this.remove(id), duration);
    }

    return id;
  },

  remove(id) {
    this.items = this.items.filter((item) => item.id !== id);
  },

  success(message, duration = 5000) {
    return this.add(message, 'success', duration);
  },

  error(message, duration = 7000) {
    return this.add(message, 'danger', duration);
  },

  warning(message, duration = 6000) {
    return this.add(message, 'warning', duration);
  },

  info(message, duration = 5000) {
    return this.add(message, 'info', duration);
  },
});

// Table state (sorting, pagination, selection)
Alpine.store('table', {
  sortColumn: null,
  sortDirection: 'asc',
  selectedRows: [],
  currentPage: 1,
  perPage: 10,

  sort(column) {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
  },

  toggleRow(id) {
    const index = this.selectedRows.indexOf(id);
    if (index > -1) {
      this.selectedRows.splice(index, 1);
    } else {
      this.selectedRows.push(id);
    }
  },

  selectAll(ids) {
    this.selectedRows = [...ids];
  },

  deselectAll() {
    this.selectedRows = [];
  },

  isSelected(id) {
    return this.selectedRows.includes(id);
  },

  resetTable() {
    this.sortColumn = null;
    this.sortDirection = 'asc';
    this.selectedRows = [];
    this.currentPage = 1;
  },
});

// User preferences
Alpine.store('preferences', {
  theme: 'light',
  sidebarCollapsed: false,
  compactMode: false,

  toggleSidebar() {
    this.sidebarCollapsed = !this.sidebarCollapsed;
    this.save();
  },

  toggleCompactMode() {
    this.compactMode = !this.compactMode;
    this.save();
  },

  save() {
    localStorage.setItem(
      'user_preferences',
      JSON.stringify({
        theme: this.theme,
        sidebarCollapsed: this.sidebarCollapsed,
        compactMode: this.compactMode,
      })
    );
  },

  load() {
    const saved = localStorage.getItem('user_preferences');
    if (saved) {
      const prefs = JSON.parse(saved);
      this.theme = prefs.theme || 'light';
      this.sidebarCollapsed = prefs.sidebarCollapsed || false;
      this.compactMode = prefs.compactMode || false;
    }
  },
});

// Loading state management
Alpine.store('loading', {
  active: {},

  start(key = 'default') {
    this.active[key] = true;
  },

  stop(key = 'default') {
    this.active[key] = false;
  },

  isLoading(key = 'default') {
    return this.active[key] === true;
  },

  stopAll() {
    this.active = {};
  },
});

// Confirmation dialog state
Alpine.store('confirm', {
  show: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  type: 'warning', // warning, danger, info
  onConfirm: null,
  onCancel: null,

  ask(options = {}) {
    return new Promise((resolve) => {
      this.title = options.title || 'Confirm Action';
      this.message = options.message || 'Are you sure?';
      this.confirmText = options.confirmText || 'Confirm';
      this.cancelText = options.cancelText || 'Cancel';
      this.type = options.type || 'warning';

      this.onConfirm = () => {
        this.show = false;
        resolve(true);
      };

      this.onCancel = () => {
        this.show = false;
        resolve(false);
      };

      this.show = true;
    });
  },

  confirm() {
    if (this.onConfirm) this.onConfirm();
  },

  cancel() {
    if (this.onCancel) this.onCancel();
  },
});

// Search/Filter state with persistence
Alpine.store('search', {
  queries: {},

  set(context, query) {
    this.queries[context] = query;
    localStorage.setItem(`search_${context}`, query);
  },

  get(context) {
    if (!this.queries[context]) {
      const saved = localStorage.getItem(`search_${context}`);
      if (saved) this.queries[context] = saved;
    }
    return this.queries[context] || '';
  },

  clear(context) {
    this.queries[context] = '';
    localStorage.removeItem(`search_${context}`);
  },

  clearAll() {
    Object.keys(this.queries).forEach((context) => {
      localStorage.removeItem(`search_${context}`);
    });
    this.queries = {};
  },
});

// Initialize stores on Alpine init
document.addEventListener('alpine:init', () => {
  Alpine.store('dashboard').loadFilters();
  Alpine.store('preferences').load();
});
