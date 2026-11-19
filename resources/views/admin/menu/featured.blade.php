@extends('layouts.admin')
@section('page-title', 'Featured')
@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <x-panel-table title="Featured restaurants">
            <thead><tr><th>Restaurant</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($featuredRestaurants as $restaurant)
                    <tr>
                        <td class="fw-semibold">{{ $restaurant->name }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.menu-management.restaurants.featured', $restaurant) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Remove</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-4">No featured restaurants yet.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>

        <x-panel-form-card title="Add featured restaurant" class="mt-3">
            <form method="GET" action="{{ route('admin.menu-management.featured') }}" class="panel-form mb-3">
                <div class="input-group input-group-sm">
                    <input type="search" name="restaurant_search" class="form-control" placeholder="Search restaurants…" value="{{ request('restaurant_search') }}">
                    <button class="btn btn-outline-brand" type="submit">Search</button>
                </div>
            </form>
            <ul class="list-group list-group-flush">
                @forelse ($restaurantSearch as $restaurant)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>{{ $restaurant->name }}</span>
                        <form method="POST" action="{{ route('admin.menu-management.restaurants.featured', $restaurant) }}">@csrf<button class="btn btn-sm btn-success">Feature</button></form>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted small">Search to find restaurants to feature.</li>
                @endforelse
            </ul>
        </x-panel-form-card>
    </div>

    <div class="col-lg-6">
        <x-panel-table title="Featured food items">
            <thead><tr><th>Item</th><th>Restaurant</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($featuredItems as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->name }}</td>
                        <td>{{ $item->restaurant?->name ?? '—' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.menu-management.food-items.featured', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Remove</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No featured food items yet.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>

        <x-panel-form-card title="Add featured food item" class="mt-3">
            <form method="GET" action="{{ route('admin.menu-management.featured') }}" class="panel-form mb-3">
                <div class="input-group input-group-sm">
                    <input type="search" name="food_search" class="form-control" placeholder="Search menu items…" value="{{ request('food_search') }}">
                    <button class="btn btn-outline-brand" type="submit">Search</button>
                </div>
            </form>
            <ul class="list-group list-group-flush">
                @forelse ($foodSearch as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>{{ $item->name }} <span class="text-muted small">· {{ $item->restaurant?->name }}</span></span>
                        <form method="POST" action="{{ route('admin.menu-management.food-items.featured', $item) }}">@csrf<button class="btn btn-sm btn-success">Feature</button></form>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted small">Search to find dishes to feature.</li>
                @endforelse
            </ul>
        </x-panel-form-card>
    </div>
</div>
@endsection
