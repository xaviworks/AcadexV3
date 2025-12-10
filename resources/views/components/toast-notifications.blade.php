{{-- Toast Notification Component - Uses Alpine store for state management --}}
<div x-data 
     x-show="$store.notifications.items.length > 0"
     class="position-fixed top-0 end-0 p-3" 
     style="z-index: 9999; max-width: 400px;">
    
    <template x-for="notification in $store.notifications.items" :key="notification.id">
        <div x-show="true"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 transform translate-x-full"
             class="toast show mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true">
            
            <div class="toast-header" :class="`bg-${notification.type} text-white`">
                <i class="bi me-2" 
                   :class="{
                       'bi-check-circle-fill': notification.type === 'success',
                       'bi-exclamation-triangle-fill': notification.type === 'warning',
                       'bi-x-circle-fill': notification.type === 'danger',
                       'bi-info-circle-fill': notification.type === 'info'
                   }"></i>
                <strong class="me-auto" x-text="notification.type.charAt(0).toUpperCase() + notification.type.slice(1)"></strong>
                <button type="button" 
                        class="btn-close btn-close-white" 
                        @click="$store.notifications.remove(notification.id)"
                        aria-label="Close"></button>
            </div>
            <div class="toast-body" x-text="notification.message"></div>
        </div>
    </template>
</div>
