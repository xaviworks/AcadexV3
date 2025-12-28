/**
 * VPAA Students Page JavaScript
 * Handles department/course filtering
 */

export function initVpaaStudentsPage() {
  const departmentSelect = document.getElementById('department_id');
  const courseSelect = document.getElementById('course_id');

  if (!departmentSelect || !courseSelect) return;

  departmentSelect.addEventListener('change', function () {
    if (this.value) {
      // Enable course select and fetch courses for the selected department
      courseSelect.disabled = false;
    } else {
      // Disable and reset course select if no department is selected
      courseSelect.disabled = true;
      courseSelect.innerHTML = '<option value="">All Courses</option>';
    }
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (document.querySelector('[data-page="vpaa-students"]') || window.location.pathname.includes('/vpaa/students')) {
    initVpaaStudentsPage();
  }
});

window.initVpaaStudentsPage = initVpaaStudentsPage;
