/**
 * Admin Tutorial - Structure Template Requests
 * Tutorial for the Formula Template Request Review page
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Structure template requests tutorial registration deferred.');
        return;
    }

    // Register the structure template requests tutorial
    window.AdminTutorial.registerTutorial('admin-structure-template-requests', {
        title: 'Formula Template Request Review',
        description: 'Learn how to review, approve, and reject chairperson formula template submissions',
        tableDataCheck: {
            selector: '.table-hover tbody tr, table tbody tr',
            emptySelectors: ['.card-body.text-center', '.bi-inbox'],
            entityName: 'formula requests',
            noAddButton: true
        },
        steps: [
            {
                target: '.container-fluid h1, .h3.text-dark.fw-bold',
                title: 'Structure Formula Requests',
                content: 'Welcome to the Formula Request Review system! Chairpersons can submit custom formula templates for their departments. As an admin, you review these submissions and decide whether to approve them for system-wide use or request modifications.',
                position: 'bottom'
            },
            {
                target: 'a[href*="gradesFormula"][href*="formulas"], .btn-outline-secondary',
                title: 'Back to Grades Formula',
                content: 'Click here to return to the main Grades Formula Management page where you can manage structure templates and formulas.',
                position: 'left'
            },
            {
                target: 'a[href*="status=all"]',
                title: 'All Requests Filter',
                content: 'View the complete history of all formula requests regardless of their status. This gives you a full audit trail of submissions from all chairpersons.',
                position: 'bottom'
            },
            {
                target: 'a[href*="status=pending"]',
                title: 'Pending Requests Filter',
                content: 'PRIORITY VIEW: Filter to see only pending requests awaiting your review. The badge shows how many requests need your attention. Process these promptly to avoid blocking chairpersons.',
                position: 'bottom'
            },
            {
                target: 'a[href*="status=approved"]',
                title: 'Approved Requests Filter',
                content: 'View previously approved formula templates. These templates are now part of the system catalog and can be used by chairpersons and instructors when configuring their courses.',
                position: 'bottom'
            },
            {
                target: 'a[href*="status=rejected"]',
                title: 'Rejected Requests Filter',
                content: 'View declined submissions with your admin notes explaining why. Chairpersons can review your feedback and submit revised requests addressing your concerns.',
                position: 'bottom'
            },
            {
                target: 'table.table-hover thead, .table thead',
                title: 'Request Details Table',
                content: 'The table displays comprehensive request information: Template Name (what the chairperson named it), Submitted By (chairperson details), Structure Type (Lecture Only, Lecture+Lab, or Custom), Current Status, Submission Date, and Available Actions.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'table tbody tr:first-child td:first-child, .fw-bold:first-of-type',
                title: 'Template Name Column',
                content: 'Shows the template\'s label and optional description. The name should clearly indicate the template\'s purpose (e.g., "Nursing Clinical Rotation Formula" or "Laboratory-Heavy Science Course").',
                position: 'bottom',
                requiresData: true
            },
            {
                target: 'table tbody tr:first-child td:nth-child(2), td:has(.text-muted)',
                title: 'Submitter Information',
                content: 'Shows which chairperson submitted the request, including their name and email. This helps you contact them if you need clarification before making a decision.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '.badge.bg-info',
                title: 'Structure Type Badge',
                content: 'Indicates the template\'s structure type: LECTURE ONLY (theory-based courses with activities like quizzes, exams, assignments), LECTURE + LAB (courses with both theoretical and practical components), or CUSTOM (unique configurations for specialized courses).',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '.badge.bg-warning',
                title: 'Status Badge',
                content: 'Color-coded status indicators: Yellow/Clock = Pending (needs review), Green/Checkmark = Approved (in system), Red/X = Rejected (declined with feedback). Status changes are immediate upon your action.',
                position: 'bottom',
                requiresData: true
            },
            {
                target: '.btn-info',
                title: 'View Request Details',
                content: 'Click View to open a detailed modal showing the complete formula configuration: all activity types, weight distributions, any composite components (like Lecture/Lab sections), and the chairperson\'s notes explaining their requirements.',
                position: 'left',
                requiresData: true
            },
            {
                target: '.btn-success.btn-sm',
                title: 'Approve Request',
                content: 'Approve the template to add it to the global structure catalog. Once approved: 1) The chairperson is notified, 2) The template becomes available system-wide, 3) Any instructor can use it when configuring their subjects. Add optional notes to provide feedback.',
                position: 'left',
                requiresData: true
            },
            {
                target: '.btn-danger.btn-sm',
                title: 'Reject Request',
                content: 'Decline the request with a REQUIRED explanation. Your feedback should be constructive: explain what\'s wrong (e.g., "weights don\'t sum to 100%", "missing required activity types") and suggest how to fix it. The chairperson can then submit a revised request.',
                position: 'left',
                requiresData: true
            },
            {
                target: '#viewRequestModal, .modal-content',
                title: 'Request Details Modal',
                content: 'The modal displays the complete formula structure: label, description, structure type, all component weights, and any hierarchical relationships (composite components with sub-activities). Review this carefully before approving.',
                position: 'center',
                optional: true
            },
            {
                target: 'textarea[name="admin_notes"], #approveAdminNotes, #rejectAdminNotes',
                title: 'Admin Notes Field',
                content: 'Add notes when approving or rejecting. For approvals: acknowledge good work or note any limitations. For rejections: REQUIRED - clearly explain issues and provide actionable guidance for resubmission.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.alert.alert-success, .alert.alert-danger',
                title: 'Action Confirmation',
                content: 'When approving/rejecting, you\'ll see confirmation messages explaining the consequences. Approvals create new system-wide templates immediately. Rejections notify the chairperson with your feedback.',
                position: 'bottom',
                optional: true
            }
        ]
    });
})();
