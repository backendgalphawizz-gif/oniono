@extends('layouts.admin')
@section('page-title', 'Banners')
@section('content')
<x-panel-toolbar>
    <x-slot:actions>
        @admincan('banners', 'edit')
            <a href="{{ route('admin.banners.create') }}" class="btn btn-brand">
                <i class="bi bi-plus-lg me-1"></i>Add banner
            </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Banners" :paginator="$banners">
    <thead>
        <tr>
            <th>Title</th>
            <th>Order</th>
            <th>Active</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($banners as $banner)
            <tr>
                <td class="fw-semibold">{{ $banner->title }}</td>
                <td>{{ $banner->sort_order }}</td>
                <td>
                    @if ($banner->is_active)
                        <span class="status-badge status-delivered">Yes</span>
                    @else
                        <span class="status-badge status-default">No</span>
                    @endif
                </td>
                <td class="text-end">
                    @admincan('banners', 'edit')
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    @endadmincan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-4">No banners yet.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
