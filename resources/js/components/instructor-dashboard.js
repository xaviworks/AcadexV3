/**
 * Real-Time Instructor Dashboard (No Reload)
 * Auto-updates all dashboard data when changes occur
 *
 * Uses LiveUpdateService for broadcaster-agnostic updates:
 * - WebSockets (Echo) when available
 * - Polling fallback when WebSockets unavailable
 *
 * Security: All requests include CSRF token and proper headers
 */
import Alpine from 'alpinejs';
import liveUpdateService from '../services/LiveUpdateService';

Alpine.data(
  'instructorDashboard',
  (
    instructorStudents,
    enrolledSubjectsCount,
    totalPassedStudents,
    totalFailedStudents,
    termCompletions,
    subjectCharts
  ) => ({
    data: {
      instructorStudents,
      enrolledSubjectsCount,
      totalPassedStudents,
      totalFailedStudents,
      termCompletions,
      subjectCharts,
    },
    loading: false,
    lastUpdated: null,

    init() {
      // Subscribe to live updates using the service
      liveUpdateService.subscribe('instructor-dashboard', {
        endpoint: '/dashboard/instructor/data',
        channels: ['table.students', 'table.grades', 'table.subjects'],
        events: ['.row.created', '.row.updated', '.row.deleted'],
        pollingInterval: 15000, // 15 seconds
        onUpdate: (newData, previousData) => {
          this.handleDataUpdate(newData, previousData);
        },
        // Only update if data actually changed
        shouldUpdate: (newData, oldData) => {
          if (!oldData) return true;
          return JSON.stringify(newData) !== JSON.stringify(oldData);
        },
      });

      // Also listen on private channel for targeted refresh (if user is logged in)
      this._setupPrivateChannel();

    },

    /**
     * Set up private channel for user-specific dashboard refresh
     */
    _setupPrivateChannel() {
      const userId = window.Laravel?.user?.id;
      if (!userId || !window.Echo) return;

      try {
        window.Echo.private(`App.Models.User.${userId}`).listen(
          '.dashboard.refresh',
          () => {
            this.refreshDashboard();
          }
        );
      } catch (e) {
        // Private channel may fail if not authenticated - ignore silently
      }
    },

    /**
     * Handle data update from LiveUpdateService
     */
    handleDataUpdate(newData, previousData) {
      if (this.loading) return;

      // Track what changed for animations
      const changes = this._detectChanges(newData, previousData);

      // Update data
      this.data = newData;
      this.lastUpdated = new Date();

      // Trigger animations for changed values
      if (changes.length > 0) {
        this._animateChanges(changes);
      }

    },

    /**
     * Detect which values changed
     */
    _detectChanges(newData, oldData) {
      if (!oldData) return [];

      const changes = [];
      const keys = [
        'instructorStudents',
        'enrolledSubjectsCount',
        'totalPassedStudents',
        'totalFailedStudents',
      ];

      keys.forEach((key) => {
        if (newData[key] !== oldData[key]) {
          changes.push({
            key,
            oldValue: oldData[key],
            newValue: newData[key],
            increased: newData[key] > oldData[key],
          });
        }
      });

      return changes;
    },

    /**
     * Animate changed stat cards
     */
    _animateChanges(changes) {
      this.$nextTick(() => {
        changes.forEach((change) => {
          const cardMap = {
            instructorStudents: 'total-students',
            enrolledSubjectsCount: 'course-load',
            totalPassedStudents: 'students-passed',
            totalFailedStudents: 'students-failed',
          };

          const cardId = cardMap[change.key];
          const element = document.querySelector(`[data-stat="${cardId}"]`);

          if (element) {
            // Add flash animation
            element.classList.add('stat-updated');
            element.classList.add(change.increased ? 'stat-increased' : 'stat-decreased');

            setTimeout(() => {
              element.classList.remove('stat-updated', 'stat-increased', 'stat-decreased');
            }, 1500);
          }
        });
      });
    },

    async refreshDashboard() {
      if (this.loading) return;

      this.loading = true;

      try {
        const response = await fetch('/dashboard/instructor/data', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN':
              document.querySelector('meta[name="csrf-token"]')?.content || '',
          },
          credentials: 'same-origin',
        });

        if (response.ok) {
          const newData = await response.json();
          this.handleDataUpdate(newData, this.data);
        }
      } catch (error) {
      } finally {
        this.loading = false;
      }
    },

    /**
     * Cleanup when component is destroyed
     */
    destroy() {
      liveUpdateService.unsubscribe('instructor-dashboard');
    },

    getTermProgress(term) {
      const termData = this.data.termCompletions[term];
      if (!termData || termData.total === 0) return 0;
      return Math.round((termData.graded / termData.total) * 100);
    },

    getProgressColor(progress) {
      if (progress === 100) return 'success';
      if (progress > 75) return 'info';
      if (progress > 50) return 'warning';
      return 'danger';
    },

    getAvgCompletion(subject) {
      if (!subject.termPercentages || subject.termPercentages.length === 0) return 0;
      const sum = subject.termPercentages.reduce((a, b) => a + b, 0);
      return sum / subject.termPercentages.length;
    },

    getCompletionColor(completion) {
      if (completion === 100) return 'success';
      if (completion >= 75) return 'info';
      if (completion >= 50) return 'warning';
      return 'danger';
    },
  })
);
