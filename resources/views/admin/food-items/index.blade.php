@extends('layouts.admin')
@section('page-title', 'Food listings')
@section('content')
<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input type="search" name="search" id="filter-search" class="form-control" placeholder="Item name…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-restaurant">Restaurant</label>
            <select name="restaurant_id" id="filter-restaurant" class="form-select">
                <option value="">All</option>
                @foreach ($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}" @selected(request('restaurant_id') == $restaurant->id)>{{ $restaurant->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-category">Category</label>
            <select name="category_id" id="filter-category" class="form-select">
                <option value="">All</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->displayLabel() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-featured">Featured</label>
            <select name="featured" id="filter-featured" class="form-select">
                <option value="">All items</option>
                <option value="1" @selected(request('featured') === '1')>Featured only</option>
            </select>
        </div>
    </x-slot:filter>
    <x-slot:actions>
        @admincan('food_items', 'edit')
            <a href="{{ route('admin.food-items.create') }}" class="btn btn-brand">
                <i class="bi bi-plus-lg me-1"></i>Add food item
            </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Food listings" :paginator="$items">
    <thead>
        <tr>
            <th>Name</th>
            <th>Restaurant</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td class="fw-semibold">
                    {{ $item->name }}
                    @if ($item->is_featured)<span class="badge text-bg-light border ms-1">Featured</span>@endif
                </td>
                <td>{{ $item->restaurant?->name ?? '—' }}</td>
                <td>{{ $item->category?->displayLabel() ?? '—' }}</td>
                <td>{{ money_inr($item->price) }}</td>
                <td>
                    @if ($item->is_available && ! $item->is_sold_out)
                        <span class="status-badge status-delivered">Available</span>
                    @else
                        <span class="status-badge status-cancelled">Unavailable</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        @admincan('food_items', 'view')
                            <a href="{{ route('admin.food-items.show', $item) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @endadmincan
                        @admincan('food_items', 'edit')
                            <a href="{{ route('admin.food-items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        @endadmincan
                        @admincan('food_items', 'delete')
                            <form method="POST" action="{{ route('admin.food-items.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        @endadmincan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No food items match your filters.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
