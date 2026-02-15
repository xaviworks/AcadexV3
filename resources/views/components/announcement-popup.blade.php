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
                    <i class="fs-5" 
                       :class="currentAnnouncement.icon ? 'fas ' + currentAnnouncement.icon : (currentAnnouncement.type === 'info' ? 'bi bi-info-circle-fill' : (currentAnnouncement.type === 'success' ? 'bi bi-check-circle-fill' : (currentAnnouncement.type === 'warning' ? 'bi bi-exclamation-triangle-fill' : 'bi bi-x-circle-fill')))"></i>
                    <h5 class="alert-heading fw-bold mb-0" id="announcement-title" x-text="currentAnnouncement.title"></h5>
                </div>
                
                <!-- Message -->
                <div class="mb-2" x-html="currentAnnouncement.message" style="line-height: 1.6;"></div>
                
                <!-- Compact Footer -->
                <div class="d-flex justify-content-between align-items-center mt-2">
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
    // Keep last-seen JSON outside Alpine proxy for cheap diff
    let _lastJson = '';
    let _pollTimer = null;

    return {
        announcements: [],
        currentIndex: 0,
        polling: false,

        get currentAnnouncement() {
            return this.announcements[this.currentIndex] || null;
        },

        /* ── Bootstrap: first fetch + start polling ── */
        async fetchAnnouncements() {
            await this._doFetch();
            this._startPolling();
        },

        /* ── Core fetch logic (reused by init & poll) ── */
        async _doFetch() {
            try {
                const response = await fetch('/announcements/active', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) return;

                const text = await response.text();

                // Smart diff — skip DOM work if nothing changed
                if (text === _lastJson) return;
                _lastJson = text;

                const data = JSON.parse(text);

                if (data.length === 0) {
                    this.announcements = [];
                    return;
                }

                // Get dismissed announcements from session storage
                let dismissedInSession = JSON.parse(sessionStorage.getItem('dismissedAnnouncements') || '[]')
                    .map(id => parseInt(id, 10));

                // Get all valid announcement IDs from server response
                const validAnnouncementIds = data.map(ann => parseInt(ann.id, 10));

                // Auto-cleanup: remove stale dismissed IDs
                const hasStaleIds = dismissedInSession.some(id => !validAnnouncementIds.includes(id));
                if (hasStaleIds) {
                    dismissedInSession = dismissedInSession.filter(id => validAnnouncementIds.includes(id));
                    sessionStorage.setItem('dismissedAnnouncements', JSON.stringify(dismissedInSession));
                }

                // Filter: Remove only announcements dismissed in this session
                const filtered = data.filter(ann => {
                    return !dismissedInSession.includes(parseInt(ann.id, 10));
                });

                // Preserve current view when user is reading an announcement
                // Only reset index if the list actually changed
                const currentIds = this.announcements.map(a => a.id).join(',');
                const newIds = filtered.map(a => a.id).join(',');

                if (currentIds !== newIds) {
                    this.announcements = filtered;
                    // Keep index in bounds
                    if (this.currentIndex >= this.announcements.length) {
                        this.currentIndex = Math.max(0, this.announcements.length - 1);
                    }
                }

            } catch (error) {
                // Silently fail
            }
        },

        /* ── Polling engine (30s) + Page Visibility API ── */
        _startPolling() {
            this.polling = true;

            // Poll every 30 seconds (announcements are infrequent)
            _pollTimer = setInterval(() => this._doFetch(), 30000);

            // Pause when tab is hidden, resume immediately when visible
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    if (_pollTimer) { clearInterval(_pollTimer); _pollTimer = null; }
                } else {
                    this._doFetch(); // instant fetch on return
                    if (!_pollTimer) {
                        _pollTimer = setInterval(() => this._doFetch(), 30000);
                    }
                }
            });
        },

        async dismissCurrentAnnouncement() {
            if (!this.currentAnnouncement) return;

            const announcementId = this.currentAnnouncement.id;

            // Save to session storage to prevent re-showing during CURRENT session
            const dismissedInSession = JSON.parse(sessionStorage.getItem('dismissedAnnouncements') || '[]');
            if (!dismissedInSession.includes(announcementId)) {
                dismissedInSession.push(announcementId);
                sessionStorage.setItem('dismissedAnnouncements', JSON.stringify(dismissedInSession));
            }

            // Mark as viewed in backend (for show_once tracking)
            try {
                await fetch(`/announcements/${announcementId}/view`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
            } catch (error) {
                // Silently fail
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
            if (this.currentAnnouncement && this.currentAnnouncement.is_dismissible) {
                this.dismissCurrentAnnouncement();
            }
        },

        logout() {
            sessionStorage.removeItem('dismissedAnnouncements');
            if (_pollTimer) { clearInterval(_pollTimer); _pollTimer = null; }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';

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
