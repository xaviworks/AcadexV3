@props([
    'id',
    'title',
    'description' => null,
    'size' => 'medium',
    'variant' => 'default',
    'centered' => true,
    'scrollable' => false,
    'bodyClass' => '',
    'contentClass' => '',
    'headerClass' => '',
    'footerClass' => 'bg-light border-top',
    'closeable' => true,
    'backdrop' => true,
    'keyboard' => true,
    'form' => null,
    'formId' => null,
    'formActionExpression' => null,
    'noPageLoader' => false,
    'enctype' => null,
    'formClass' => '',
    'formAttributes' => '',
    'novalidate' => false,
    'closeAction' => null,
    'method' => 'POST',
    'show' => false,
])

@php
    $sizeClass = [
        'small' => 'modal-sm',
        'sm' => 'modal-sm',
        'medium' => '',
        'md' => '',
        'large' => 'modal-lg',
        'lg' => 'modal-lg',
        'extra-large' => 'modal-xl',
        'xl' => 'modal-xl',
        'fullscreen' => 'modal-fullscreen',
    ][$size] ?? '';

    $variantClasses = [
        'default' => ['header' => 'bg-white text-dark border-bottom', 'close' => ''],
        'form' => ['header' => 'bg-success text-white border-0', 'close' => 'btn-close-white'],
        'success' => ['header' => 'bg-success text-white border-0', 'close' => 'btn-close-white'],
        'info' => ['header' => 'bg-info text-white border-0', 'close' => 'btn-close-white'],
        'warning' => ['header' => 'bg-warning text-dark border-0', 'close' => ''],
        'danger' => ['header' => 'bg-danger text-white border-0', 'close' => 'btn-close-white'],
        'error' => ['header' => 'bg-danger text-white border-0', 'close' => 'btn-close-white'],
    ][$variant] ?? ['header' => 'bg-white text-dark border-bottom', 'close' => ''];

    $dialogClasses = trim(collect([
        'modal-dialog',
        $centered ? 'modal-dialog-centered' : null,
        $scrollable ? 'modal-dialog-scrollable' : null,
        $sizeClass,
    ])->filter()->implode(' '));

    $modalClasses = 'modal fade' . ($show ? ' show d-block' : '');
    $ariaHidden = $show ? 'false' : 'true';
    $labelId = $id . 'Label';
@endphp

<div
    {{ $attributes->merge(['class' => $modalClasses]) }}
    id="{{ $id }}"
    tabindex="-1"
    aria-labelledby="{{ $labelId }}"
    aria-hidden="{{ $ariaHidden }}"
    @if($backdrop === false || $backdrop === 'static') data-bs-backdrop="static" @endif
    @if(!$keyboard) data-bs-keyboard="false" @endif
>
    <div class="{{ $dialogClasses }}">
        @if($form !== null)
            <form
                method="{{ $method }}"
                action="{{ $form }}"
                @if($formActionExpression) :action="{!! $formActionExpression !!}" @endif
                @if($formId) id="{{ $formId }}" @endif
                @if($noPageLoader) data-no-page-loader @endif
                @if($enctype) enctype="{{ $enctype }}" @endif
                @if($formClass) class="{{ $formClass }}" @endif
                @if($novalidate) novalidate @endif
                {!! $formAttributes !!}
            >
        @endif

        <div class="modal-content border-0 shadow-lg {{ $contentClass }}">
            <div class="modal-header {{ $headerClass ?: $variantClasses['header'] }}">
                <div class="d-flex align-items-start gap-2">
                    @isset($icon)
                        <span class="modal-title-icon flex-shrink-0">{{ $icon }}</span>
                    @endisset
                    <div>
                        <h5 class="modal-title fw-semibold mb-0" id="{{ $labelId }}">{{ $title }}</h5>
                        @if($description)
                            <p class="small mb-0 mt-1 opacity-75">{{ $description }}</p>
                        @endif
                    </div>
                </div>

                @if($closeable)
                    <button
                        type="button"
                        class="btn-close {{ $variantClasses['close'] }}"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @if($closeAction) onclick="{{ $closeAction }}" @endif
                    ></button>
                @endif
            </div>

            <div class="modal-body {{ $bodyClass }}">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="modal-footer {{ $footerClass }}">
                    {{ $footer }}
                </div>
            @endisset
        </div>

        @if($form !== null)
            </form>
        @endif
    </div>
</div>
