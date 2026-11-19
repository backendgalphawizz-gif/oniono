@extends('layouts.restaurant')

@section('page-title', 'Dashboard')

@section('content')
@php
    $restaurantUser = auth('restaurant')->user();
@endphp

<div class="dashboard-intro">
    <h2>Welcome back, {{ $restaurantUser->name }}</h2>
    <p>Overview of your restaurant — orders, sales, and settlements (your data only).</p>
</div>

<div class="row g-3 g-xl-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('restaurant.orders.index') }}" class="dashboard-kpi">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-orange"><i class="bi bi-bag-check-fill"></i></span>
            </div>
            <div class="dashboard-kpi-label">Total orders</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="dashboard-kpi-meta">
                <span class="dashboard-kpi-action">View all orders <i class="bi bi-arrow-right-short"></i></span>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('restaurant.orders.index', ['bucket' => 'new']) }}" class="dashboard-kpi dashboard-kpi--pending">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-amber"><i class="bi bi-bell-fill"></i></span>
            </div>
            <div class="dashboard-kpi-label">New orders</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['new_orders']) }}</div>
            <div class="dashboard-kpi-meta">Awaiting accept / reject</div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('restaurant.orders.index', ['bucket' => 'ongoing']) }}" class="dashboard-kpi">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-teal"><i class="bi bi-arrow-repeat"></i></span>
            </div>
            <div class="dashboard-kpi-label">Ongoing orders</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['ongoing_orders']) }}</div>
            <div class="dashboard-kpi-meta">Accepted through out for delivery</div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('restaurant.orders.index', ['bucket' => 'completed']) }}" class="dashboard-kpi">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-purple"><i class="bi bi-check-circle-fill"></i></span>
            </div>
            <div class="dashboard-kpi-label">Completed orders</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['completed_orders']) }}</div>
            <div class="dashboard-kpi-meta">Delivered successfully</div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('restaurant.orders.index', ['bucket' => 'cancelled']) }}" class="dashboard-kpi">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-rose"><i class="bi bi-x-circle-fill"></i></span>
            </div>
            <div class="dashboard-kpi-label">Cancelled orders</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['cancelled_orders']) }}</div>
            <div class="dashboard-kpi-meta">Cancelled, rejected, or failed</div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="dashboard-kpi dashboard-kpi--revenue h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-orange"><i class="bi bi-graph-up-arrow"></i></span>
            </div>
            <div class="dashboard-kpi-label">Sales &amp; earnings</div>
            <div class="dashboard-kpi-value">{{ money_inr($stats['total_earnings']) }}</div>
            <div class="dashboard-kpi-meta">
                Today {{ money_inr($stats['daily_sales']) }} ·
                This month {{ money_inr($stats['monthly_sales']) }} sales (delivered)
            </div>
            <a href="{{ route('restaurant.earnings.index') }}" class="dashboard-kpi-action mt-2 d-inline-flex">
                Earnings ledger <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="{{ route('restaurant.orders.index', ['bucket' => 'new']) }}" class="dashboard-kpi dashboard-kpi--pending h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-amber"><i class="bi bi-hourglass-split"></i></span>
            </div>
            <div class="dashboard-kpi-label">Pending requests</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['pending_requests']) }}</div>
            <div class="dashboard-kpi-meta">
                {{ number_format($stats['new_orders']) }} new orders ·
                {{ number_format($stats['open_support_tickets']) }} open support tickets
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="{{ route('restaurant.settlements.index') }}" class="dashboard-kpi h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-teal"><i class="bi bi-wallet2"></i></span>
            </div>
            <div class="dashboard-kpi-label">Settlements</div>
            <div class="dashboard-kpi-value">{{ money_inr($stats['pending_earnings']) }}</div>
            <div class="dashboard-kpi-meta">
                Pending payout · {{ number_format($stats['active_settlements']) }} in progress ·
                {{ money_inr($stats['settled_earnings']) }} settled
            </div>
        </a>
    </div>
</div>

<x-panel-form-card title="Profile management" subtitle="Keep your store details, hours, documents, and availability up to date.">
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Availability</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge text-bg-{{ $profileSummary['is_open'] ? 'success' : 'secondary' }}">
                        {{ $profileSummary['is_open'] ? 'Accepting orders' : 'Closed' }}
                    </span>
                    @if ($profileSummary['is_blocked'])
                        <span class="badge text-bg-danger">Blocked</span>
                    @endif
                </div>
                <div class="small text-muted mt-2">Account: {{ $profileSummary['account_status'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Documents</div>
                <div class="fw-semibold">
                    @if ($profileSummary['missing_documents'] > 0)
                        {{ $profileSummary['missing_documents'] }} missing
                    @elseif ($profileSummary['pending_documents'] > 0)
                        {{ $profileSummary['pending_documents'] }} pending review
                    @else
                        All uploaded
                    @endif
                </div>
                <div class="small text-muted mt-2">PAN &amp; optional business files</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="small text-muted text-uppercase fw-semibold mb-2">Profile details</div>
            <ul class="list-unstyled mb-0 row g-2">
                @foreach ([
                    ['#profile-details', 'Details & photo', 'person-vcard'],
                    ['#contact-address', 'Contact & address', 'geo-alt'],
                    ['#opening-hours', 'Opening hours', 'clock'],
                    ['#documents', 'Documents', 'file-earmark-text'],
                    ['#availability', 'Availability', 'toggle-on'],
                ] as [$hash, $label, $icon])
                    <li class="col-sm-6">
                        <a href="{{ route('restaurant.profile.edit').$hash }}" class="d-flex align-items-center gap-2 small fw-semibold text-decoration-none">
                            <i class="bi bi-{{ $icon }} text-brand"></i>
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <a href="{{ route('restaurant.profile.edit') }}" class="btn btn-outline-brand btn-sm">
        <i class="bi bi-pencil-square me-1"></i> Manage full profile
    </a>
</x-panel-form-card>

<x-panel-table title="Recent orders">
    <thead>
        <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($recentOrders as $order)
            <tr>
                <td>#{{ $order->order_number }}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ money_inr($order->grand_total) }}</td>
                <td>{{ $order->status->label() }}</td>
                <td>
                    <a href="{{ route('restaurant.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-muted text-center py-4">No orders yet.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
