@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5" x-data="notificationPage()" x-init="init()">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-bell text-primary me-2"></i>Notifications
            </h2>
            <p class="text-muted mb-0">
                @if($isAdmin)
                    <span class="badge bg-dark me-1"><i class="bi bi-shield-lock me-1"></i>Admin View</span>
                    Complete notification audit with detailed metadata
                @else
                    Stay updated with your latest notifications
                @endif
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Category Filter --}}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-filter me-1"></i>
                    {{ $categories[$currentCategory] ?? 'All' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($categories as $key => $label)
                        <li>
                            <a class="dropdown-item {{ $currentCategory === $key ? 'active' : '' }}" 
                               href="{{ route('notifications.index', ['category' => $key === 'all' ? null : $key]) }}">
                                @switch($key)
                                    @case('academic')
                                        <i class="bi bi-journal-check me-2 text-success"></i>
                                        @break
                                    @case('security')
                                        <i class="bi bi-shield-fill me-2 text-danger"></i>
                                        @break
                                    @case('announcement')
                                        <i class="bi bi-megaphone me-2 text-primary"></i>
                                        @break
                                    @case('system')
                                        <i class="bi bi-gear me-2 text-secondary"></i>
                                        @break
                                    @default
                                        <i class="bi bi-bell me-2"></i>
                                @endswitch
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            {{-- Mark All as Read --}}
            <button @click="markAllAsRead()" 
                    :disabled="unreadCount === 0"
                    class="btn btn-outline-primary">
                <i class="bi bi-check-all me-1"></i>
                Mark All as Read
                <span x-show="unreadCount > 0" class="badge bg-primary ms-1" x-text="unreadCount"></span>
            </button>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            {{-- Loading State --}}
            <div x-show="loading && items.length === 0" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading notifications...</p>
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && items.length === 0" class="text-center py-5">
                <i class="bi bi-bell-slash text-muted display-1 d-block mb-3"></i>
                <h5 class="text-muted mb-2">No notifications yet</h5>
                <p class="text-muted mb-0">You're all caught up! New notifications will appear here.</p>
            </div>

            {{-- Notification Items --}}
            <div x-show="items.length > 0" class="notification-list">
                <template x-for="notification in items" :key="notification.id">
                    <div class="notification-item p-4 border-bottom position-relative"
                         :class="{ 'notification-unread': !notification.is_read }"
                         @click="markAsRead(notification)">
                        
                        {{-- Priority indicator for urgent/high --}}
                        <div x-show="notification.priority === 'urgent' || notification.priority === 'high'"
                             class="position-absolute top-0 start-0 h-100"
                             :class="notification.priority === 'urgent' ? 'bg-danger' : 'bg-warning'"
                             style="width: 4px;"></div>
                        
                        <div class="d-flex align-items-start">
                            {{-- Icon --}}
                            <div class="notification-icon me-3 flex-shrink-0"
                                 :class="'notification-icon-' + notification.color">
                                <i :class="notification.icon"></i>
                            </div>
                            
                            {{-- Content --}}
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                                    {{-- Unread Badge --}}
                                    <span x-show="!notification.is_read" class="badge bg-primary">New</span>
                                    
                                    {{-- Category Badge --}}
                                    <span class="badge"
                                          :class="{
                                              'bg-success-subtle text-success': notification.category === 'academic',
                                              'bg-danger-subtle text-danger': notification.category === 'security',
                                              'bg-primary-subtle text-primary': notification.category === 'announcement',
                                              'bg-secondary-subtle text-secondary': notification.category === 'system'
                                          }"
                                          x-text="notification.category.charAt(0).toUpperCase() + notification.category.slice(1)">
                                    </span>
                                    
                                    {{-- Priority Badge for Admin --}}
                                    @if($isAdmin)
                                    <span x-show="notification.priority && notification.priority !== 'normal'" 
                                          class="badge"
                                          :class="{
                                              'bg-danger': notification.priority === 'urgent',
                                              'bg-warning text-dark': notification.priority === 'high',
                                              'bg-info': notification.priority === 'low'
                                          }"
                                          x-text="notification.priority.toUpperCase()">
                                    </span>
                                    @endif
                                    
                                    <small class="text-muted ms-auto" x-text="notification.time_ago"></small>
                                </div>
                                
                                {{-- Message --}}
                                <p class="mb-2" 
                                   :class="{ 'fw-semibold': !notification.is_read }"
                                   x-text="notification.message"></p>
                                
                                {{-- Announcement Content (for announcement notifications) --}}
                                <div x-show="notification.category === 'announcement' && notification.announcement_content"
                                     class="announcement-content bg-light rounded p-3 mb-2">
                                    <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                                        <i class="bi bi-megaphone-fill text-primary"></i>
                                        <strong x-text="notification.announcement_title || 'Announcement'"></strong>
                                        <span x-show="notification.announcement_sender" class="text-muted small ms-auto">
                                            From: <span x-text="notification.announcement_sender"></span>
                                        </span>
                                    </div>
                                    <div class="announcement-body" x-html="notification.announcement_content"></div>
                                </div>
                                
                                {{-- Admin Extra Details --}}
                                @if($isAdmin)
                                <div x-show="notification.extra && Object.keys(notification.extra).length > 0" 
                                     class="notification-extra bg-light rounded p-2 mb-2 small">
                                    <template x-for="(value, key) in notification.extra" :key="key">
                                        <div class="d-flex gap-2" x-show="value !== null && value !== ''">
                                            <span class="text-muted text-capitalize" x-text="key.replace(/_/g, ' ') + ':'"></span>
                                            <span class="text-dark" x-text="typeof value === 'object' ? JSON.stringify(value) : value"></span>
                                        </div>
                                    </template>
                                </div>
                                @endif
                                
                                {{-- Action Button --}}
                                <div x-show="notification.action_url" class="mt-2">
                                    <a :href="notification.action_url" 
                                       class="btn btn-sm btn-outline-primary"
                                       @click.stop>
                                        <span x-text="notification.action_text || 'View Details'"></span>
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            
                            {{-- Actions --}}
                            <div class="dropdown ms-2 flex-shrink-0">
                                <button class="btn btn-link text-muted p-1" 
                                        type="button" 
                                        data-bs-toggle="dropdown"
                                        @click.stop>
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li x-show="!notification.is_read">
                                        <button class="dropdown-item" @click.stop="markAsRead(notification)">
                                            <i class="bi bi-check me-2"></i>Mark as read
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" @click.stop="deleteNotification(notification)">
                                            <i class="bi bi-trash me-2"></i>Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load More Trigger (Infinite Scroll) --}}
            <div x-ref="scrollTrigger" 
                 x-show="hasMore" 
                 x-intersect:enter="loadMore()"
                 class="text-center py-4">
                <div x-show="loadingMore" class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading more...</span>
                </div>
                <span x-show="!loadingMore" class="text-muted small">Scroll for more</span>
            </div>
            
            {{-- End of List --}}
            <div x-show="!hasMore && items.length > 0" class="text-center py-4 text-muted small">
                <i class="bi bi-check-circle me-1"></i>You've reached the end
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Notification page styles - inline for immediate availability */
.notification-list {
    max-height: calc(100vh - 250px);
    overflow-y: auto;
}

.notification-item {
    transition: all 0.2s ease;
    cursor: pointer;
    background-color: #fff;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-item:last-child {
    border-bottom: none !important;
}

.notification-unread {
    background-color: #f0f7ff !important;
    border-left: 3px solid var(--bs-primary, #0d6efd);
}

.notification-unread:hover {
    background-color: #e6f0ff !important;
}

.notification-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.notification-icon-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
.notification-icon-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
.notification-icon-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
.notification-icon-info { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
.notification-icon-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
.notification-icon-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }

.notification-extra {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.75rem;
    border-left: 3px solid #dee2e6;
    max-height: 150px;
    overflow-y: auto;
}

/* Announcement Content Styles */
.announcement-content {
    border-left: 3px solid #0d6efd;
    font-size: 0.9375rem;
}

.announcement-content .announcement-body {
    line-height: 1.6;
    color: #374151;
}

.announcement-content .announcement-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 0.5rem 0;
}

.announcement-content .announcement-body p {
    margin-bottom: 0.5rem;
}

.announcement-content .announcement-body p:last-child {
    margin-bottom: 0;
}

.badge.bg-success-subtle { background-color: rgba(25, 135, 84, 0.15) !important; }
.badge.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.15) !important; }
.badge.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.15) !important; }
.badge.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.15) !important; }

.min-width-0 { min-width: 0; }

@keyframes slideInFromTop {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.notification-item.new-notification {
    animation: slideInFromTop 0.3s ease-out;
}
</style>
@endpush

@push('scripts')
<script>
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

        setupRealtimeListener() {
            if (typeof Echo !== 'undefined' && window.userId) {
                Echo.private(`App.Models.User.${window.userId}`)
                    .notification((notification) => {
                        this.handleNewNotification(notification);
                    });
            }
        },

        handleNewNotification(notification) {
            const newItem = {
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
            };
            
            // Include announcement fields if present
            if (notification.category === 'announcement') {
                newItem.announcement_title = notification.announcement_title || null;
                newItem.announcement_content = notification.announcement_content || null;
                newItem.announcement_sender = notification.announcement_sender || null;
            }
            
            this.items.unshift(newItem);
            this.unreadCount++;
            if (window.notify) {
                window.notify.info(notification.message);
            }
        },

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
                notification.is_read = true;
                this.unreadCount = data.unread_count;
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

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

// Initialize with server-rendered data
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationPage', () => notificationPageComponent({
        initialNotifications: @json($notifications),
        initialUnreadCount: {{ $unreadCount }},
        hasMoreInitial: {{ $hasMore ? 'true' : 'false' }},
        nextCursorInitial: @json($nextCursor),
        currentCategory: @json($currentCategory),
    }));
});
</script>
@endpush
