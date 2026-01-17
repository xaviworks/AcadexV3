/**
 * Real-Time Instructor Dashboard (No Reload)
 * Auto-updates all dashboard data when changes occur
 */
import Alpine from 'alpinejs';

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

    init() {
      if (!window.Echo) {
        console.error('Laravel Echo not initialized');
        return;
      }

      // Listen for student changes
      window.Echo.channel('table.students')
        .listen('.row.created', () => this.refreshDashboard())
        .listen('.row.updated', () => this.refreshDashboard())
        .listen('.row.deleted', () => this.refreshDashboard());

      // Listen for grade changes
      window.Echo.channel('table.grades')
        .listen('.row.created', () => this.refreshDashboard())
        .listen('.row.updated', () => this.refreshDashboard());

      // Listen for subject changes
      window.Echo.channel('table.subjects')
        .listen('.row.created', () => this.refreshDashboard())
        .listen('.row.updated', () => this.refreshDashboard())
        .listen('.row.deleted', () => this.refreshDashboard());

      console.log('Instructor dashboard real-time updates active');
    },

    async refreshDashboard() {
      if (this.loading) return;

      this.loading = true;

      try {
        const response = await fetch('/dashboard/instructor/data', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        });

        if (response.ok) {
          const newData = await response.json();
          this.data = newData;
          console.log('Dashboard data updated');
        }
      } catch (error) {
        console.error('Failed to refresh dashboard:', error);
      } finally {
        this.loading = false;
      }
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
