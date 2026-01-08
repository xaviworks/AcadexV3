/**
 * Notification page Alpine.js component
 * Handles infinite scroll, mark as read, and real-time updates
 */

function notificationPageComponent(config) {
    return {
        items: config.initialNotifications || [],
        unreadCount: config.initialUnreadCount || 0,
        hasMore: config.hasMoreInitial || false,
        nextCursor: config.nextCursorInitial || null,
        currentCategory: config.currentCategory || 'all',
        loading: false,
        loadingMore: false,

        init() {
            // Listen for real-time notification events
            this.setupRealtimeListener();
        },

        /**
         * Setup real-time notification listener via Echo/Pusher
         */
        setupRealtimeListener() {
            // Check if Echo is available (Laravel Echo for broadcasting)
            if (typeof Echo !== 'undefined') {
                Echo.private(`App.Models.User.${window.userId}`)
                    .notification((notification) => {
                        this.handleNewNotification(notification);
                    });
            }
        },

        /**
         * Handle incoming real-time notification
         */
        handleNewNotification(notification) {
            // Add to top of list
            this.items.unshift({
                id: notification.id,
                type: notification.type,
                category: notification.category || 'general',
                priority: notification.priority || 'normal',
                icon: notification.icon || 'bi-bell',
                color: notification.color || 'info',
                message: notification.message,
                action_url: notification.action_url,
                action_text: notification.action_text,
                is_read: false,
                time_ago: 'Just now',
                created_at: notification.created_at,
            });

            this.unreadCount++;

            // Show toast notification
            if (window.notify) {
                window.notify.info(notification.message);
            }
        },

        /**
         * Load more notifications (infinite scroll)
         */
        async loadMore() {
            if (this.loadingMore || !this.hasMore) return;

            this.loadingMore = true;

            try {
                const params = new URLSearchParams({
                    cursor: this.nextCursor,
                    category: this.currentCategory,
                    limit: 20,
                });

                const response = await fetch(`/notifications/paginate?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error('Failed to load notifications');

                const data = await response.json();

                // Append new notifications
                this.items.push(...data.notifications);
                this.hasMore = data.has_more;
                this.nextCursor = data.next_cursor;

            } catch (error) {
                console.error('Error loading more notifications:', error);
                if (window.notify) {
                    window.notify.error('Failed to load more notifications');
                }
            } finally {
                this.loadingMore = false;
            }
        },

        /**
         * Mark a notification as read
         */
        async markAsRead(notification) {
            if (notification.is_read) return;

            try {
                const response = await fetch(`/notifications/${notification.id}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error('Failed to mark as read');

                const data = await response.json();

                // Update local state
                notification.is_read = true;
                this.unreadCount = data.unread_count;

            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        /**
         * Mark all notifications as read
         */
        async markAllAsRead() {
            if (this.unreadCount === 0) return;

            try {
                const response = await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error('Failed to mark all as read');

                // Update local state
                this.items.forEach(n => n.is_read = true);
                this.unreadCount = 0;

                if (window.notify) {
                    window.notify.success('All notifications marked as read');
                }

            } catch (error) {
                console.error('Error marking all as read:', error);
                if (window.notify) {
                    window.notify.error('Failed to mark all as read');
                }
            }
        },

        /**
         * Delete a notification
         */
        async deleteNotification(notification) {
            try {
                const response = await fetch(`/notifications/${notification.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error('Failed to delete notification');

                const data = await response.json();

                // Remove from list
                const index = this.items.findIndex(n => n.id === notification.id);
                if (index > -1) {
                    this.items.splice(index, 1);
                }

                this.unreadCount = data.unread_count;

                if (window.notify) {
                    window.notify.success('Notification deleted');
                }

            } catch (error) {
                console.error('Error deleting notification:', error);
                if (window.notify) {
                    window.notify.error('Failed to delete notification');
                }
            }
        },
    };
}

// Make component available globally
window.notificationPageComponent = notificationPageComponent;
