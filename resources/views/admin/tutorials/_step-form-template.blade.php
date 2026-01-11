<div class="card step-item" x-data="{ open: true }" data-step-index="{{ $index }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Step {{ $index + 1 }}: {{ $step->title ?? 'Untitled' }}</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="open = !open">
            <span x-show="!open"><i class="bi bi-chevron-down"></i></span>
            <span x-show="open"><i class="bi bi-chevron-up"></i></span>
        </button>
    </div>
    <div class="card-body" x-show="open" x-transition>
        @include('admin.tutorials._step-form-fields', ['index' => $index, 'step' => $step, 'positions' => $positions])
    </div>
</div>
