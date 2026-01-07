<!-- System Announcements Modal Component (Blocks Interaction) -->
<div x-data="announcementPopup()" x-init="fetchAnnouncements()" @keydown.escape.window="handleEscape()">
    <!-- Backdrop Overlay - Blocks interaction with page -->
    <div 
        x-show="currentAnnouncement"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="announcement-backdrop"
        style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 9998; backdrop-filter: blur(2px);">
    </div>
    
    <!-- Compact Top-Middle Announcement -->
    <div 
        x-show="currentAnnouncement"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate(-50%, -10px)"
        x-transition:enter-end="opacity-100 transform translate(-50%, 0)"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate(-50%, 0)"
        x-transition:leave-end="opacity-0 transform translate(-50%, -10px)"
        class="position-fixed announcement-modal"
        style="top: 80px; left: 50%; transform: translateX(-50%); z-index: 9999; width: 90%; max-width: 600px;">
        
        <template x-if="currentAnnouncement">
            <div 
                class="alert shadow-lg border-0 rounded-4 mb-0"
                :class="{
                    'alert-info': currentAnnouncement.type === 'info',
                    'alert-success': currentAnnouncement.type === 'success',
                    'alert-warning': currentAnnouncement.type === 'warning',
                    'alert-danger': currentAnnouncement.type === 'danger'
                }"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="announcement-title">
                
                <!-- Compact Header -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi fs-5" 
                       :class="{
                           'bi-info-circle-fill': currentAnnouncement.type === 'info',
                           'bi-check-circle-fill': currentAnnouncement.type === 'success',
                           'bi-exclamation-triangle-fill': currentAnnouncement.type === 'warning',
                           'bi-x-circle-fill': currentAnnouncement.type === 'danger'
                       }"></i>
                    <h5 class="alert-heading fw-bold mb-0" id="announcement-title" x-text="currentAnnouncement.title"></h5>
                </div>
                
                <!-- Message -->
                <div class="mb-2" x-html="currentAnnouncement.message" style="line-height: 1.6;"></div>
                
                <!-- Compact Footer -->
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <small class="text-muted" x-show="announcements.length > 1" x-text="`${currentIndex + 1} of ${announcements.length}`"></small>
                    <div class="d-flex gap-1 ms-auto">
                        <button 
                            x-show="announcements.length > 1"
                            @click="previousAnnouncement()"
                            :disabled="currentIndex === 0"
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            style="padding: 0.15rem 0.4rem;">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button 
                            x-show="announcements.length > 1"
                            @click="nextAnnouncement()"
                            :disabled="currentIndex === announcements.length - 1"
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            style="padding: 0.15rem 0.4rem;">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button 
                            @click="currentAnnouncement.is_dismissible ? dismissCurrentAnnouncement() : logout()"
                            type="button"
                            class="btn btn-sm"
                            :class="{
                                'btn-primary': currentAnnouncement.type === 'info',
                                'btn-success': currentAnnouncement.type === 'success',
                                'btn-warning': currentAnnouncement.type === 'warning',
                                'btn-danger': currentAnnouncement.type === 'danger'
                            }"
                            x-text="currentAnnouncement.is_dismissible ? 'Got it' : 'Logout'">
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<style>
/* Compact top-middle announcement with backdrop */
.announcement-backdrop {
    pointer-events: auto;
}

.announcement-modal {
    pointer-events: auto;
}

.announcement-modal .alert {
    padding: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .announcement-modal {
        width: 95% !important;
        top: 70px !important;
    }
}

@media (max-width: 576px) {
    .announcement-modal {
        width: 95% !important;
        top: 60px !important;
    }
    
    .announcement-modal .alert {
        padding: 0.85rem;
    }
    
    .announcement-modal .alert-heading {
        font-size: 1rem;
    }
}
</style>

<script>
function announcementPopup() {
    return {
        announcements: [],
        currentIndex: 0,
        
        get currentAnnouncement() {
            return this.announcements[this.currentIndex] || null;
        },
        
        async fetchAnnouncements() {
            try {
                const response = await fetch('/announcements/active', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (!response.ok) return;
                
                const data = await response.json();
                
                // Check session storage for dismissed announcements (convert to numbers for comparison)
                const dismissedInSession = JSON.parse(sessionStorage.getItem('dismissedAnnouncements') || '[]')
                    .map(id => parseInt(id, 10));
                
                // Filter out any announcements already dismissed in this session
                this.announcements = data.filter(ann => !dismissedInSession.includes(parseInt(ann.id, 10)));
                
            } catch (error) {
                console.error('Failed to fetch announcements:', error);
            }
        },
        
        async dismissCurrentAnnouncement() {
            if (!this.currentAnnouncement) return;
            
            const announcementId = this.currentAnnouncement.id;
            const showOnce = this.currentAnnouncement.show_once;
            
            // Save to session storage for this session (prevents showing again on page refresh)
            const dismissedInSession = JSON.parse(sessionStorage.getItem('dismissedAnnouncements') || '[]');
            if (!dismissedInSession.includes(announcementId)) {
                dismissedInSession.push(announcementId);
                sessionStorage.setItem('dismissedAnnouncements', JSON.stringify(dismissedInSession));
            }
            
            // Mark as viewed in backend (for analytics and show_once tracking)
            try {
                await fetch(`/announcements/${announcementId}/view`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
            } catch (error) {
                console.error('Failed to mark announcement as viewed:', error);
            }
            
            // Remove from array
            this.announcements.splice(this.currentIndex, 1);
            
            // Adjust current index if needed
            if (this.currentIndex >= this.announcements.length && this.currentIndex > 0) {
                this.currentIndex = this.announcements.length - 1;
            }
        },
        
        nextAnnouncement() {
            if (this.currentIndex < this.announcements.length - 1) {
                this.currentIndex++;
            }
        },
        
        previousAnnouncement() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },
        
        handleEscape() {
            // Allow ESC key to close announcement only if dismissible
            if (this.currentAnnouncement && this.currentAnnouncement.is_dismissible) {
                this.dismissCurrentAnnouncement();
            }
        },
        
        logout() {
            // Create a form and submit POST request to logout
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
