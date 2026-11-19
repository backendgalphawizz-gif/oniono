@extends('layouts.admin')
@section('page-title', 'Revenue reports')
@section('content')
@include('admin.reports-analytics.partials.date-filter')

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">Gross revenue (delivered)</div>
            <div class="fw-bold fs-5">{{ money_inr($totals->gross_revenue ?? 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">Platform commission</div>
            <div class="fw-bold fs-5">{{ money_inr($totals->commission_total ?? 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">Restaurant earnings</div>
            <div class="fw-bold fs-5">{{ money_inr($totals->restaurant_earnings ?? 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-mini h-100">
            <div class="text-muted small">Delivered orders</div>
            <div class="fw-bold fs-5">{{ number_format($totals->order_count ?? 0) }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <x-panel-table title="Revenue by day">
            <thead><tr><th>Date</th><th class="text-end">Revenue</th><th class="text-end">Orders</th></tr></thead>
            <tbody>
                @forelse ($salesByDay as $row)
                    <tr>
                        <td>{{ $row->day }}</td>
                        <td class="text-end fw-semibold">{{ money_inr($row->total) }}</td>
                        <td class="text-end">{{ $row->orders_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No delivered orders in range.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>
    </div>
    <div class="col-lg-5">
        <x-panel-table title="By payment method">
            <thead><tr><th>Method</th><th class="text-end">Orders</th><th class="text-end">Revenue</th></tr></thead>
            <tbody>
                @forelse ($byPayment as $row)
                    <tr>
                        <td class="text-capitalize">{{ $row->payment_method?->value ?? $row->payment_method ?? '—' }}</td>
                        <td class="text-end">{{ $row->orders_count }}</td>
                        <td class="text-end">{{ money_inr($row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No data.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>
    </div>
</div>
@endsection
