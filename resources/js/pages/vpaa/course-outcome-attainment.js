/**
 * VPAA Course Outcome Attainment Page JavaScript
 * Handles subject card navigation and department/course filtering
 */

export function initVpaaCourseOutcomeAttainmentPage() {
  // Subject card click handlers
  document.querySelectorAll('.subject-card[data-url]').forEach((card) => {
    card.addEventListener('click', () => {
      window.location.href = card.dataset.url;
    });
  });

  // Enable/disable course select based on department selection
  const departmentSelect = document.getElementById('department_id');
  const courseSelect = document.getElementById('course_id');

  if (!departmentSelect || !courseSelect) return;

  departmentSelect.addEventListener('change', function () {
    const deptId = this.value;
    if (!deptId) {
      // Disable and reset course select if no department is selected
      courseSelect.disabled = true;
      while (courseSelect.options.length > 1) courseSelect.remove(1);
      courseSelect.selectedIndex = 0;
      return;
    }

    // Enable course select and fetch courses via AJAX
    courseSelect.disabled = false;
    fetch(`/api/courses?department_id=${encodeURIComponent(deptId)}`)
      .then((response) => response.json())
      .then((data) => {
        // Clear existing options except the first one
        while (courseSelect.options.length > 1) courseSelect.remove(1);
        // Add new course options
        data.forEach((course) => {
          const option = new Option(`${course.course_code} - ${course.course_description}`, course.id);
          courseSelect.add(option);
        });
      })
      .catch((error) => console.error('Error fetching courses:', error));
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (
    document.querySelector('[data-page="vpaa-course-outcome-attainment"]') ||
    window.location.pathname.includes('/vpaa/course-outcome-attainment')
  ) {
    initVpaaCourseOutcomeAttainmentPage();
  }
});

window.initVpaaCourseOutcomeAttainmentPage = initVpaaCourseOutcomeAttainmentPage;
