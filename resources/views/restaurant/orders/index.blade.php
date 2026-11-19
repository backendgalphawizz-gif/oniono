@extends('layouts.restaurant')

@section('page-title', 'Orders')

@section('content')
<div class="mb-4">
    <h2 class="h5 mb-1">Order requests</h2>
    <p class="text-muted small mb-0">Manage incoming and active orders for your restaurant only.</p>
</div>

@if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif

<div class="d-flex flex-wrap gap-2 mb-4">
    @foreach ($filters as $filter)
        @php
            $active = request('status') === $filter['param']
                || ($filter['param'] === null && ! request()->filled('status') && ! request()->filled('bucket'));
            $url = $filter['param']
                ? route('restaurant.orders.index', ['status' => $filter['param']])
                : route('restaurant.orders.index');
        @endphp
        <a
            href="{{ $url }}"
            class="btn btn-sm {{ $active ? 'btn-brand' : 'btn-outline-secondary' }}"
        >
            {{ $filter['label'] }}
            <span class="badge {{ $active ? 'text-bg-light' : 'text-bg-secondary' }} ms-1">{{ $filter['count'] }}</span>
        </a>
    @endforeach
</div>

@if (request()->filled('bucket') && ! request()->filled('status'))
    @php
        $bucketLabel = match (request('bucket')) {
            'new' => 'New orders',
            'ongoing' => 'Ongoing orders',
            'completed' => 'Completed orders',
            'cancelled' => 'Cancelled orders',
            default => null,
        };
    @endphp
    @if ($bucketLabel)
        <p class="text-muted small mb-3">
            Dashboard filter: <strong>{{ $bucketLabel }}</strong>
            · <a href="{{ route('restaurant.orders.index') }}">Clear</a>
        </p>
    @endif
@endif

<x-panel-table title="Orders" :paginator="$orders">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Placed</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Rider</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orders as $order)
            @php
                $statusClass = match ($order->status) {
                    \App\Enums\OrderStatus::Delivered => 'status-delivered',
                    \App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Rejected, \App\Enums\OrderStatus::Failed => 'status-cancelled',
                    \App\Enums\OrderStatus::Pending => 'status-pending',
                    default => 'status-default',
                };
            @endphp
            <tr>
                <td class="fw-semibold">#{{ $order->order_number }}</td>
                <td class="small text-muted">{{ $order->created_at->format('M j, g:i A') }}</td>
                <td>{{ $order->customer_name ?? '—' }}</td>
                <td>{{ money_inr($order->grand_total) }}</td>
                <td class="small text-capitalize">{{ str_replace('_', ' ', $order->payment_status->value) }}</td>
                <td><span class="status-badge {{ $statusClass }}">{{ $order->status->label() }}</span></td>
                <td class="small">{{ $order->deliveryAssignment?->deliveryPartner?->name ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('restaurant.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No orders in this view.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
