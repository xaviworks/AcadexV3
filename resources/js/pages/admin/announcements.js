/**
 * Admin Announcements Page Scripts
 * 
 * Handles Quill rich text editor initialization and announcement form logic
 */

// Initialize Quill Editor
let quill;

// Dynamically load a script and return a promise that resolves when loaded
function loadScript(src) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`script[src="${src}"]`)) {
      // Script already included in the page
      // Wait until global Quill is available
      const check = () => {
        if (window.Quill) return resolve();
        setTimeout(check, 50);
      };
      return check();
    }

    const s = document.createElement('script');
    s.src = src;
    s.async = true;
    s.onload = () => resolve();
    s.onerror = () => reject(new Error('Failed to load ' + src));
    document.head.appendChild(s);
  });
}

/**
 * Initialize the Quill editor and set up event handlers
 * @param {Object} config - Configuration object from Blade template
 * @param {string} config.csrfToken - CSRF token
 * @param {string} config.previewUrl - URL for recipient preview
 * @param {string} config.oldMessage - Previously entered message (for validation errors)
 */
async function initAnnouncementEditor(config) {
    // Ensure Quill library is available; load it dynamically if necessary
    if (typeof window.Quill === 'undefined') {
        try {
            await loadScript('https://cdn.quilljs.com/1.3.7/quill.min.js');
        } catch (e) {
            console.error('Failed to load Quill library:', e);
            return; // Abort initialization
        }
    }

    // Initialize Quill with toolbar (no image upload)
    quill = new Quill('#message-editor', {
        theme: 'snow',
        placeholder: 'Enter your announcement message...',
        modules: {
            toolbar: [
                [{ 'font': [] }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    // Block pasted or inserted images (clipboard HTML with <img>)
    const Delta = Quill.import('delta');
    const clipboard = quill.getModule('clipboard');
    clipboard.addMatcher('IMG', () => new Delta());

    // Prevent drag/drop files (including images) from being inserted
    quill.root.addEventListener('drop', (event) => {
        if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
            event.preventDefault();
        }
    });

    // Prevent pasting images (files or inline image MIME types)
    quill.root.addEventListener('paste', (event) => {
        const items = event.clipboardData ? Array.from(event.clipboardData.items) : [];
        const hasImage = items.some((item) => item.kind === 'file' || (item.type && item.type.startsWith('image/')));
        if (hasImage) {
            event.preventDefault();
        }
    });

    // Set initial content if exists
    if (config.oldMessage) {
        quill.root.innerHTML = config.oldMessage;
        // Sync initial content to hidden input
        document.getElementById('message-input').value = config.oldMessage;
    }

    // Sync content to hidden input on text change
    quill.on('text-change', function() {
        const html = quill.root.innerHTML;
        document.getElementById('message-input').value = html;
        
        // Update Alpine.js message length
        if (window.announcementFormInstance) {
            window.announcementFormInstance.messageLength = quill.getText().trim().length;
            window.announcementFormInstance.messageHtml = html;
        }
    });

    // Trigger initial sync for Alpine instance after a short delay to ensure Alpine is ready
    setTimeout(() => {
        if (window.announcementFormInstance) {
            const html = quill.root.innerHTML;
            const textLength = quill.getText().trim().length;
            window.announcementFormInstance.messageLength = textLength;
            window.announcementFormInstance.messageHtml = html;
            document.getElementById('message-input').value = html;
        }
    }, 100);
}

/**
 * Alpine.js component for the announcement form
 * @param {Object} config - Configuration object from Blade template
 * @param {string} config.csrfToken - CSRF token
 * @param {string} config.previewUrl - URL for recipient preview
 * @param {string} config.oldTitle - Previously entered title
 * @param {string} config.oldTargetType - Previously selected target type
 * @param {string} config.oldTargetId - Previously selected target ID
 * @param {Array} config.users - Array of user objects
 */
function announcementForm(config) {
    const instance = {
        title: config.oldTitle || '',
        messageHtml: '',
        messageLength: 0,
        targetType: config.oldTargetType || '',
        selectedTargetId: config.oldTargetId || '',
        userSearch: '',
        users: config.users || [],
        filteredUsers: config.users || [],
        recipientCount: 0,
        recipientPreview: [],
        isSubmitting: false,

        get canSubmit() {
            return this.title.trim() !== '' && 
                   this.messageLength > 0 && 
                   this.targetType !== '' && 
                   this.selectedTargetId !== '';
        },

        init() {
            window.announcementFormInstance = this;
            // Sync initial message content if exists (for validation errors)
            if (config.oldMessage) {
                this.messageHtml = config.oldMessage;
                // Calculate text length from HTML by creating a temp element
                const temp = document.createElement('div');
                temp.innerHTML = config.oldMessage;
                this.messageLength = (temp.textContent || temp.innerText || '').trim().length;
            }
        },

        selectTargetType(type) {
            this.targetType = type;
            this.selectedTargetId = '';
            this.recipientCount = 0;
            this.recipientPreview = [];
            
            if (type === 'specific_user') {
                this.filteredUsers = this.users;
            }
        },

        filterUsers() {
            const search = this.userSearch.toLowerCase();
            this.filteredUsers = this.users.filter(user => 
                user.name.toLowerCase().includes(search) || 
                user.email.toLowerCase().includes(search)
            );
        },

        selectUser(userId) {
            this.selectedTargetId = userId;
            const user = this.users.find(u => u.id === userId);
            if (user) {
                this.recipientCount = 1;
                this.recipientPreview = [{ name: user.name, email: user.email }];
            }
        },

        async fetchRecipientPreview() {
            if (!this.selectedTargetId || !this.targetType) {
                this.recipientCount = 0;
                this.recipientPreview = [];
                return;
            }

            try {
                const response = await fetch(config.previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        target_type: this.targetType,
                        target_id: this.selectedTargetId,
                    }),
                });

                const data = await response.json();
                this.recipientCount = data.count;
                this.recipientPreview = data.preview;
            } catch (error) {
                console.error('Failed to fetch recipient preview:', error);
            }
        },

        handleSubmit(e) {
            if (!this.canSubmit) {
                e.preventDefault();
                return;
            }
            // Ensure message is synced
            document.getElementById('message-input').value = quill.root.innerHTML;
            this.isSubmitting = true;
        }
    };
    
    return instance;
}

// Export for use in Blade template
window.initAnnouncementEditor = initAnnouncementEditor;
window.announcementForm = announcementForm;
