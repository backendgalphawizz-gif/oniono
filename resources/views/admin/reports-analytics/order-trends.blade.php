@extends('layouts.admin')
@section('page-title', 'Order trends')
@section('content')
@include('admin.reports-analytics.partials.date-filter')

<div class="card card-soft mb-4">
    <div class="card-header-brand"><h2 class="h6 mb-0">Order volume & revenue trend</h2></div>
    <div class="card-body">
        <canvas id="orderTrendsChart" height="100" aria-label="Order trends chart"></canvas>
    </div>
</div>

<x-panel-table title="Daily breakdown">
    <thead><tr><th>Date</th><th class="text-end">Orders</th><th class="text-end">Revenue (all statuses)</th></tr></thead>
    <tbody>
        @forelse ($trends as $row)
            <tr>
                <td>{{ $row->day }}</td>
                <td class="text-end">{{ $row->orders_count }}</td>
                <td class="text-end">{{ money_inr($row->revenue) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-4">No orders in range.</td></tr>
        @endforelse
    </tbody>
</x-panel-table>

@push('scripts')
<script>
(function () {
    var labels = @json($trends->pluck('day'));
    var orders = @json($trends->pluck('orders_count'));
    var revenue = @json($trends->pluck('revenue'));
    var el = document.getElementById('orderTrendsChart');
    if (!el || typeof Chart === 'undefined') return;
    new Chart(el, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Orders',
                    data: orders,
                    borderColor: '#ff8c00',
                    backgroundColor: 'rgba(255, 140, 0, 0.12)',
                    tension: 0.25,
                    yAxisID: 'y',
                },
                {
                    label: 'Revenue',
                    data: revenue,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.08)',
                    tension: 0.25,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'Orders' } },
                y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue' } },
            },
        },
    });
})();
</script>
@endpush
@endsection
