@extends('layouts.admin')
@section('page-title', 'Dashboard')
@section('content')

<div class="dashboard-intro">
    <h2>Welcome back, {{ auth('admin')->user()->name }}</h2>
    <p>Platform snapshot — users, partners, restaurants, orders, and revenue at a glance.</p>
</div>

<div class="row g-3 g-xl-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index') }}" class="dashboard-kpi">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-orange"><i class="bi bi-people-fill"></i></span>
            </div>
            <div class="dashboard-kpi-label">Total users</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['total_users']) }}</div>
            <div class="dashboard-kpi-meta">
                <span class="dashboard-kpi-action">Manage users <i class="bi bi-arrow-right-short"></i></span>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.restaurants.index') }}" class="dashboard-kpi">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-purple"><i class="bi bi-shop"></i></span>
            </div>
            <div class="dashboard-kpi-label">Total restaurants</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['total_restaurants']) }}</div>
            <div class="dashboard-kpi-meta">
                <span class="dashboard-kpi-action">Restaurant management <i class="bi bi-arrow-right-short"></i></span>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.delivery-partners.index') }}" class="dashboard-kpi">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-teal"><i class="bi bi-bicycle"></i></span>
            </div>
            <div class="dashboard-kpi-label">Total delivery partners</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['total_delivery_partners']) }}</div>
            <div class="dashboard-kpi-meta">
                <span class="dashboard-kpi-action">Partner management <i class="bi bi-arrow-right-short"></i></span>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.orders.index') }}" class="dashboard-kpi">
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

    <div class="col-md-6 col-xl-4">
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="dashboard-kpi dashboard-kpi--pending h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-amber"><i class="bi bi-hourglass-split"></i></span>
            </div>
            <div class="dashboard-kpi-label">Pending orders / requests</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['pending_requests']) }}</div>
            <div class="dashboard-kpi-meta">
                {{ number_format($stats['pending_orders']) }} orders ·
                {{ number_format($stats['pending_restaurants']) }} restaurants ·
                {{ number_format($stats['pending_partners']) }} partners
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="{{ route('admin.restaurants.index', ['status' => 'approved', 'account' => 'active']) }}" class="dashboard-kpi h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-purple"><i class="bi bi-shop-window"></i></span>
            </div>
            <div class="dashboard-kpi-label">Active restaurants</div>
            <div class="dashboard-kpi-value">{{ number_format($stats['active_restaurants']) }}</div>
            <div class="dashboard-kpi-meta">Approved and not blocked</div>
        </a>
    </div>

    <div class="col-xl-4">
        <div class="dashboard-kpi dashboard-kpi--revenue h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-orange"><i class="bi bi-graph-up-arrow"></i></span>
            </div>
            <div class="dashboard-kpi-label">Total earnings / revenue</div>
            <div class="dashboard-kpi-value">{{ money_inr($stats['total_revenue']) }}</div>
            <div class="dashboard-kpi-meta">From delivered orders</div>
            <div class="dashboard-revenue-pills">
                <span class="dashboard-revenue-pill">Commission {{ money_inr($stats['platform_commission']) }}</span>
                <span class="dashboard-revenue-pill">Pending settlements {{ money_inr($stats['pending_settlements']) }}</span>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="dashboard-kpi-action mt-2 d-inline-flex">
                Payment details <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>
</div>
@endsection
