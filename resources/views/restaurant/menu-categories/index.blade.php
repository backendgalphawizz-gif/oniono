@extends('layouts.restaurant')

@section('page-title', 'Menu categories')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="h5 mb-1">Categories</h2>
        <p class="text-muted small mb-0">Platform categories plus your own sub-categories for menu items.</p>
    </div>
    <a href="{{ route('restaurant.menu-categories.create') }}" class="btn btn-brand btn-sm">Add category</a>
</div>

@if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
@endif

<x-panel-form-card title="Your custom categories" subtitle="You can edit or delete only categories created by your restaurant.">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Under</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($myCategories as $category)
                    <tr>
                        <td class="fw-medium">{{ $category->name }}</td>
                        <td>{{ $category->parent?->name }}</td>
                        <td>{{ $category->food_items_count }}</td>
                        <td>
                            <span class="badge text-bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('restaurant.menu-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('restaurant.menu-categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted text-center py-3">No custom categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-panel-form-card>

<x-panel-form-card title="Platform categories" subtitle="Shared sub-categories from Oniono — use when adding menu items.">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Category</th><th>Main</th></tr></thead>
            <tbody>
                @foreach ($platformCategories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td class="text-muted">{{ $category->parent?->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-panel-form-card>
@endsection
