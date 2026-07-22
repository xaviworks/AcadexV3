@props([
    'primaryText' => null,
    'primaryVariant' => 'primary',
    'primaryType' => 'submit',
    'secondaryText' => 'Cancel',
    'secondaryDismiss' => true,
    'destructiveText' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $isDisabled = $loading || $disabled;
@endphp

<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 w-100">
    @if($secondaryText)
        <button
            type="button"
            class="btn btn-secondary"
            @if($secondaryDismiss) data-bs-dismiss="modal" @endif
            @disabled($loading)
        >
            {{ $secondaryText }}
        </button>
    @endif

    {{ $slot }}

    @if($destructiveText)
        <button type="{{ $primaryType }}" class="btn btn-danger" @disabled($isDisabled)>
            @if($loading)
                <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            @endif
            {{ $destructiveText }}
        </button>
    @elseif($primaryText)
        <button type="{{ $primaryType }}" class="btn btn-{{ $primaryVariant }}" @disabled($isDisabled)>
            @if($loading)
                <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            @endif
            {{ $primaryText }}
        </button>
    @endif
</div>
