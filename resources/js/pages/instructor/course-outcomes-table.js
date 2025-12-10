/**
 * Course Outcomes Table Page JavaScript
 * Handles modal functions for editing and deleting course outcomes
 */

// Modal Functions
export function openEditModal(id, coCode, identifier, description) {
    // Populate the form fields
    const editCoCode = document.getElementById('edit_co_code');
    const editCoIdentifier = document.getElementById('edit_co_identifier');
    const editDescription = document.getElementById('edit_description');
    const editForm = document.getElementById('editForm');
    
    if (editCoCode) editCoCode.value = coCode;
    if (editCoIdentifier) editCoIdentifier.value = identifier;
    if (editDescription) editDescription.value = description;
    
    // Set the form action URL
    if (editForm) {
        editForm.action = `/instructor/course_outcomes/${id}`;
    }
    
    // Show the modal
    if (typeof window.modal !== 'undefined') {
        window.modal.open('editCourseOutcomeModal', { id, coCode, coDescription: description });
    }
}

export function openDeleteModal(id, coCode) {
    const deleteCoCode = document.getElementById('delete_co_code');
    const deleteForm = document.getElementById('deleteForm');
    
    if (deleteCoCode) {
        deleteCoCode.textContent = coCode;
    }
    
    // Set the form action URL
    if (deleteForm) {
        deleteForm.action = `/instructor/course_outcomes/${id}`;
    }
    
    // Show the modal
    if (typeof window.modal !== 'undefined') {
        window.modal.open('deleteCourseOutcomeModal', { id, coCode });
    }
}

export function initCourseOutcomesTablePage() {
    // Course outcomes table is already rendered server-side
    // Modal functions are exposed globally for inline onclick handlers
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initCourseOutcomesTablePage);

// Expose functions globally
window.openEditModal = openEditModal;
window.openDeleteModal = openDeleteModal;
window.initCourseOutcomesTablePage = initCourseOutcomesTablePage;
