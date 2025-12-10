/**
 * VPAA Students Departments Page JavaScript
 * Handles department card navigation
 */

export function initVpaaStudentsDepartmentsPage() {
    document.querySelectorAll('#department-selection .subject-card[data-url]').forEach(card => {
        card.addEventListener('click', () => {
            window.location.href = card.dataset.url;
        });
    });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="vpaa-students-departments"]') || 
        document.querySelector('#department-selection .subject-card[data-url]')) {
        initVpaaStudentsDepartmentsPage();
    }
});

window.initVpaaStudentsDepartmentsPage = initVpaaStudentsDepartmentsPage;
