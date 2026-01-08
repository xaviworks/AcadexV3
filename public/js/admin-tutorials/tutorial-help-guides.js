/**
 * Admin Tutorial - Help Guides Management
 * Tutorials for the Help Guides index, create, and edit pages
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Help guides tutorial registration deferred.');
        return;
    }

    /**
     * Helper function to check if guides table has data
     */
    function hasGuidesData() {
        // Check for empty state indicator
        const emptyState = document.querySelector('.text-center.py-5 .bi-inbox');
        if (emptyState) return false;
        
        // Check for table rows
        const rows = document.querySelectorAll('#guidesTable tbody tr');
        if (rows.length === 0) return false;
        
        // Make sure rows have actual data (not empty state message)
        const dataRows = Array.from(rows).filter(row => {
            const emptyCell = row.querySelector('td[colspan], .dataTables_empty');
            return !emptyCell;
        });
        
        return dataRows.length > 0;
    }

    /**
     * Helper function to open the create modal
     */
    function openCreateModal() {
        const modal = document.getElementById('createGuideModal');
        if (modal && typeof bootstrap !== 'undefined') {
            // Check if modal is already open
            const existingInstance = bootstrap.Modal.getInstance(modal);
            if (existingInstance) {
                return true; // Already open
            }
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            return true;
        }
        // Fallback: click the button
        const btn = document.querySelector('button[data-bs-target="#createGuideModal"]');
        if (btn) {
            btn.click();
            return true;
        }
        return false;
    }

    /**
     * Helper function to close the create modal
     */
    function closeCreateModal() {
        const modal = document.getElementById('createGuideModal');
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }

    // Steps for when the table has data (normal view)
    const stepsWithData = [
        {
            target: '.container-fluid h1, .h3.text-dark.fw-bold',
            title: 'Help Guides Management',
            content: 'Welcome to the Help Guides Management page! Here you can create and manage help documentation that is displayed to users based on their roles. Guides help users understand how to use different features of the system.',
            position: 'bottom'
        },
        {
            target: 'button[data-bs-target="#createGuideModal"], button.btn-success',
            title: 'Create New Guide',
            content: 'Click this button to create a new help guide. You can add a title, rich-text content, select which user roles can see it, set priority, and attach PDF documents.',
            position: 'left'
        },
        {
            target: '#guidesSearch, .input-group:has(#guidesSearch)',
            title: 'Search Guides',
            content: 'Use the search box to quickly find guides by title or content. Results filter in real-time as you type.',
            position: 'bottom',
            optional: true
        },
        {
            target: '#guidesTable thead, table thead',
            title: 'Guides Table Overview',
            content: 'The table displays all help guides with: Priority (High/Normal/Low), Title with content preview, Visible To (user roles), Status (Visible/Hidden), Creator, Last Updated, and Actions.',
            position: 'bottom'
        },
        {
            target: '.priority-badge, .priority-high, .priority-normal, .priority-low',
            title: 'Priority Levels',
            content: 'Priority determines the display order for users: High (shown first), Normal (middle), Low (shown last). Use high priority for important announcements or critical guides.',
            position: 'bottom',
            optional: true
        },
        {
            target: '.guide-info, .guide-title',
            title: 'Guide Title & Preview',
            content: 'Shows the guide title and a short excerpt of the content. Click the edit button to see or modify the full content.',
            position: 'bottom',
            optional: true
        },
        {
            target: '.role-badges, .role-badge',
            title: 'Role Visibility',
            content: 'Blue badges show which user roles can see this guide. You can make guides visible to specific roles like Instructors, Chairpersons, Deans, etc. Users only see guides assigned to their role.',
            position: 'bottom',
            optional: true
        },
        {
            target: '.status-badge, .status-visible, .status-hidden',
            title: 'Guide Status',
            content: 'Status indicates if the guide is currently visible to users: Green "Visible" means users can see it, Gray "Hidden" means it\'s saved but not displayed. Hidden guides are useful for drafts or temporary hiding.',
            position: 'bottom',
            optional: true
        },
        {
            target: '.action-btn.btn-edit, .edit-guide',
            title: 'Edit Guide',
            content: 'Click the pencil icon to edit a guide. You can modify the title, content, visibility settings, priority, and manage attachments.',
            position: 'left',
            optional: true
        },
        {
            target: '.action-btn.btn-delete, .delete-guide',
            title: 'Delete Guide',
            content: 'Click the trash icon to permanently delete a guide. This also removes all attached files. Requires confirmation before deletion.',
            position: 'left',
            optional: true
        },
        {
            target: '.card.mt-4, .card:has(.bi-lightbulb)',
            title: 'Quick Tips Panel',
            content: 'This panel provides helpful tips about managing guides: role-based visibility, attachment support, priority ordering, and how hidden guides work.',
            position: 'top',
            optional: true
        }
    ];

    // Steps for when the table is empty (modal-based tour)
    const stepsEmpty = [
        {
            target: '.container-fluid h1, .h3.text-dark.fw-bold',
            title: 'Help Guides Management',
            content: 'Welcome to the Help Guides Management page! No guides have been created yet. Let me walk you through how to create your first help guide.',
            position: 'bottom'
        },
        {
            target: 'button[data-bs-target="#createGuideModal"], button.btn-success',
            title: 'Create Your First Guide',
            content: 'Click this button to open the guide creation form. Click "Next" and I\'ll open it for you so we can explore the available options.',
            position: 'left'
        },
        {
            target: '#createGuideModal .modal-header, #createGuideModal .modal-title',
            title: 'Create Guide Modal',
            content: 'This modal contains all the fields needed to create a new help guide. Let\'s go through each section.',
            position: 'bottom'
        },
        {
            target: '#create_title',
            title: 'Guide Title',
            content: 'Enter a clear, descriptive title for your guide. This is what users will see in the help guide list. Make it specific and informative (e.g., "How to Submit Grades" or "Registration Process").',
            position: 'bottom'
        },
        {
            target: '#create_content, #createGuideModal .note-editor, #createGuideModal textarea[name="content"]',
            title: 'Guide Content',
            content: 'Write your guide content here using the rich text editor. You can format text (bold, italic, lists), add headings, insert links, and more. The content should be clear and helpful for users.',
            position: 'bottom'
        },
        {
            target: '#create_roles_container, #createGuideModal .col-md-6:first-of-type .border.rounded',
            title: 'Role Visibility',
            content: 'Select which user roles can see this guide. Check the boxes for roles that should have access: Instructors, Chairpersons, Deans, etc. Use "Select All" to make it visible to everyone.',
            position: 'right'
        },
        {
            target: '#create_sort_order',
            title: 'Display Priority',
            content: 'Set the priority level: High (Top) - shown first to users, Normal - middle of the list, Low (Bottom) - shown last. Use high priority for important announcements.',
            position: 'right'
        },
        {
            target: '#create_is_active',
            title: 'Visibility Toggle',
            content: 'This switch controls whether the guide is visible to users. When ON (green), users can see it. Turn it OFF to save as a draft without publishing.',
            position: 'right'
        },
        {
            target: '#create_attachments',
            title: 'PDF Attachments',
            content: 'Optionally attach PDF files to your guide. Users can download or preview these attachments. You can add up to 10 PDF files, each up to 10MB in size.',
            position: 'top'
        },
        {
            target: '#createGuideModal .modal-footer .btn-success',
            title: 'Create Guide Button',
            content: 'Once you\'ve filled in the details, click "Create Guide" to save and publish. The guide will immediately be available to the selected user roles (if visibility is enabled).',
            position: 'top'
        },
        {
            target: '#createGuideModal .modal-footer .btn-secondary',
            title: 'Cancel Button',
            content: 'Click "Cancel" to close this modal without saving. You can always come back later to create a guide. That completes the tour!',
            position: 'top'
        }
    ];

    /**
     * Build steps dynamically based on data presence
     */
    function getHelpGuidesSteps() {
        return hasGuidesData() ? stepsWithData : stepsEmpty;
    }

    // Register the help guides index tutorial with dynamic steps
    window.AdminTutorial.registerTutorial('admin-help-guides', {
        title: 'Help Guides Management',
        description: 'Learn how to create, manage, and organize help guides for different user roles',
        // Initial steps - will be replaced by dynamic logic
        steps: stepsWithData
    });

    // Enhance AdminTutorial to handle the help guides empty state specially
    (function enhanceForHelpGuides() {
        const originalStart = window.AdminTutorial.start.bind(window.AdminTutorial);
        const originalShowStep = window.AdminTutorial.showStep.bind(window.AdminTutorial);
        const originalEnd = window.AdminTutorial.end.bind(window.AdminTutorial);

        // Track if we opened the modal
        let modalOpenedByTutorial = false;

        window.AdminTutorial.start = function(tutorialId) {
            // If this is the help guides tutorial, update steps based on data
            if (tutorialId === 'admin-help-guides') {
                const tutorial = this.tutorials[tutorialId];
                if (tutorial) {
                    tutorial.steps = getHelpGuidesSteps();
                }
            }
            originalStart(tutorialId);
        };

        window.AdminTutorial.showStep = function(stepIndex) {
            // Handle modal opening for help guides empty state
            if (this.currentTutorial && this.currentTutorial.id === 'admin-help-guides' && !hasGuidesData()) {
                // Open modal when moving to step 2 (index 2 - the modal header step)
                if (stepIndex === 2 && !modalOpenedByTutorial) {
                    openCreateModal();
                    modalOpenedByTutorial = true;
                    // Wait for modal animation before showing step
                    setTimeout(() => {
                        originalShowStep(stepIndex);
                    }, 400);
                    return;
                }
            }
            originalShowStep(stepIndex);
        };

        window.AdminTutorial.end = function() {
            // Close modal if we opened it for the help guides tutorial
            if (this.currentTutorial && this.currentTutorial.id === 'admin-help-guides' && modalOpenedByTutorial) {
                closeCreateModal();
                modalOpenedByTutorial = false;
            }
            originalEnd();
        };
    })();

    // Register the help guides create tutorial (standalone page)
    window.AdminTutorial.registerTutorial('admin-help-guides-create', {
        title: 'Create Help Guide',
        description: 'Learn how to create a new help guide with rich content, role visibility, and attachments',
        steps: [
            {
                target: '.container-fluid h1, .h3.text-dark.fw-bold',
                title: 'Create Help Guide',
                content: 'This page allows you to create a new help guide. Fill in the required fields and configure visibility settings to publish your guide.',
                position: 'bottom'
            },
            {
                target: 'a[href*="help-guides"].btn-outline-secondary, .btn-outline-secondary:has(.bi-arrow-left)',
                title: 'Back to List',
                content: 'Click here to return to the guides list without saving. Any unsaved changes will be lost.',
                position: 'left'
            },
            {
                target: 'input#title, input[name="title"]',
                title: 'Guide Title',
                content: 'Enter a clear, descriptive title for your guide. This is what users will see in the help guide list. Make it specific and informative.',
                position: 'bottom'
            },
            {
                target: '#content, textarea[name="content"], .note-editor',
                title: 'Guide Content',
                content: 'Write your guide content using the rich text editor. You can format text (bold, italic, lists), add headings, insert links, and more. The content should be clear and helpful for users.',
                position: 'bottom'
            },
            {
                target: '.col-md-6:has(input[name="visible_roles[]"]), .border.rounded.p-3:has(input[type="checkbox"])',
                title: 'Role Visibility',
                content: 'Select which user roles can see this guide. Check the boxes for each role that should have access. Use "Select All" to make it visible to everyone, or choose specific roles.',
                position: 'right'
            },
            {
                target: 'select#sort_order, select[name="sort_order"]',
                title: 'Display Priority',
                content: 'Set the priority level: High (Top) - shown first, Normal - middle of the list, Low (Bottom) - shown last. Higher priority guides appear before lower priority ones.',
                position: 'right'
            },
            {
                target: 'input#is_active, input[name="is_active"]',
                title: 'Visibility Toggle',
                content: 'Toggle whether the guide is visible to users. When enabled (green), users can see the guide. When disabled, the guide is saved but hidden from users.',
                position: 'right'
            },
            {
                target: 'input#attachments, input[name="attachments[]"]',
                title: 'PDF Attachments',
                content: 'Optionally attach PDF files to your guide. Users can download or preview these attachments. You can add up to 10 PDF files, each up to 10MB in size.',
                position: 'top'
            },
            {
                target: 'button[type="submit"].btn-success',
                title: 'Create Guide',
                content: 'Click to save and publish your guide. If visibility is enabled, the guide will immediately be available to selected user roles.',
                position: 'top'
            }
        ]
    });

    // Register the help guides edit tutorial
    window.AdminTutorial.registerTutorial('admin-help-guides-edit', {
        title: 'Edit Help Guide',
        description: 'Learn how to modify an existing help guide, update content, and manage attachments',
        steps: [
            {
                target: '.container-fluid h1, .h3.text-dark.fw-bold',
                title: 'Edit Help Guide',
                content: 'This page allows you to modify an existing help guide. Update the content, change visibility settings, or manage attachments.',
                position: 'bottom'
            },
            {
                target: 'a[href*="help-guides"].btn-outline-secondary, .btn-outline-secondary:has(.bi-arrow-left)',
                title: 'Back to List',
                content: 'Click here to return to the guides list. Make sure to save your changes first, or they will be lost.',
                position: 'left'
            },
            {
                target: 'input#title, input[name="title"]',
                title: 'Update Title',
                content: 'Modify the guide title if needed. The title should clearly describe what the guide is about.',
                position: 'bottom'
            },
            {
                target: '#content, textarea[name="content"], .note-editor',
                title: 'Update Content',
                content: 'Edit the guide content using the rich text editor. You can update text, add new sections, or restructure the information.',
                position: 'bottom'
            },
            {
                target: '.col-md-6:has(input[name="visible_roles[]"]), .border.rounded.p-3:has(input[type="checkbox"])',
                title: 'Update Role Visibility',
                content: 'Change which user roles can see this guide. Add or remove role access as needed.',
                position: 'right'
            },
            {
                target: '.small.text-muted:has(strong)',
                title: 'Guide Metadata',
                content: 'View when the guide was created and last updated, along with who made the changes. This helps track guide history.',
                position: 'left',
                optional: true
            },
            {
                target: '#existingAttachments, .mb-4:has(#existingAttachments)',
                title: 'Current Attachments',
                content: 'View and manage existing PDF attachments. Click on a file name to preview/download it. Use the "Remove" button to delete attachments.',
                position: 'top',
                optional: true
            },
            {
                target: 'input[name="attachments[]"]',
                title: 'Add More Attachments',
                content: 'Upload additional PDF files to the guide. New files will be added alongside existing attachments.',
                position: 'top'
            },
            {
                target: 'button[type="submit"].btn-success',
                title: 'Update Guide',
                content: 'Click to save all your changes. The updated guide will be immediately available to users (if visibility is enabled).',
                position: 'top'
            }
        ]
    });
})();
