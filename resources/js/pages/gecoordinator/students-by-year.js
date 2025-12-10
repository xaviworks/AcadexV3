/**
 * GE Coordinator Students by Year Page JavaScript
 * Handles year level filtering
 */

export function initStudentsByYearPage() {
    const yearFilter = document.getElementById('yearFilter');
    const table = document.getElementById('studentsTable');
    
    if (!yearFilter || !table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    yearFilter.addEventListener('change', function() {
        const selectedYear = this.value;
        
        for (let row of rows) {
            if (!selectedYear || row.getAttribute('data-year') === selectedYear) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="gecoordinator-students-by-year"]') || 
        window.location.pathname.includes('/gecoordinator/students')) {
        initStudentsByYearPage();
    }
});

window.initStudentsByYearPage = initStudentsByYearPage;
