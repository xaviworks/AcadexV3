/**
 * Admin Announcements Page Scripts
 * 
 * Handles Quill rich text editor initialization and announcement form logic
 */

// Initialize Quill Editor
let quill;

/**
 * Initialize the Quill editor and set up event handlers
 * @param {Object} config - Configuration object from Blade template
 * @param {string} config.csrfToken - CSRF token for uploads
 * @param {string} config.uploadUrl - URL for image uploads
 * @param {string} config.previewUrl - URL for recipient preview
 * @param {string} config.oldMessage - Previously entered message (for validation errors)
 */
function initAnnouncementEditor(config) {
    // Custom image handler
    function imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (file) {
                // Show upload progress
                document.getElementById('image-upload-progress').classList.add('active');
                
                try {
                    // Create FormData for upload
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('_token', config.csrfToken);

                    // Upload to server
                    const response = await fetch(config.uploadUrl, {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Upload failed');
                    }

                    const data = await response.json();
                    
                    // Insert image into editor
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', data.url);
                    quill.setSelection(range.index + 1);
                } catch (error) {
                    console.error('Image upload failed:', error);
                    alert('Failed to upload image. Please try again.');
                } finally {
                    document.getElementById('image-upload-progress').classList.remove('active');
                }
            }
        };
    }

    // Initialize Quill with custom toolbar
    quill = new Quill('#message-editor', {
        theme: 'snow',
        placeholder: 'Enter your announcement message...',
        modules: {
            toolbar: {
                container: [
                    [{ 'font': [] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ],
                handlers: {
                    image: imageHandler
                }
            }
        }
    });

    // Set initial content if exists
    if (config.oldMessage) {
        quill.root.innerHTML = config.oldMessage;
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
}

/**
 * Alpine.js component for the announcement form
 * @param {Object} config - Configuration object from Blade template
 * @param {string} config.csrfToken - CSRF token
 * @param {string} config.previewUrl - URL for recipient preview
 * @param {string} config.oldTitle - Previously entered title
 * @param {string} config.oldTargetType - Previously selected target type
 * @param {string} config.oldTargetId - Previously selected target ID
 * @param {boolean} config.hasOldActionUrl - Whether there was a previous action URL
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
        showActionFields: config.hasOldActionUrl || false,
        isSubmitting: false,

        get canSubmit() {
            return this.title.trim() !== '' && 
                   this.messageLength > 0 && 
                   this.targetType !== '' && 
                   this.selectedTargetId !== '';
        },

        init() {
            window.announcementFormInstance = this;
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
