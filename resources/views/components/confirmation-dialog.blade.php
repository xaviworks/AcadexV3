{{-- Alpine Confirmation Dialog Component --}}
<div x-data 
     x-show="$store.confirm.show" 
     x-transition.opacity 
     class="modal fade show" 
     style="display: block; z-index: 1055;" 
     tabindex="-1"
     @keydown.escape.window="$store.confirm.cancel()">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header with dynamic color based on type --}}
            <div class="modal-header border-0" 
                 :class="{
                     'bg-warning text-dark': $store.confirm.type === 'warning',
                     'bg-danger text-white': $store.confirm.type === 'danger',
                     'bg-info text-white': $store.confirm.type === 'info'
                 }">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="me-2"
                       :class="{
                           'bi bi-exclamation-triangle-fill': $store.confirm.type === 'warning',
                           'bi bi-exclamation-octagon-fill': $store.confirm.type === 'danger',
                           'bi bi-info-circle-fill': $store.confirm.type === 'info'
                       }"></i>
                    <span x-text="$store.confirm.title"></span>
                </h5>
                <button type="button" 
                        class="btn-close" 
                        :class="{ 'btn-close-white': $store.confirm.type !== 'warning' }"
                        @click="$store.confirm.cancel()" 
                        aria-label="Close"></button>
            </div>
            
            {{-- Body --}}
            <div class="modal-body p-4">
                <p class="mb-0" x-text="$store.confirm.message"></p>
            </div>
            
            {{-- Footer --}}
            <div class="modal-footer border-0 bg-light">
                <button type="button" 
                        class="btn btn-secondary" 
                        @click="$store.confirm.cancel()"
                        x-text="$store.confirm.cancelText"></button>
                <button type="button" 
                        class="btn"
                        :class="{
                            'btn-warning': $store.confirm.type === 'warning',
                            'btn-danger': $store.confirm.type === 'danger',
                            'btn-info': $store.confirm.type === 'info'
                        }"
                        @click="$store.confirm.confirm()"
                        x-text="$store.confirm.confirmText"></button>
            </div>
        </div>
    </div>
</div>

{{-- Confirmation dialog backdrop --}}
<div x-data 
     x-show="$store.confirm.show" 
     x-transition.opacity 
     class="modal-backdrop fade show" 
     style="z-index: 1054;"
     @click="$store.confirm.cancel()"></div>
