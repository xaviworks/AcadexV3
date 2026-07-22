{{--
    Generic Confirmation Modal Partial
--}}

@php
    $headerClassValue = $headerClass ?? 'bg-primary';
    $variant = str_contains($headerClassValue, 'danger') ? 'danger' : (str_contains($headerClassValue, 'warning') ? 'warning' : (str_contains($headerClassValue, 'success') ? 'success' : 'default'));
    $buttonVariant = str_replace('btn-', '', $confirmClass ?? 'btn-primary');
@endphp

<x-modal.confirmation
    :id="$id"
    :title="$title"
    :variant="$variant"
    form=""
    :form-id="$formId"
>
    {!! $message ?? 'Are you sure?' !!}
    @if(!empty($messageVar))
        <strong id="{{ $messageVar }}"></strong>
    @endif
    @if(!empty($additionalMessage))
        {!! $additionalMessage !!}
    @endif

    <x-slot:footer>
        <x-modal.actions :primary-text="$confirmText ?? 'Confirm'" :primary-variant="$buttonVariant" />
    </x-slot:footer>
</x-modal.confirmation>
