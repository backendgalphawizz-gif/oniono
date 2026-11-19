@extends('layouts.admin')
@section('page-title', 'Restaurant performance')
@section('content')
@include('admin.reports-analytics.partials.date-filter')

<x-panel-table title="Restaurant performance">
    <thead>
        <tr>
            <th>Restaurant</th>
            <th class="text-end">Orders</th>
            <th class="text-end">Delivered</th>
            <th class="text-end">Revenue</th>
            <th class="text-end">Avg. order</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($restaurants as $row)
            <tr>
                <td>
                    @if ($row->restaurant)
                        <a href="{{ route('admin.restaurants.show', $row->restaurant_id) }}" class="fw-semibold">{{ $row->restaurant->name }}</a>
                    @else
                        Restaurant #{{ $row->restaurant_id }}
                    @endif
                </td>
                <td class="text-end">{{ $row->orders_count }}</td>
                <td class="text-end">{{ $row->delivered_count }}</td>
                <td class="text-end fw-semibold">{{ money_inr($row->revenue) }}</td>
                <td class="text-end">{{ $row->avg_order_value ? money_inr($row->avg_order_value) : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No restaurant activity in range.</td></tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
