import Alpine from 'alpinejs';

Alpine.data('adminDashboard', (initialTotalUsers, initialLoginCount, initialFailedLoginCount, initialSuccessfulData, initialFailedData, initialMonthlySuccessfulData, initialMonthlyFailedData) => ({
    data: {
        totalUsers: initialTotalUsers || 0,
        loginCount: initialLoginCount || 0,
        failedLoginCount: initialFailedLoginCount || 0,
        successfulData: initialSuccessfulData || Array(24).fill(0),
        failedData: initialFailedData || Array(24).fill(0),
        monthlySuccessfulData: initialMonthlySuccessfulData || Array(12).fill(0),
        monthlyFailedData: initialMonthlyFailedData || Array(12).fill(0)
    },
    
    get activeUsersPercentage() {
        return Math.round((this.data.loginCount / Math.max(this.data.totalUsers, 1)) * 100);
    },
    
    init() {
        if (window.Echo) {
            window.Echo.channel('table.user_logs')
                .listen('.row.created', () => this.refreshDashboard());
            
            window.Echo.channel('table.users')
                .listen('.row.created', () => this.refreshDashboard());
        }
    },
    
    refreshDashboard() {
        const selectedDate = new URLSearchParams(window.location.search).get('date') || new Date().toISOString().split('T')[0];
        const selectedYear = new URLSearchParams(window.location.search).get('year') || new Date().getFullYear();
        
        fetch(`/dashboard/admin/data?date=${selectedDate}&year=${selectedYear}`)
            .then(response => response.json())
            .then(newData => {
                this.data.totalUsers = newData.totalUsers || 0;
                this.data.loginCount = newData.loginCount || 0;
                this.data.failedLoginCount = newData.failedLoginCount || 0;
                this.data.successfulData = newData.successfulData || Array(24).fill(0);
                this.data.failedData = newData.failedData || Array(24).fill(0);
                this.data.monthlySuccessfulData = newData.monthlySuccessfulData || Array(12).fill(0);
                this.data.monthlyFailedData = newData.monthlyFailedData || Array(12).fill(0);
            })
            .catch(error => console.error('Admin dashboard refresh error:', error));
    },
    
    getSuccessful(index) {
        return this.data.successfulData[index] || 0;
    },
    
    getFailed(index) {
        return this.data.failedData[index] || 0;
    },
    
    getTotal(index) {
        return this.getSuccessful(index) + this.getFailed(index);
    },
    
    getRate(index) {
        const total = this.getTotal(index);
        return total > 0 ? Math.round((this.getSuccessful(index) / total) * 100) : 0;
    },
    
    getRateColor(rate) {
        if (rate >= 90) return 'success';
        if (rate >= 70) return 'info';
        if (rate >= 50) return 'warning';
        return 'danger';
    },
    
    getMonthlySuccessful(index) {
        return this.data.monthlySuccessfulData[index] || 0;
    },
    
    getMonthlyFailed(index) {
        return this.data.monthlyFailedData[index] || 0;
    },
    
    getMonthlyTotal(index) {
        return this.getMonthlySuccessful(index) + this.getMonthlyFailed(index);
    },
    
    getMonthlyRate(index) {
        const total = this.getMonthlyTotal(index);
        return total > 0 ? Math.round((this.getMonthlySuccessful(index) / total) * 100) : 0;
    },
    
    isHighlightMonth(index) {
        const monthlyTotals = this.data.monthlySuccessfulData.map((val, i) => 
            val + this.data.monthlyFailedData[i]
        );
        const sortedIndices = monthlyTotals
            .map((total, i) => ({ index: i, total }))
            .sort((a, b) => b.total - a.total)
            .slice(0, 6)
            .map(item => item.index);
        return sortedIndices.includes(index);
    }
}));
