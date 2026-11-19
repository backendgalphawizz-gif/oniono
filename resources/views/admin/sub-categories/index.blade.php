@extends('layouts.admin')
@section('page-title', 'Sub-categories')
@section('content')
<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input type="search" name="search" id="filter-search" class="form-control" placeholder="Name or main category…" value="{{ request('search') }}">
        </div>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-parent">Main category</label>
            <select name="parent_id" id="filter-parent" class="form-select">
                <option value="">All</option>
                @foreach ($mainCategories as $main)
                    <option value="{{ $main->id }}" @selected((string) request('parent_id') === (string) $main->id)>{{ $main->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-status">Status</label>
            <select name="status" id="filter-status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
    </x-slot:filter>
    <x-slot:actions>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-brand">Main categories</a>
        @admincan('sub_categories', 'edit')
        <a href="{{ route('admin.sub-categories.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i>Add sub-category
        </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Sub-categories" :paginator="$subCategories">
    <thead>
        <tr>
            <th>Sub-category</th>
            <th>Main category</th>
            <th>Items</th>
            <th>Sort</th>
            <th>Active</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($subCategories as $subCategory)
            <tr>
                <td class="fw-semibold">{{ $subCategory->name }}</td>
                <td>{{ $subCategory->parent?->name ?? '—' }}</td>
                <td>{{ $subCategory->food_items_count }}</td>
                <td>{{ $subCategory->sort_order }}</td>
                <td>
                    @if ($subCategory->is_active)
                        <span class="status-badge status-delivered">Yes</span>
                    @else
                        <span class="status-badge status-default">No</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        @admincan('sub_categories', 'edit')
                            <a href="{{ route('admin.sub-categories.edit', $subCategory) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        @endadmincan
                        @admincan('sub_categories', 'delete')
                            <form method="POST" action="{{ route('admin.sub-categories.destroy', $subCategory) }}" class="d-inline" onsubmit="return confirm('Delete this sub-category?');">
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
                <td colspan="6" class="text-center text-muted py-4">No sub-categories yet. Create a main category first.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
