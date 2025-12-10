/**
 * VPAA Course Outcome Results Page JavaScript
 * Modifies the instructor view for VPAA read-only context
 */

export function initVpaaCourseOutcomeResultsPage() {
    // Mark body so global CSS can target instructor links in included template
    document.body.classList.add('vpaa-view');

    // Extra hardening: remove the specific setup button by text or href
    document.querySelectorAll('a.btn').forEach(function(el){
        const txt = (el.textContent || '').trim();
        const href = el.getAttribute('href') || '';
        if (/set\s*up\s*course\s*outcomes/i.test(txt) || href.includes('/instructor/course_outcomes')) {
            el.style.display = 'none';
        }
    });

    // Remove the informational note about creating course outcomes for the current academic period (global)
    document.querySelectorAll('small.text-muted').forEach(function(el){
        const text = (el.textContent || '').replace(/\s+/g,' ').trim().toLowerCase();
        if (text.includes('course outcomes can be created')) {
            const container = el.closest('.mt-3');
            if (container) container.remove(); else el.remove();
        }
    });

    // If the included instructor view shows the setup guidance alert, rewrite it for VPAA context
    const infoAlerts = Array.from(document.querySelectorAll('.alert.alert-info'));
    infoAlerts.forEach(alert => {
        const heading = alert.querySelector('.alert-heading');
        const list = alert.querySelector('ul');
        if (heading && /no course outcomes found/i.test(heading.textContent || '')) {
            heading.innerHTML = '<i class="bi bi-info-circle me-2"></i>Viewing Only: No Course Outcomes Available';
            // Replace guidance content with VPAA-friendly copy
            const p = alert.querySelector('p');
            if (p) {
                p.textContent = 'This subject currently has no defined course outcomes. Results will appear once instructors set up outcomes and assessments.';
            }
            const hr = alert.querySelector('hr');
            if (hr) hr.remove();
            if (list) list.replaceChildren();
            if (list) {
                const li = document.createElement('li');
                li.textContent = 'Monitoring only: setup is managed by instructors and department staff.';
                list.appendChild(li);
            }
        }
    });
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initVpaaCourseOutcomeResultsPage);

// Expose function globally
window.initVpaaCourseOutcomeResultsPage = initVpaaCourseOutcomeResultsPage;
