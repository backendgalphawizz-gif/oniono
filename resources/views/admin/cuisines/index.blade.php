@extends('layouts.admin')
@section('page-title', 'Cuisines')
@section('content')
<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-5 col-lg-4 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input type="search" name="search" id="filter-search" class="form-control" placeholder="Cuisine name…" value="{{ request('search') }}">
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
        @admincan('cuisines', 'edit')
            <a href="{{ route('admin.cuisines.create') }}" class="btn btn-brand">
                <i class="bi bi-plus-lg me-1"></i>Add cuisine
            </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Cuisines" :paginator="$cuisines">
    <thead>
        <tr>
            <th>Name</th>
            <th>Active</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($cuisines as $cuisine)
            <tr>
                <td class="fw-semibold">{{ $cuisine->name }}</td>
                <td>
                    @if ($cuisine->is_active)
                        <span class="status-badge status-delivered">Yes</span>
                    @else
                        <span class="status-badge status-default">No</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        @admincan('cuisines', 'edit')
                            <a href="{{ route('admin.cuisines.edit', $cuisine) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        @endadmincan
                        @admincan('cuisines', 'delete')
                            <form method="POST" action="{{ route('admin.cuisines.destroy', $cuisine) }}" class="d-inline" onsubmit="return confirm('Delete this cuisine?');">
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
                <td colspan="3" class="text-center text-muted py-4">No cuisines match your filters.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
