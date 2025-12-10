/**
 * Instructor Course Outcomes Page JavaScript
 * Handles subject card navigation and CO code generation
 */

export function initCourseOutcomesPage() {
    // Subject card click handlers
    document.querySelectorAll('.subject-card[data-url]').forEach(card => {
        card.addEventListener('click', function() {
            window.location.href = this.dataset.url;
        });
    });

    // Auto-generate CO Code and Identifier when modal is shown
    const modal = document.getElementById('addCourseOutcomeModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            generateNextCOCode();
        });
    }
}

/**
 * Generate next CO code based on existing course outcomes
 */
function generateNextCOCode() {
    // Get subject code from page data
    const pageData = window.courseOutcomesPageData || {};
    const subjectCode = pageData.subjectCode || '';
    
    // Get existing course outcomes from the table
    const existingCOs = [];
    const coRows = document.querySelectorAll('tbody tr');
    
    coRows.forEach(row => {
        const coCodeCell = row.querySelector('td:first-child');
        if (coCodeCell) {
            const coCode = coCodeCell.textContent.trim();
            // Extract number from CO code (e.g., "CO1" -> 1)
            const match = coCode.match(/CO(\d+)/i);
            if (match) {
                existingCOs.push(parseInt(match[1]));
            }
        }
    });

    // Determine next CO number
    let nextCONumber = 1;
    if (existingCOs.length > 0) {
        const maxCO = Math.max(...existingCOs);
        nextCONumber = maxCO + 1;
    }

    // Set the auto-generated values
    const coCodeInput = document.getElementById('co_code');
    const coIdentifierInput = document.getElementById('co_identifier');
    
    if (coCodeInput && coIdentifierInput) {
        const newCOCode = `CO${nextCONumber}`;
        const newIdentifier = subjectCode ? `${subjectCode}.${nextCONumber}` : `CO${nextCONumber}`;
        
        coCodeInput.value = newCOCode;
        coIdentifierInput.value = newIdentifier;
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="instructor-course-outcomes"]') || 
        window.location.pathname.includes('/instructor/course-outcomes')) {
        initCourseOutcomesPage();
    }
});

window.initCourseOutcomesPage = initCourseOutcomesPage;
window.generateNextCOCode = generateNextCOCode;
