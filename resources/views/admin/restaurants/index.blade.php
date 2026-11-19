@extends('layouts.admin')
@section('page-title', 'Restaurant Management')
@section('content')
<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input type="search" name="search" id="filter-search" class="form-control" placeholder="Name, owner, email…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-status">Registration</label>
            <select name="status" id="filter-status" class="form-select">
                <option value="">All</option>
                @foreach (['pending', 'approved', 'rejected', 'suspended'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-account">Account</label>
            <select name="account" id="filter-account" class="form-select">
                <option value="">All</option>
                @foreach (['active' => 'Active', 'blocked' => 'Blocked'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('account') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </x-slot:filter>
    <x-slot:actions>
        @admincan('restaurants', 'edit')
            <a href="{{ route('admin.restaurants.create') }}" class="btn btn-brand">
                <i class="bi bi-plus-lg me-1"></i>Add restaurant
            </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Restaurants" :paginator="$restaurants">
    <thead>
        <tr>
            <th>Restaurant</th>
            <th>Owner</th>
            <th>Contact</th>
            <th>Menu</th>
            <th>Orders</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($restaurants as $restaurant)
            @php
                $statusClass = match (true) {
                    $restaurant->is_blocked => 'status-cancelled',
                    $restaurant->status->value === 'approved' => 'status-delivered',
                    $restaurant->status->value === 'pending' => 'status-pending',
                    default => 'status-cancelled',
                };
            @endphp
            <tr>
                <td class="fw-semibold">{{ $restaurant->name }}</td>
                <td>{{ $restaurant->owner_name }}</td>
                <td>
                    <div>{{ $restaurant->email }}</div>
                    <div class="small text-muted">{{ $restaurant->phone }}</div>
                </td>
                <td>{{ $restaurant->food_items_count }}</td>
                <td>{{ $restaurant->orders_count }}</td>
                <td><span class="status-badge {{ $statusClass }}">{{ $restaurant->accountStatusLabel() }}</span></td>
                <td class="text-end">
                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                        @admincan('restaurants', 'view')
                            <a href="{{ route('admin.restaurants.show', $restaurant) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @endadmincan
                        @admincan('restaurants', 'edit')
                            <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        @endadmincan
                        @admincan('restaurants', 'delete')
                            <form method="POST" action="{{ route('admin.restaurants.destroy', $restaurant) }}" class="d-inline" onsubmit="return confirm('Delete this restaurant?');">
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
                <td colspan="7" class="text-center text-muted py-4">No restaurants match your filters.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
