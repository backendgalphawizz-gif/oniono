@extends('layouts.admin')
@section('page-title', 'Popular items & restaurants')
@section('content')
@include('admin.reports-analytics.partials.date-filter')

<div class="row g-3">
    <div class="col-lg-6">
        <x-panel-table title="Popular food items">
            <thead><tr><th>Item</th><th class="text-end">Units sold</th><th class="text-end">Revenue</th></tr></thead>
            <tbody>
                @forelse ($topItems as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row->item_name }}</td>
                        <td class="text-end">{{ $row->units_sold }}</td>
                        <td class="text-end">{{ money_inr($row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No item sales in range.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>
    </div>
    <div class="col-lg-6">
        <x-panel-table title="Popular restaurants">
            <thead><tr><th>Restaurant</th><th class="text-end">Orders</th><th class="text-end">Revenue</th></tr></thead>
            <tbody>
                @forelse ($topRestaurants as $row)
                    <tr>
                        <td>{{ $row->restaurant?->name ?? 'Restaurant #'.$row->restaurant_id }}</td>
                        <td class="text-end">{{ $row->orders_count }}</td>
                        <td class="text-end fw-semibold">{{ money_inr($row->revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No restaurant sales in range.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>
    </div>
</div>
@endsection
