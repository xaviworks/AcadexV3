@props([
    'id',
    'title' => 'Warning',
    'description' => null,
    'size' => 'medium',
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
    'backdrop' => true,
    'keyboard' => true,
    'closeable' => true,
    'scrollable' => false,
    'bodyClass' => '',
    'contentClass' => '',
    'headerClass' => '',
    'footerClass' => 'bg-light border-top',
])

<x-modal.base
    :id="$id"
    :title="$title"
    :description="$description"
    :size="$size"
    variant="warning"
    :form="$form"
    :form-id="$formId"
    :form-action-expression="$formActionExpression"
    :no-page-loader="$noPageLoader"
    :enctype="$enctype"
    :form-class="$formClass"
    :form-attributes="$formAttributes"
    :novalidate="$novalidate"
    :close-action="$closeAction"
    :method="$method"
    :show="$show"
    :backdrop="$backdrop"
    :keyboard="$keyboard"
    :closeable="$closeable"
    :scrollable="$scrollable"
    :body-class="$bodyClass"
    :content-class="$contentClass"
    :header-class="$headerClass"
    :footer-class="$footerClass"
    {{ $attributes }}
>
    @isset($icon)
        <x-slot:icon>
            {{ $icon }}
        </x-slot:icon>
    @endisset

    {{ $slot }}

    @isset($footer)
        <x-slot:footer>
            {{ $footer }}
        </x-slot:footer>
    @endisset
</x-modal.base>
