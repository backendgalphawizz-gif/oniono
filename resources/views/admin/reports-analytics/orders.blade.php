@extends('layouts.admin')
@section('page-title', 'Order reports')
@section('content')
@include('admin.reports-analytics.partials.date-filter')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">Total orders</div>
            <div class="fw-bold fs-4">{{ number_format($total_orders) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">Delivered</div>
            <div class="fw-bold fs-4">{{ number_format($delivered) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">In pipeline</div>
            <div class="fw-bold fs-4">{{ number_format($pending_pipeline) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">Cancelled</div>
            <div class="fw-bold fs-4">{{ number_format($cancelled) }}</div>
        </div>
    </div>
</div>

<x-panel-table title="Orders in range" :paginator="$orders">
    <thead>
        <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Restaurant</th>
            <th>Status</th>
            <th class="text-end">Total</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orders as $order)
            <tr>
                <td>
                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_number }}</a>
                </td>
                <td>{{ $order->customer_name ?? $order->user?->name ?? '—' }}</td>
                <td>{{ $order->restaurant?->name ?? '—' }}</td>
                <td><span class="status-badge status-default">{{ $order->status->label() }}</span></td>
                <td class="text-end">{{ money_inr($order->grand_total) }}</td>
                <td class="text-muted small">{{ $order->created_at->format('M j, Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No orders in this date range.</td></tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
