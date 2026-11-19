@extends('layouts.admin')
@section('page-title', 'Categories')
@section('content')
@if ($errors->has('category'))
    <div class="alert alert-danger">{{ $errors->first('category') }}</div>
@endif

<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-5 col-lg-4 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input type="search" name="search" id="filter-search" class="form-control" placeholder="Category name…" value="{{ request('search') }}">
        </div>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-status">Status</label>
            <select name="status" id="filter-status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
    </x-slot:filter>
    <x-slot:actions>
        <a href="{{ route('admin.sub-categories.index') }}" class="btn btn-outline-brand">Sub-categories</a>
        @admincan('categories', 'edit')
        <a href="{{ route('admin.categories.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i>Add category
        </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Main categories" :paginator="$categories">
    <thead>
        <tr>
            <th>Name</th>
            <th>Sub-categories</th>
            <th>Items</th>
            <th>Sort</th>
            <th>Active</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($categories as $category)
            <tr>
                <td class="fw-semibold">{{ $category->name }}</td>
                <td>
                    <a href="{{ route('admin.sub-categories.index', ['parent_id' => $category->id]) }}">{{ $category->children_count }}</a>
                </td>
                <td>{{ $category->food_items_count }}</td>
                <td>{{ $category->sort_order }}</td>
                <td>
                    @if ($category->is_active)
                        <span class="status-badge status-delivered">Yes</span>
                    @else
                        <span class="status-badge status-default">No</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        @admincan('categories', 'edit')
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        @endadmincan
                        @admincan('categories', 'delete')
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Delete this category?');">
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
                <td colspan="6" class="text-center text-muted py-4">No main categories yet. Create one, then add sub-categories.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
