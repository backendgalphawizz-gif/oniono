@extends('layouts.admin')
@section('page-title', 'User activity reports')
@section('content')
@include('admin.reports-analytics.partials.date-filter')

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-mini h-100">
            <div class="text-muted small">New registrations</div>
            <div class="fw-bold fs-4">{{ number_format($newUsers) }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-mini h-100">
            <div class="text-muted small">Customers who placed orders</div>
            <div class="fw-bold fs-4">{{ number_format($activeCustomers) }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <x-panel-table title="Registrations by day">
            <thead><tr><th>Date</th><th class="text-end">Users</th></tr></thead>
            <tbody>
                @forelse ($registrationsByDay as $row)
                    <tr><td>{{ $row->day }}</td><td class="text-end">{{ $row->total }}</td></tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-4">No new users in range.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>
    </div>
    <div class="col-lg-7">
        <x-panel-table title="Top customers by spend">
            <thead><tr><th>Customer</th><th class="text-end">Orders</th><th class="text-end">Spend</th></tr></thead>
            <tbody>
                @forelse ($topCustomers as $row)
                    <tr>
                        <td>
                            @if ($row->user)
                                <a href="{{ route('admin.users.show', $row->user_id) }}" class="fw-semibold">{{ $row->user->name }}</a>
                                <div class="small text-muted">{{ $row->user->email ?? $row->user->phone }}</div>
                            @else
                                User #{{ $row->user_id }}
                            @endif
                        </td>
                        <td class="text-end">{{ $row->orders_count }}</td>
                        <td class="text-end fw-semibold">{{ money_inr($row->spend) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No customer orders in range.</td></tr>
                @endforelse
            </tbody>
        </x-panel-table>
    </div>
</div>
@endsection
