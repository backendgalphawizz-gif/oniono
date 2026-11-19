@extends('layouts.admin')
@section('page-title', 'Orders')
@section('content')
<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-order">Order number</label>
            <input name="search" id="filter-order" class="form-control" placeholder="Search order #" value="{{ request('search') }}">
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-status">Order status</label>
            <select name="status" id="filter-status" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\OrderStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-payment">Payment</label>
            <select name="payment_status" id="filter-payment" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\PaymentStatus::cases() as $ps)
                    <option value="{{ $ps->value }}" @selected(request('payment_status') === $ps->value)>{{ ucfirst(str_replace('_', ' ', $ps->value)) }}</option>
                @endforeach
            </select>
        </div>
    </x-slot:filter>
</x-panel-toolbar>

<x-panel-table title="All orders" :paginator="$orders">
    <thead>
        <tr>
            <th>Order</th>
            <th>Placed</th>
            <th>Restaurant</th>
            <th>Customer</th>
            <th>Rider</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orders as $order)
            @php
                $statusClass = match ($order->status) {
                    \App\Enums\OrderStatus::Delivered => 'status-delivered',
                    \App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Rejected, \App\Enums\OrderStatus::Failed => 'status-cancelled',
                    \App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Preparing, \App\Enums\OrderStatus::ReadyForPickup => 'status-pending',
                    default => 'status-default',
                };
            @endphp
            <tr>
                <td class="fw-semibold">#{{ $order->order_number }}</td>
                <td class="small text-muted">{{ $order->created_at->format('M j, Y g:i A') }}</td>
                <td>{{ $order->restaurant?->name ?? '—' }}</td>
                <td>{{ $order->customer_name ?? $order->user?->name ?? '—' }}</td>
                <td>{{ $order->deliveryAssignment?->deliveryPartner?->name ?? '—' }}</td>
                <td>{{ money_inr($order->grand_total) }}</td>
                <td class="small">{{ strtoupper($order->payment_method->value) }} · {{ ucfirst(str_replace('_', ' ', $order->payment_status->value)) }}</td>
                <td><span class="status-badge {{ $statusClass }}">{{ $order->status->label() }}</span></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.orders.show', $order) }}">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">No orders found.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
