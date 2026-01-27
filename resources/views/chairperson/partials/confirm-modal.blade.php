{{--
    Generic Confirmation Modal Partial
    
    Usage:
    @include('chairperson.partials.confirm-modal', [
        'id' => 'confirmDeleteModal',
        'title' => 'Confirm Delete',
        'headerClass' => 'bg-danger', // bg-danger, bg-success, bg-warning
        'formId' => 'deleteForm',
        'message' => 'Are you sure you want to delete this item?',
        'messageVar' => 'itemName', // JS variable name for dynamic content
        'confirmText' => 'Delete',
        'confirmClass' => 'btn-danger'
    ])
--}}

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="{{ $formId }}">
            @csrf
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header {{ $headerClass ?? 'bg-primary' }} text-white">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {!! $message ?? 'Are you sure?' !!}
                    @if(!empty($messageVar))
                        <strong id="{{ $messageVar }}"></strong>
                    @endif
                    @if(!empty($additionalMessage))
                        {!! $additionalMessage !!}
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn {{ $confirmClass ?? 'btn-primary' }}">{{ $confirmText ?? 'Confirm' }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
