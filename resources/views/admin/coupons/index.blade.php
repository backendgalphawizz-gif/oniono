@extends('layouts.admin')
@section('page-title', 'Coupons')
@section('content')
<x-panel-toolbar>
    <x-slot:actions>
        @admincan('coupons', 'edit')
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-brand">
                <i class="bi bi-plus-lg me-1"></i>Add coupon
            </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Coupons" :paginator="$coupons">
    <thead>
        <tr>
            <th>Code</th>
            <th>Title</th>
            <th>Value</th>
            <th>Active</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($coupons as $coupon)
            <tr>
                <td class="fw-semibold">{{ $coupon->code }}</td>
                <td>{{ $coupon->title }}</td>
                <td>{{ $coupon->discount_value }}</td>
                <td>
                    @if ($coupon->is_active)
                        <span class="status-badge status-delivered">Yes</span>
                    @else
                        <span class="status-badge status-default">No</span>
                    @endif
                </td>
                <td class="text-end">
                    @admincan('coupons', 'edit')
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    @endadmincan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">No coupons yet.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
