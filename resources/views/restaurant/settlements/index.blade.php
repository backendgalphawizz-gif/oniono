@extends('layouts.restaurant')

@section('page-title', 'Settlements')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="h5 mb-1">Settlement history</h2>
        <p class="text-muted small mb-0">Payout batches processed for your restaurant.</p>
    </div>
    <a href="{{ route('restaurant.earnings.index') }}" class="btn btn-sm btn-outline-secondary">Earnings overview</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-soft dashboard-kpi--pending">
            <div class="card-body">
                <div class="dashboard-kpi-label">Pending payments</div>
                <div class="dashboard-kpi-value">{{ money_inr($summary['pending_amount']) }}</div>
                <div class="small text-muted">Settlements pending, processing, or on hold</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-soft">
            <div class="card-body">
                <div class="dashboard-kpi-label">Paid out (all time)</div>
                <div class="dashboard-kpi-value">{{ money_inr($summary['paid_amount']) }}</div>
            </div>
        </div>
    </div>
</div>

<form method="GET" class="mb-3">
    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach (\App\Enums\SettlementStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
        @endforeach
    </select>
</form>

<x-panel-table title="Settlements" :paginator="$settlements">
    <thead>
        <tr>
            <th>Settlement #</th>
            <th>Created</th>
            <th>Payment date</th>
            <th class="text-end">Amount</th>
            <th>Mode</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($settlements as $settlement)
            <tr>
                <td class="fw-medium">{{ $settlement->settlement_number }}</td>
                <td class="small text-muted">{{ $settlement->created_at->format('M j, Y') }}</td>
                <td class="small">{{ $settlement->payment_date?->format('M j, Y') ?? '—' }}</td>
                <td class="text-end">{{ money_inr($settlement->amount) }}</td>
                <td class="text-uppercase small">{{ $settlement->payment_mode ?? '—' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $settlement->status->value ?? $settlement->status)) }}</td>
                <td class="text-end">
                    <a href="{{ route('restaurant.settlements.show', $settlement) }}" class="btn btn-sm btn-outline-primary">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">No settlements found.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
