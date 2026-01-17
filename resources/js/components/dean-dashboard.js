import Alpine from 'alpinejs';

Alpine.data('deanDashboard', (initialTotalStudents, initialTotalInstructors, initialTotalCourses, initialTotalDepartments) => ({
    data: {
        totalStudents: initialTotalStudents,
        totalInstructors: initialTotalInstructors,
        totalCourses: initialTotalCourses,
        totalDepartments: initialTotalDepartments
    },
    
    init() {
        // Listen to table changes for real-time updates
        if (window.Echo) {
            // Listen to student changes
            window.Echo.channel('table.students')
                .listen('.table.row.created', () => this.refreshDashboard())
                .listen('.table.row.updated', () => this.refreshDashboard())
                .listen('.table.row.deleted', () => this.refreshDashboard());
            
            // Listen to user (instructor) changes
            window.Echo.channel('table.users')
                .listen('.table.row.created', () => this.refreshDashboard())
                .listen('.table.row.updated', () => this.refreshDashboard())
                .listen('.table.row.deleted', () => this.refreshDashboard());
            
            // Listen to department changes
            window.Echo.channel('table.departments')
                .listen('.table.row.created', () => this.refreshDashboard())
                .listen('.table.row.updated', () => this.refreshDashboard())
                .listen('.table.row.deleted', () => this.refreshDashboard());
        }
    },
    
    refreshDashboard() {
        fetch('/dashboard/dean/data')
            .then(response => response.json())
            .then(newData => {
                this.data.totalStudents = newData.totalStudents;
                this.data.totalInstructors = newData.totalInstructors;
                this.data.totalCourses = newData.totalCourses;
                this.data.totalDepartments = newData.totalDepartments;
            })
            .catch(error => console.error('Error refreshing dean dashboard:', error));
    }
}));
