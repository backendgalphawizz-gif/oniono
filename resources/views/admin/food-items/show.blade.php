@extends('layouts.admin')
@section('page-title', $foodItem->name)
@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-1">{{ $foodItem->name }}</h2>
                        <p class="text-muted small mb-0">{{ $foodItem->restaurant?->name }} · {{ $foodItem->category?->displayLabel() }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        @if ($foodItem->is_featured)<span class="status-badge status-pending">Featured</span>@endif
                        @if ($foodItem->is_available && ! $foodItem->is_sold_out)<span class="status-badge status-delivered">Available</span>@else<span class="status-badge status-cancelled">Unavailable</span>@endif
                    </div>
                </div>
                <dl class="row small mb-0">
                    <dt class="col-sm-4 text-muted">Price</dt>
                    <dd class="col-sm-8">{{ money_inr($foodItem->price) }}</dd>
                    <dt class="col-sm-4 text-muted">Food type</dt>
                    <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $foodItem->food_type)) }}</dd>
                    @if ($foodItem->description)
                        <dt class="col-sm-4 text-muted">Description</dt>
                        <dd class="col-sm-8">{{ $foodItem->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @if ($foodItem->variants->isNotEmpty() || $foodItem->addons->isNotEmpty())
            <div class="card card-soft mt-3">
                <div class="card-body">
                    @if ($foodItem->variants->isNotEmpty())
                        <h3 class="h6">Variants</h3>
                        <ul class="small">@foreach ($foodItem->variants as $v)<li>{{ $v->name }} — {{ money_inr($v->price) }}</li>@endforeach</ul>
                    @endif
                    @if ($foodItem->addons->isNotEmpty())
                        <h3 class="h6 mt-2">Add-ons</h3>
                        <ul class="small mb-0">@foreach ($foodItem->addons as $a)<li>{{ $a->name }} — {{ money_inr($a->price) }}</li>@endforeach</ul>
                    @endif
                </div>
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="card card-soft">
            <div class="card-body panel-action-stack">
                <a href="{{ route('admin.food-items.edit', $foodItem) }}" class="btn btn-outline-primary btn-sm">Edit listing</a>
                <form method="POST" action="{{ route('admin.menu-management.food-items.featured', $foodItem) }}">@csrf<button type="submit" class="btn btn-outline-secondary btn-sm">{{ $foodItem->is_featured ? 'Remove from featured' : 'Mark as featured' }}</button></form>
                <div class="panel-action-stack__danger">
                    <form method="POST" action="{{ route('admin.food-items.destroy', $foodItem) }}" onsubmit="return confirm('Delete this food item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete item</button>
                    </form>
                </div>
            </div>
        </div>
        @unless ($canEditPrice)
            <div class="alert alert-warning small mt-3 mb-0">Price editing is disabled in platform settings. You can still update availability and featured status.</div>
        @endunless
    </div>
</div>
@endsection
