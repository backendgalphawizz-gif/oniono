@extends('layouts.restaurant')

@section('page-title', 'Earnings & payments')

@section('content')
<div class="mb-4">
    <h2 class="h5 mb-1">Earnings &amp; payments</h2>
    <p class="text-muted small mb-0">Sales, commissions, and payouts for your restaurant only.</p>
</div>

<div class="row g-3 g-xl-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-kpi h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-orange"><i class="bi bi-calendar-day"></i></span>
            </div>
            <div class="dashboard-kpi-label">Today&apos;s sales</div>
            <div class="dashboard-kpi-value">{{ money_inr($overview['today_sales']) }}</div>
            <div class="dashboard-kpi-meta">Delivered orders · today</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-kpi dashboard-kpi--revenue h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-teal"><i class="bi bi-cash-coin"></i></span>
            </div>
            <div class="dashboard-kpi-label">Today&apos;s net earnings</div>
            <div class="dashboard-kpi-value">{{ money_inr($overview['today_earnings']) }}</div>
            <div class="dashboard-kpi-meta">Commission today: {{ money_inr($overview['today_commission']) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-kpi dashboard-kpi--pending h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-amber"><i class="bi bi-hourglass-split"></i></span>
            </div>
            <div class="dashboard-kpi-label">Pending payout</div>
            <div class="dashboard-kpi-value">{{ money_inr($overview['pending_earnings']) }}</div>
            <div class="dashboard-kpi-meta">Awaiting settlement</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dashboard-kpi h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-purple"><i class="bi bi-percent"></i></span>
            </div>
            <div class="dashboard-kpi-label">Platform commission</div>
            <div class="dashboard-kpi-value">{{ money_inr($overview['total_commission']) }}</div>
            <div class="dashboard-kpi-meta">Lifetime deductions</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body small">
                <div class="text-muted">Lifetime delivered sales</div>
                <div class="h5 mb-0">{{ money_inr($overview['lifetime_sales']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body small">
                <div class="text-muted">Settled to you</div>
                <div class="h5 mb-0">{{ money_inr($overview['settled_earnings']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body small">
                <div class="text-muted">Settlements in progress</div>
                <div class="h5 mb-0">{{ money_inr($overview['pending_settlement_amount']) }}</div>
                <div class="text-muted">{{ $overview['pending_settlement_count'] }} batch(es)</div>
            </div>
        </div>
    </div>
</div>

<x-panel-form-card title="Daily sales &amp; earnings" subtitle="Last 14 days (delivered sales vs recorded earnings).">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-end">Orders</th>
                    <th class="text-end">Sales</th>
                    <th class="text-end">Commission</th>
                    <th class="text-end">Net earnings</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dailyBreakdown as $row)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($row->day)->format('D, M j, Y') }}</td>
                        <td class="text-end">{{ $row->orders }}</td>
                        <td class="text-end">{{ money_inr($row->sales) }}</td>
                        <td class="text-end text-muted">−{{ money_inr($row->commission) }}</td>
                        <td class="text-end fw-medium">{{ money_inr($row->earnings) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-panel-form-card>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 mb-2">
    <h3 class="h6 mb-0">Order-wise earnings</h3>
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending payout</option>
            <option value="settled" @selected(request('status') === 'settled')>Settled</option>
        </select>
    </form>
</div>

<x-panel-table title="Per-order ledger" :paginator="$earnings">
    <thead>
        <tr>
            <th>Order</th>
            <th>Date</th>
            <th class="text-end">Order value</th>
            <th class="text-end">Commission</th>
            <th class="text-end">Net earning</th>
            <th>Payout status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($earnings as $earning)
            <tr>
                <td class="fw-medium">
                    @if ($earning->order)
                        <a href="{{ route('restaurant.orders.show', $earning->order) }}">#{{ $earning->order->order_number }}</a>
                    @else
                        —
                    @endif
                </td>
                <td class="small text-muted">{{ $earning->created_at->format('M j, Y') }}</td>
                <td class="text-end">{{ money_inr($earning->commissionable_amount) }}</td>
                <td class="text-end text-muted">
                    −{{ money_inr($earning->commission_amount) }}
                    <span class="d-block small">({{ number_format($earning->commission_percentage, 1) }}%)</span>
                </td>
                <td class="text-end fw-medium">{{ money_inr($earning->net_earning) }}</td>
                <td>
                    <span class="badge text-bg-{{ $earning->status === 'settled' ? 'success' : 'warning' }}">
                        {{ ucfirst($earning->status) }}
                    </span>
                    @if ($earning->settled_at)
                        <span class="d-block small text-muted">{{ $earning->settled_at->format('M j, Y') }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No earnings recorded yet. Earnings are created when you accept orders.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>

<x-panel-form-card title="Settlement history" subtitle="Recent payout batches from the platform." class="mt-4">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('restaurant.settlements.index') }}" class="btn btn-sm btn-outline-secondary">View all settlements</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Settlement #</th>
                    <th>Date</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentSettlements as $settlement)
                    <tr>
                        <td class="fw-medium">{{ $settlement->settlement_number }}</td>
                        <td class="small text-muted">{{ $settlement->created_at->format('M j, Y') }}</td>
                        <td class="text-end">{{ money_inr($settlement->amount) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $settlement->status->value ?? $settlement->status)) }}</td>
                        <td class="text-end">
                            <a href="{{ route('restaurant.settlements.show', $settlement) }}" class="btn btn-sm btn-outline-primary">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No settlements yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-panel-form-card>
@endsection
