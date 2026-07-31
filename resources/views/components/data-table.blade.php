@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'bi-table',
    'variant' => 'success',
    'tableId' => null,
    'tableClass' => '',
    'responsiveClass' => '',
    'compact' => false,
    'bordered' => true,
    'hover' => true,
    'paginated' => true,
    'toolbarInHeader' => false,
    'dataTablesControlsInHeader' => false,
    'scrollY' => null,
])

@php
    $tableClasses = trim(implode(' ', array_filter([
        'table acadex-table align-middle mb-0',
        $bordered ? 'table-bordered' : null,
        $hover ? 'table-hover' : null,
        $compact ? 'table-sm' : null,
        $tableClass,
    ])));

    $responsiveStyle = $scrollY ? '--acadex-table-max-height: ' . $scrollY . ';' : null;
@endphp

<section {{ $attributes->merge([
    'class' => 'acadex-table-card',
    'data-acadex-table-card' => $paginated ? 'true' : 'false',
]) }}>
    @if($title || $subtitle || isset($actions) || $dataTablesControlsInHeader)
        <div class="acadex-table-card__header acadex-table-card__header--{{ $variant }}">
            <div class="acadex-table-card__title-group">
                @if($title)
                    <h2 class="acadex-table-card__title">
                        <i class="bi {{ $icon }}" aria-hidden="true"></i>
                        <span>{{ $title }}</span>
                    </h2>
                @endif

                @if($subtitle)
                    <p class="acadex-table-card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if(isset($actions) || ($paginated && $toolbarInHeader) || $dataTablesControlsInHeader)
                <div class="acadex-table-card__actions">
                    @isset($actions)
                        {{ $actions }}
                    @endisset

                    @if($paginated && $toolbarInHeader)
                        <label class="acadex-table-card__length acadex-table-card__length--header">
                            <span>Show</span>
                            <select class="form-select form-select-sm" data-acadex-page-size aria-label="Rows per page">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>entries</span>
                        </label>
                    @endif

                    @if($dataTablesControlsInHeader)
                        <div class="acadex-table-card__datatable-controls" data-acadex-datatables-header-controls></div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    @isset($filters)
        <div class="acadex-table-card__filters">
            {{ $filters }}
        </div>
    @endisset

    @if($paginated && ! $toolbarInHeader)
        <div class="acadex-table-card__toolbar" data-acadex-table-toolbar>
            <label class="acadex-table-card__length">
                <span>Show</span>
                <select class="form-select form-select-sm" data-acadex-page-size aria-label="Rows per page">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entries</span>
            </label>
        </div>
    @endif

    @isset($tabs)
        <div class="acadex-table-card__tabs">
            {{ $tabs }}
        </div>
    @endisset

    <div class="acadex-table-responsive {{ $responsiveClass }}" @if($responsiveStyle) style="{{ $responsiveStyle }}" @endif>
        <table @if($tableId) id="{{ $tableId }}" @endif class="{{ $tableClasses }}">
            {{ $slot }}
        </table>
    </div>

    @isset($pagination)
        <div class="acadex-table-card__pagination">
            {{ $pagination }}
        </div>
    @else
        @if($paginated)
            <div class="acadex-table-card__pagination" data-acadex-pagination>
                <div class="acadex-table-card__page-summary" data-acadex-page-summary></div>
                <div class="acadex-table-card__pager" aria-label="Table pagination">
                    <button type="button" class="btn btn-outline-success btn-sm" data-acadex-prev-page>
                        Previous
                    </button>
                    <span class="acadex-table-card__page-indicator" data-acadex-page-indicator>Page 1</span>
                    <button type="button" class="btn btn-outline-success btn-sm" data-acadex-next-page>
                        Next
                    </button>
                </div>
            </div>
        @endif
    @endisset
</section>
