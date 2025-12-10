/**
 * Admin Structure Template Requests Page JavaScript
 * Handles viewing, approving, and rejecting template requests
 */

const structureTypeLabels = {
    lecture_only: 'Lecture Only',
    lecture_lab: 'Lecture + Lab',
    custom: 'Custom',
};

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatMultiline(value) {
    return escapeHtml(value).replace(/\r?\n/g, '<br>');
}

function safeParseJson(value) {
    if (!value) {
        return {};
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        console.error('Failed to parse structure payload', error);
        return {};
    }
}

function formatWeight(value) {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric.toFixed(2) : '0.00';
}

function groupStructure(entries) {
    if (!Array.isArray(entries)) {
        return [];
    }

    const groups = [];
    const componentLookup = new Map();
    let lastGroup = null;

    entries.forEach((rawEntry) => {
        const entry = rawEntry || {};
        const isMain = Boolean(entry.is_main);

        if (isMain) {
            const group = {
                component: entry,
                subComponents: [],
            };

            groups.push(group);
            lastGroup = group;

            const key = entry.component_id ?? entry.id;
            if (key !== undefined && key !== null) {
                componentLookup.set(String(key), group);
            }

            return;
        }

        if (!lastGroup) {
            return;
        }

        let targetGroup = lastGroup;
        const parentKey = entry.parent_id;

        if (parentKey !== undefined && parentKey !== null) {
            const lookupKey = String(parentKey);
            if (componentLookup.has(lookupKey)) {
                targetGroup = componentLookup.get(lookupKey);
            }
        }

        targetGroup.subComponents.push(entry);
    });

    return groups;
}

window.viewRequest = function(button) {
    const label = button.dataset.label ?? '';
    const description = button.dataset.description ?? '';
    const chairperson = button.dataset.chairperson ?? '';
    const status = button.dataset.status ?? '';
    const adminNotes = button.dataset.adminNotes ?? '';
    const structureConfig = safeParseJson(button.dataset.structure);
    const structureTypeKey = structureConfig?.type ?? 'custom';
    const structureTypeLabel = structureTypeLabels[structureTypeKey] ?? 'Custom';
    const structureEntries = Array.isArray(structureConfig?.structure) ? structureConfig.structure : [];
    const grouped = groupStructure(structureEntries);

    let html = `
        <div class="mb-3">
            <label class="fw-bold text-muted small">Template Name</label>
            <p>${escapeHtml(label)}</p>
        </div>
    `;

    if (description) {
        html += `
            <div class="mb-3">
                <label class="fw-bold text-muted small">Description</label>
                <p>${formatMultiline(description)}</p>
            </div>
        `;
    }

    html += `
        <div class="mb-3">
            <label class="fw-bold text-muted small">Submitted By</label>
            <p>${escapeHtml(chairperson)}</p>
        </div>
        <div class="mb-3">
            <label class="fw-bold text-muted small">Structure Type</label>
            <p><span class="badge bg-info text-dark">${escapeHtml(structureTypeLabel)}</span></p>
        </div>
        <div class="mb-3">
            <label class="fw-bold text-muted small">Grading Components</label>
            <div class="mt-2">
    `;

    if (!grouped.length) {
        html += '<p class="text-muted">No grading components provided.</p>';
    } else {
        grouped.forEach(({ component, subComponents }) => {
            const mainLabel = escapeHtml(component?.label ?? 'Unnamed');
            const activityType = escapeHtml(component?.activity_type ?? 'other');
            const mainWeight = formatWeight(component?.weight);

            html += `
                <div class="card mb-2 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${mainLabel}</strong>
                                <span class="badge bg-success-subtle text-success ms-2">${activityType}</span>
                            </div>
                            <strong class="text-success">${mainWeight}%</strong>
                        </div>
            `;

            if (subComponents.length) {
                html += '<div class="mt-2 ps-3 border-start border-success">';
                subComponents.forEach((sub) => {
                    const subLabel = escapeHtml(sub?.label ?? 'Unnamed');
                    const subWeight = formatWeight(sub?.weight);

                    html += `
                        <div class="d-flex justify-content-between small mb-1">
                            <span><i class="bi bi-arrow-return-right me-1"></i>${subLabel}</span>
                            <span class="text-success">${subWeight}%</span>
                        </div>
                    `;
                });
                html += '</div>';
            }

            html += '</div></div>';
        });
    }

    html += '</div></div>';

    if (adminNotes && (status === 'approved' || status === 'rejected')) {
        const alertType = status === 'approved' ? 'success' : 'danger';
        html += `
            <div class="mb-3">
                <label class="fw-bold text-muted small">Admin Notes</label>
                <div class="alert alert-${alertType} mb-0">
                    ${formatMultiline(adminNotes)}
                </div>
            </div>
        `;
    }

    document.getElementById('viewRequestBody').innerHTML = html;
};

window.approveRequest = function(button) {
    const templateName = button.dataset.templateName ?? '';
    const approveUrl = button.dataset.approveUrl ?? '';
    const approveForm = document.getElementById('approveForm');
    const approveTemplateNameEl = document.getElementById('approveTemplateName');
    const approveAdminNotesEl = document.getElementById('approveAdminNotes');

    if (approveTemplateNameEl) {
        approveTemplateNameEl.textContent = templateName;
    }

    if (approveAdminNotesEl) {
        approveAdminNotesEl.value = '';
    }

    if (approveForm && approveUrl) {
        approveForm.action = approveUrl;
    }
};

window.rejectRequest = function(button) {
    const templateName = button.dataset.templateName ?? '';
    const rejectUrl = button.dataset.rejectUrl ?? '';
    const rejectForm = document.getElementById('rejectForm');
    const rejectTemplateNameEl = document.getElementById('rejectTemplateName');
    const rejectAdminNotesEl = document.getElementById('rejectAdminNotes');

    if (rejectTemplateNameEl) {
        rejectTemplateNameEl.textContent = templateName;
    }

    if (rejectAdminNotesEl) {
        rejectAdminNotesEl.value = '';
    }

    if (rejectForm && rejectUrl) {
        rejectForm.action = rejectUrl;
    }
};

export function initStructureTemplateRequestsPage() {
    // Page is initialized via global functions called from modal buttons
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-page="admin-structure-template-requests"]') || 
        window.location.pathname.includes('/admin/structure-template-requests')) {
        initStructureTemplateRequestsPage();
    }
});

window.initStructureTemplateRequestsPage = initStructureTemplateRequestsPage;
