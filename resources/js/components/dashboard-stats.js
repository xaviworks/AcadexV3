/**
 * Real-Time Dashboard Stats (Facebook-style auto-update)
 * Automatically refreshes dashboard statistics when data changes
 */
import Alpine from 'alpinejs';

Alpine.data('dashboardStats', (initialStats) => ({
  stats: initialStats || {},
  loading: false,

  init() {
    if (!window.Echo) return;

    // Listen for student changes
    this.listenForStudentUpdates();

    // Listen for grade changes
    this.listenForGradeUpdates();

    // Listen for subject assignment changes (Course Load)
    this.listenForSubjectUpdates();

    // Listen for user-related changes (Admin/Dean dashboards)
    this.listenForUserUpdates();

    // Listen for department changes (VPAA dashboard)
    this.listenForDepartmentUpdates();

    // Listen for user-specific dashboard refresh events
    this.listenForDashboardRefresh();
  },

  /**
   * Listen for student-related changes
   */
  listenForStudentUpdates() {
    const channel = window.Echo.channel('table.students');

    channel.listen('.row.created', () => {
      this.refreshStats();
    });

    channel.listen('.row.deleted', () => {
      this.refreshStats();
    });

    channel.listen('.row.updated', () => {
      this.refreshStats();
    });
  },

  /**
   * Listen for grade-related changes
   */
  listenForGradeUpdates() {
    const channel = window.Echo.channel('table.grades');

    channel.listen('.row.created', () => {
      this.refreshStats();
    });

    channel.listen('.row.updated', () => {
      this.refreshStats();
    });
  },

  /**
   * Listen for subject assignment changes (affects Course Load)
   */
  listenForSubjectUpdates() {
    const channel = window.Echo.channel('table.subjects');

    channel.listen('.row.created', () => {
      this.refreshStats();
    });

    channel.listen('.row.updated', () => {
      this.refreshStats();
    });

    channel.listen('.row.deleted', () => {
      this.refreshStats();
    });
  },

  /**
   * Listen for user-related changes (Admin/Dean dashboards)
   */
  listenForUserUpdates() {
    const channel = window.Echo.channel('table.users');

    channel.listen('.row.created', () => {
      this.refreshStats();
    });

    channel.listen('.row.updated', () => {
      this.refreshStats();
    });

    channel.listen('.row.deleted', () => {
      this.refreshStats();
    });
  },

  /**
   * Listen for department changes (VPAA dashboard)
   */
  listenForDepartmentUpdates() {
    const channel = window.Echo.channel('table.departments');

    channel.listen('.row.created', () => {
      this.refreshStats();
    });

    channel.listen('.row.updated', () => {
      this.refreshStats();
    });

    channel.listen('.row.deleted', () => {
      this.refreshStats();
    });
  },

  /**
   * Listen for user-specific dashboard refresh events
   */
  listenForDashboardRefresh() {
    const userId = window.Laravel?.user?.id;
    if (!userId) return;

    window.Echo.private(`App.Models.User.${userId}`).listen('.dashboard.refresh', (e) => {
      console.log('📊 Dashboard refresh triggered for user:', userId);
      this.refreshStats();
    });
  },

  /**
   * Fetch fresh statistics from server (instant, no delay)
   */
  async refreshStats() {
    if (this.loading) return;

    this.loading = true;

    try {
      const response = await fetch('/dashboard/stats', {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();

        // Update stats with smooth transition
        Object.keys(data).forEach((key) => {
          if (this.stats[key] !== data[key]) {
            this.stats[key] = data[key];
            this.flashCard(key);
          }
        });
      }
    } catch (error) {
      console.error('Failed to refresh dashboard stats:', error);
    } finally {
      this.loading = false;
    }
  },

  /**
   * Flash animation when stat updates
   */
  flashCard(statKey) {
    this.$nextTick(() => {
      const element = document.querySelector(`[data-stat="${statKey}"]`);
      if (element) {
        element.classList.add('flash-updated');
        setTimeout(() => element.classList.remove('flash-updated'), 1000);
      }
    });
  },
}));
