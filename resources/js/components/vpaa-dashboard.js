import Alpine from 'alpinejs';

Alpine.data('vpaaDashboard', (initialDepartmentsCount, initialInstructorsCount, initialStudentsCount) => ({
    data: {
        departmentsCount: initialDepartmentsCount,
        instructorsCount: initialInstructorsCount,
        studentsCount: initialStudentsCount
    },
    
    init() {
        // Listen to table changes for real-time updates
        if (window.Echo) {
            // Listen to department changes
            window.Echo.channel('table.departments')
                .listen('.table.row.created', () => this.refreshDashboard())
                .listen('.table.row.updated', () => this.refreshDashboard())
                .listen('.table.row.deleted', () => this.refreshDashboard());
            
            // Listen to user (instructor) changes
            window.Echo.channel('table.users')
                .listen('.table.row.created', () => this.refreshDashboard())
                .listen('.table.row.updated', () => this.refreshDashboard())
                .listen('.table.row.deleted', () => this.refreshDashboard());
            
            // Listen to student changes
            window.Echo.channel('table.students')
                .listen('.table.row.created', () => this.refreshDashboard())
                .listen('.table.row.updated', () => this.refreshDashboard())
                .listen('.table.row.deleted', () => this.refreshDashboard());
        }
    },
    
    refreshDashboard() {
        fetch('/dashboard/vpaa/data')
            .then(response => response.json())
            .then(newData => {
                this.data.departmentsCount = newData.departmentsCount;
                this.data.instructorsCount = newData.instructorsCount;
                this.data.studentsCount = newData.studentsCount;
            })
            .catch(error => console.error('Error refreshing VPAA dashboard:', error));
    }
}));
