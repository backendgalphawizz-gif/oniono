@props([
    'method' => 'GET',
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'card card-soft panel-toolbar mb-3']) }}>
    <div class="card-body py-3">
        <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-end {{ isset($filter) ? 'justify-content-between' : 'justify-content-end' }} gap-3">
            @if (isset($filter))
                <form method="{{ $method }}" action="{{ $action ?? url()->current() }}" class="panel-filter-form flex-grow-1">
                    <div class="row g-2 align-items-end">
                        {{ $filter }}
                        <div class="col-12 col-sm-auto d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-outline-brand">
                                <i class="bi bi-funnel me-1"></i>Apply filters
                            </button>
                            @if (count(request()->except('page')) > 0)
                                <a href="{{ url()->current() }}" class="btn btn-light border">Clear</a>
                            @endif
                        </div>
                    </div>
                </form>
            @endif

            @if (isset($actions))
                <div class="panel-toolbar-actions d-flex flex-wrap gap-2 flex-shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
</div>
