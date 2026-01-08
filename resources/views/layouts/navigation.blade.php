<header class="px-4 py-3 shadow-sm d-flex justify-content-between align-items-center header-stable" style="background-color: var(--dark-green); color: white; height: 70px; position: sticky; top: 0; z-index: 1020;">
    <!-- Left: Current Academic Period -->
    <div class="d-flex align-items-center" style="width: 400px; flex-shrink: 0;">
        <h1 class="mb-0 d-flex align-items-center" style="line-height: 1; width: 100%; height: 34px; font-size: 1rem; font-weight: 600;">
            <i class="bi bi-calendar-event me-2" style="font-size: 1.125rem; display: inline-block; width: 20px; height: 20px; text-align: center; flex-shrink: 0; line-height: 1;"></i>
            @php
                $activePeriod = \App\Models\AcademicPeriod::find(session('active_academic_period_id'));
                $user = Auth::user();
                
                // For VPAA and other roles without active period, get the latest/current period
                if (!$activePeriod && in_array($user->role, [2, 3, 5])) { // Dean, Admin, VPAA
                    $activePeriod = \App\Models\AcademicPeriod::orderBy('created_at', 'desc')->first();
                }
            @endphp
            @if($activePeriod && !in_array($user->role, [2, 3, 5])) <!-- Exclude Dean, Admin, VPAA -->
                @php
                    $semesterLabel = '';
                    $academicYear = $activePeriod->academic_year;

                    switch ($activePeriod->semester) {
                        case '1st':
                            $semesterLabel = 'First Semester';
                            break;
                        case '2nd':
                            $semesterLabel = 'Second Semester';
                            break;
                        case 'Summer':
                            $semesterLabel = 'Summer';
                            break;
                        default:
                            $semesterLabel = 'Unknown Semester';
                            break;
                    }

                @endphp
                
                <span class="badge bg-success bg-opacity-25 px-3 py-2 rounded-pill" style="white-space: nowrap; font-size: 0.8125rem; font-weight: 500; line-height: 1; height: 32px; display: inline-flex; align-items: center; min-width: 290px; justify-content: center; letter-spacing: -0.01em;">
                    Academic Year {{ $academicYear }}
                </span>
            @else
                <span class="badge bg-success bg-opacity-25 px-3 py-2 rounded-pill" style="white-space: nowrap; font-size: 0.8125rem; font-weight: 500; line-height: 1; height: 32px; display: inline-flex; align-items: center; min-width: 290px; justify-content: center; letter-spacing: -0.01em;">Dashboard</span>
            @endif
        </h1>    
    </div>

    <!-- Right: Reload + Notifications + Profile Dropdown -->
    <div class="d-flex align-items-center gap-2">
        @php
            $user = Auth::user();
            $showNotifications = in_array($user->role, [1, 4]); // Chairperson=1, GE Coordinator=4
            $nameParts = explode(' ', Auth::user()->name);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[count($nameParts) - 1] ?? '';
            $displayName = $firstName . ' ' . $lastName;
        @endphp
        
        <!-- Hard Refresh Button -->
        <div x-data="{ showTooltip: false }" class="position-relative">
            <button @click="hardRefresh()" 
                    @mouseenter="showTooltip = true" 
                    @mouseleave="showTooltip = false"
                    type="button" 
                    class="btn btn-link text-white p-2" 
                    style="min-width: 50px; min-height: 50px;">
                <i class="bi bi-arrow-clockwise fs-4" style="display: inline-block; line-height: 1;"></i>
            </button>
            <div x-show="showTooltip" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="position-absolute bg-dark text-white px-2 py-1 rounded shadow-sm"
                 style="top: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; font-size: 0.75rem; z-index: 1060;">
                Hard Refresh (Clear Cache)
            </div>
        </div>
        
        <!-- Notification Bell - Now available for all authenticated users -->
        <div class="position-relative" x-data="notificationBell" x-init="init()" x-cloak style="min-width: 50px;">
            <button @click="toggleDropdown" type="button" class="btn btn-link text-white position-relative p-2" style="min-width: 50px; min-height: 50px;">
                <i :class="unreadCount > 0 ? 'bi bi-bell-fill fs-4' : 'bi bi-bell fs-4'" style="display: inline-block; line-height: 1;"></i>
                <span x-show="unreadCount > 0" 
                      x-text="unreadCount > 99 ? '99+' : unreadCount"
                      class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="font-size: 0.65rem; min-width: 20px; line-height: 1.4;">
                </span>
            </button>
            
            <!-- Notification Dropdown -->
            <div x-show="showDropdown" 
                 @click.away="showDropdown = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 x-cloak
                 class="position-absolute end-0 mt-2 bg-white rounded-3 shadow-lg notification-dropdown"
                 style="width: 400px; max-height: 500px; z-index: 1050;">
                
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center sticky-top bg-white rounded-top-3">
                    <h6 class="mb-0 text-dark fw-semibold">
                        <i class="bi bi-bell me-2"></i>Notifications
                    </h6>
                    <button @click="markAllRead" 
                            x-show="unreadCount > 0"
                            class="btn btn-sm btn-link text-primary p-0">
                        Mark all read
                    </button>
                </div>
                
                <div class="notification-dropdown-body" style="max-height: 380px; overflow-y: auto;">
                    <!-- Loading State -->
                    <div x-show="loading" class="p-4 text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span>Loading...</span>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!loading && notifications.length === 0" class="p-4 text-center text-muted">
                        <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                        <small>No new notifications</small>
                    </div>
                    
                    <!-- Notification Items -->
                    <template x-for="notification in notifications" :key="notification.id">
                        <div class="notification-dropdown-item p-3 border-bottom" 
                             :class="{ 'bg-light': !notification.is_read }"
                             @click="handleNotificationClick(notification)"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-start">
                                <!-- Dynamic Icon -->
                                <div class="notification-icon-sm me-2 mt-1 rounded-circle d-flex align-items-center justify-content-center"
                                     :class="'notification-icon-' + (notification.color || 'info')"
                                     style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i :class="notification.icon || 'bi-bell'" style="font-size: 0.875rem;"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <!-- Badges -->
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <span x-show="!notification.is_read" class="badge bg-primary" style="font-size: 0.65rem;">New</span>
                                        <span class="badge"
                                              :class="{
                                                  'bg-success-subtle text-success': notification.category === 'academic',
                                                  'bg-danger-subtle text-danger': notification.category === 'security',
                                                  'bg-primary-subtle text-primary': notification.category === 'announcement',
                                                  'bg-secondary-subtle text-secondary': notification.category === 'system'
                                              }"
                                              style="font-size: 0.65rem;"
                                              x-text="notification.category ? notification.category.charAt(0).toUpperCase() + notification.category.slice(1) : 'General'">
                                        </span>
                                    </div>
                                    <!-- Message -->
                                    <p class="mb-1 small" 
                                       :class="{ 'fw-semibold text-dark': !notification.is_read, 'text-secondary': notification.is_read }"
                                       x-text="notification.message"></p>
                                    <!-- Time -->
                                    <small class="text-muted" x-text="notification.time_ago"></small>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="p-2 text-center border-top bg-white rounded-bottom-3 sticky-bottom">
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-link text-primary">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Profile Dropdown -->
        <div class="dropdown" style="min-width: 200px; position: relative; z-index: 2000;">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle hover-lift" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="position-relative" style="flex-shrink: 0;">
                <img src="{{ avatar($displayName, '259c59', 'fff', 76) }}"
                     alt="avatar"
                     class="rounded-circle me-2 border-2 border-success"
                     width="38"
                     height="38"
                     style="display: block;">
                <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-white" style="width: 10px; height: 10px;"></span>
            </div>
            <div class="d-flex flex-column ms-2" style="min-width: 100px;">
                <span class="fw-medium">{{ $displayName }}</span>
                <small class="text-success">Online</small>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="min-width: 280px; z-index: 2010 !important; position: absolute !important;" aria-labelledby="profileDropdown">
            <li class="px-3 py-3 border-bottom">
                <div class="d-flex align-items-center">
                    <img src="{{ avatar($displayName, '259c59', 'fff', 90) }}"
                         alt="avatar"
                         class="rounded-circle me-3"
                         width="45"
                         height="45">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-dark">{{ $displayName }}</span>
                        <span class="text-muted small text-truncate" style="max-width: 180px;">{{ Auth::user()->email }}</span>
                    </div>
                </div>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center py-2 px-3" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person-gear me-2 text-muted"></i>
                    <span>Profile Settings</span>
                </a>
            </li>
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center py-2 px-3 text-danger" data-bs-toggle="modal" data-bs-target="#signOutModal">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span>Sign Out</span>
                </button>
            </li>
        </ul>
    </div>
    </div>
</header>

{{-- Styles: resources/css/layout/navigation.css --}}

{{-- Hard Refresh Function --}}
<script>
function hardRefresh() {
    // Clear browser cache and reload
    if ('caches' in window) {
        // Clear all service worker caches
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }
    
    // Clear localStorage cache (if any app-specific cache exists)
    const cacheKeys = Object.keys(localStorage).filter(key => 
        key.includes('cache') || key.includes('Cache')
    );
    cacheKeys.forEach(key => localStorage.removeItem(key));
    
    // Clear sessionStorage
    sessionStorage.clear();
    
    // Force hard reload (bypass browser cache)
    // location.reload(true) is deprecated, using cache-busting URL instead
    const url = new URL(window.location.href);
    url.searchParams.set('_refresh', Date.now());
    window.location.href = url.toString();
}
</script>

@if($showNotifications ?? false)
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', () => ({
        notifications: [],
        unreadCount: 0,
        showDropdown: false,
        loading: false,
        lastPollTimestamp: null,
        pollInterval: null,
        seenNotificationIds: new Set(),
        
        init() {
            // Fetch unviewed count from server (persisted in database)
            this.fetchUnreadCount();
            // Setup real-time listener if Echo is available, otherwise use polling
            this.setupRealtimeListener();
            // Start polling for live updates (every 10 seconds)
            this.startPolling();
        },
        
        destroy() {
            // Clean up polling interval when component is destroyed
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        },
        
        setupRealtimeListener() {
            if (typeof Echo !== 'undefined' && window.userId) {
                Echo.private(`App.Models.User.${window.userId}`)
                    .notification((notification) => {
                        this.handleNewNotification(notification);
                    });
            }
        },
        
        startPolling() {
            // Initialize with current timestamp to only get new notifications
            this.lastPollTimestamp = new Date().toISOString();
            
            // Poll every 10 seconds for new notifications
            this.pollInterval = setInterval(() => {
                this.pollForNewNotifications();
            }, 10000);
        },
        
        async pollForNewNotifications() {
            try {
                const url = new URL('{{ route("notifications.poll") }}', window.location.origin);
                if (this.lastPollTimestamp) {
                    url.searchParams.set('since', this.lastPollTimestamp);
                }
                
                const response = await fetch(url.toString());
                if (!response.ok) return;
                
                const data = await response.json();
                
                // Update badge count
                this.unreadCount = data.count;
                
                // Process new notifications
                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        // Only show toast for truly new notifications we haven't seen
                        if (!this.seenNotificationIds.has(notification.id)) {
                            this.seenNotificationIds.add(notification.id);
                            this.handleNewNotification(notification);
                        }
                    });
                    
                    // Update timestamp for next poll
                    if (data.latest_timestamp) {
                        this.lastPollTimestamp = data.latest_timestamp;
                    }
                }
            } catch (error) {
                console.error('Error polling for notifications:', error);
            }
        },
        
        handleNewNotification(notification) {
            // Add to top of list if dropdown is open
            if (this.showDropdown) {
                // Check if notification already exists in the list
                const exists = this.notifications.some(n => n.id === notification.id);
                if (!exists) {
                    this.notifications.unshift({
                        id: notification.id,
                        message: notification.message,
                        category: notification.category || 'general',
                        icon: notification.icon || 'bi-bell',
                        color: notification.color || 'info',
                        action_url: notification.action_url,
                        is_read: false,
                        time_ago: 'Just now',
                    });
                }
            }
            
            // Update unread count badge
            this.fetchUnreadCount();
        },
        
        async fetchUnreadCount() {
            try {
                const response = await fetch('{{ route("notifications.unread-count") }}');
                const data = await response.json();
                this.unreadCount = data.count;
            } catch (error) {
                console.error('Error fetching notification count:', error);
            }
        },
        
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("notifications.unread") }}');
                const data = await response.json();
                this.notifications = data.notifications;
                // Mark all fetched notifications as seen
                this.notifications.forEach(n => this.seenNotificationIds.add(n.id));
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async toggleDropdown() {
            this.showDropdown = !this.showDropdown;
            if (this.showDropdown) {
                // Mark all as viewed in database (clears badge count)
                await this.markAllViewed();
                this.fetchNotifications();
            }
        },
        
        async markAllViewed() {
            try {
                const response = await fetch('{{ route("notifications.viewed") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (response.ok) {
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Error marking as viewed:', error);
            }
        },
        
        handleNotificationClick(notification) {
            this.markRead(notification.id);
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },
        
        async markRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    // Update local state
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.is_read = true;
                    }
                }
            } catch (error) {
                console.error('Error marking as read:', error);
            }
        },
        
        async markAllRead() {
            try {
                const response = await fetch('{{ route("notifications.read-all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (response.ok) {
                    this.notifications.forEach(n => n.is_read = true);
                    if (window.notify) {
                        window.notify.success('All notifications marked as read');
                    }
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        }
    }));
});
</script>
@else
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', () => ({
        notifications: [],
        unreadCount: 0,
        showDropdown: false,
        loading: false,
        lastPollTimestamp: null,
        pollInterval: null,
        seenNotificationIds: new Set(),
        
        init() {
            // Fetch unviewed count from server (persisted in database)
            this.fetchUnreadCount();
            // Setup real-time listener if Echo is available
            this.setupRealtimeListener();
            // Start polling for live updates (every 10 seconds)
            this.startPolling();
        },
        
        destroy() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        },
        
        setupRealtimeListener() {
            if (typeof Echo !== 'undefined' && window.userId) {
                Echo.private(`App.Models.User.${window.userId}`)
                    .notification((notification) => {
                        this.handleNewNotification(notification);
                    });
            }
        },
        
        startPolling() {
            this.lastPollTimestamp = new Date().toISOString();
            
            this.pollInterval = setInterval(() => {
                this.pollForNewNotifications();
            }, 10000);
        },
        
        async pollForNewNotifications() {
            try {
                const url = new URL('/notifications/poll', window.location.origin);
                if (this.lastPollTimestamp) {
                    url.searchParams.set('since', this.lastPollTimestamp);
                }
                
                const response = await fetch(url.toString());
                if (!response.ok) return;
                
                const data = await response.json();
                
                this.unreadCount = data.count;
                
                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        if (!this.seenNotificationIds.has(notification.id)) {
                            this.seenNotificationIds.add(notification.id);
                            this.handleNewNotification(notification);
                        }
                    });
                    
                    if (data.latest_timestamp) {
                        this.lastPollTimestamp = data.latest_timestamp;
                    }
                }
            } catch (error) {
                console.error('Error polling for notifications:', error);
            }
        },
        
        handleNewNotification(notification) {
            if (this.showDropdown) {
                const exists = this.notifications.some(n => n.id === notification.id);
                if (!exists) {
                    this.notifications.unshift({
                        id: notification.id,
                        message: notification.message,
                        category: notification.category || 'general',
                        icon: notification.icon || 'bi-bell',
                        color: notification.color || 'info',
                        action_url: notification.action_url,
                        is_read: false,
                        time_ago: 'Just now',
                    });
                }
            }
            
            // Update unread count badge
            this.fetchUnreadCount();
        },
        
        async fetchUnreadCount() {
            try {
                const response = await fetch('/notifications/unread-count');
                const data = await response.json();
                this.unreadCount = data.count;
            } catch (error) {
                console.error('Error fetching notification count:', error);
            }
        },
        
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await fetch('/notifications/unread');
                const data = await response.json();
                this.notifications = data.notifications;
                this.notifications.forEach(n => this.seenNotificationIds.add(n.id));
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async toggleDropdown() {
            this.showDropdown = !this.showDropdown;
            if (this.showDropdown) {
                await this.markAllViewed();
                this.fetchNotifications();
            }
        },
        
        async markAllViewed() {
            try {
                const response = await fetch('/notifications/viewed', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (response.ok) {
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Error marking as viewed:', error);
            }
        },
        
        handleNotificationClick(notification) {
            this.markRead(notification.id);
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },
        
        async markRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.is_read = true;
                    }
                }
            } catch (error) {
                console.error('Error marking as read:', error);
            }
        },
        
        async markAllRead() {
            try {
                const response = await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (response.ok) {
                    this.notifications.forEach(n => n.is_read = true);
                    if (window.notify) {
                        window.notify.success('All notifications marked as read');
                    }
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        }
    }));
});
</script>
@endif
