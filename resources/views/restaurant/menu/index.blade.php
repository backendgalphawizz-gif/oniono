@extends('layouts.restaurant')

@section('page-title', 'Menu')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="h5 mb-1">Menu management</h2>
        <p class="text-muted small mb-0">Food items for your restaurant only.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('restaurant.menu-categories.index') }}" class="btn btn-outline-secondary btn-sm">Categories</a>
        <a href="{{ route('restaurant.menu.create') }}" class="btn btn-brand btn-sm">Add item</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->displayLabel() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <select name="availability" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All availability</option>
            <option value="available" @selected(request('availability') === 'available')>Available</option>
            <option value="sold_out" @selected(request('availability') === 'sold_out')>Sold out</option>
            <option value="unavailable" @selected(request('availability') === 'unavailable')>Unavailable</option>
        </select>
    </div>
</form>

<x-panel-table title="Menu items" :paginator="$items">
    <thead>
        <tr>
            <th>Item</th>
            <th>Category</th>
            <th>Price</th>
            <th>Prep</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if ($item->image)
                            <img src="{{ asset('storage/'.$item->image) }}" alt="" width="40" height="40" class="rounded border" style="object-fit: cover;">
                        @else
                            <span class="d-inline-flex align-items-center justify-content-center rounded border bg-light text-muted" style="width:40px;height:40px;font-size:0.7rem;">—</span>
                        @endif
                        <div>
                            <div class="fw-medium">{{ $item->name }}</div>
                            <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $item->food_type) }}</div>
                        </div>
                    </div>
                </td>
                <td class="small">{{ $item->category?->displayLabel() ?? '—' }}</td>
                <td>{{ money_inr($item->price) }}</td>
                <td>{{ $item->preparation_time }}m</td>
                <td>
                    @if ($item->is_sold_out)
                        <span class="badge text-bg-warning">Sold out</span>
                    @elseif ($item->is_available)
                        <span class="badge text-bg-success">Available</span>
                    @else
                        <span class="badge text-bg-secondary">Unavailable</span>
                    @endif
                </td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('restaurant.menu.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form method="POST" action="{{ route('restaurant.menu.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No menu items yet. Add your first dish.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
