/**
 * VPAA Grades Page JavaScript
 * Handles department/course filtering via AJAX
 */

export function updateCourses() {
    const departmentId = document.getElementById('department_id')?.value;
    const courseSelect = document.getElementById('course_id');
    
    if (!courseSelect) return;
    
    if (departmentId) {
        // Fetch courses for the selected department
        fetch(`/api/departments/${departmentId}/courses`)
            .then(response => response.json())
            .then(data => {
                courseSelect.innerHTML = '<option value="">Select Course</option>';
                data.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = `${course.course_code} - ${course.name}`;
                    courseSelect.appendChild(option);
                });
                courseSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching courses:', error);
                courseSelect.innerHTML = '<option value="">Error loading courses</option>';
            });
    } else {
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        courseSelect.disabled = true;
    }
}

export function initVpaaGradesPage() {
    const departmentSelect = document.getElementById('department_id');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', updateCourses);
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="vpaa-grades"]') || 
        window.location.pathname.includes('/vpaa/grades')) {
        initVpaaGradesPage();
    }
});

window.updateCourses = updateCourses;
window.initVpaaGradesPage = initVpaaGradesPage;
