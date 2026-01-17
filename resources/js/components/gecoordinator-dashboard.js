/**
 * Real-Time GE Coordinator Dashboard (No Reload)
 * Auto-updates all dashboard data when changes occur
 */
import Alpine from 'alpinejs';

Alpine.data('gecoordinatorDashboard', (countInstructors, countStudents, countCourses) => ({
    data: {
        countInstructors,
        countStudents,
        countCourses
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

        // Listen for subject changes (affects course count)
        window.Echo.channel('table.subjects')
            .listen('.row.created', () => this.refreshDashboard())
            .listen('.row.updated', () => this.refreshDashboard())
            .listen('.row.deleted', () => this.refreshDashboard());

        // Listen for user changes (affects instructor count)
        window.Echo.channel('table.users')
            .listen('.row.created', () => this.refreshDashboard())
            .listen('.row.updated', () => this.refreshDashboard())
            .listen('.row.deleted', () => this.refreshDashboard());

        console.log('GE Coordinator dashboard real-time updates active');
    },

    async refreshDashboard() {
        if (this.loading) return;
        
        this.loading = true;
        
        try {
            const response = await fetch('/dashboard/gecoordinator/data', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const newData = await response.json();
                this.data = newData;
                console.log('GE Coordinator dashboard data updated');
            }
        } catch (error) {
            console.error('Failed to refresh GE Coordinator dashboard:', error);
        } finally {
            this.loading = false;
        }
    }
}));
