@extends('layouts.restaurant')

@section('page-title', $settlement->settlement_number)

@section('content')
<div class="mb-4">
    <a href="{{ route('restaurant.settlements.index') }}" class="small">&larr; Back to settlements</a>
    <h2 class="h5 mt-2 mb-0">{{ $settlement->settlement_number }}</h2>
    <p class="text-muted small mb-0">Payout details for your restaurant</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="text-muted small">Amount</div>
                <div class="h4 mb-0">{{ money_inr($settlement->amount) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body small">
                <div class="text-muted">Status</div>
                <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $settlement->status->value ?? $settlement->status)) }}</div>
                <div class="text-muted mt-2">Payment mode</div>
                <div>{{ strtoupper($settlement->payment_mode ?? '—') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body small">
                <div class="text-muted">Payment date</div>
                <div>{{ $settlement->payment_date?->format('M j, Y') ?? 'Not paid yet' }}</div>
                @if ($settlement->transaction_reference)
                    <div class="text-muted mt-2">Reference</div>
                    <div class="text-break">{{ $settlement->transaction_reference }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($settlement->admin_remarks)
    <div class="alert alert-light border small mb-4">
        <span class="fw-semibold">Note from platform:</span> {{ $settlement->admin_remarks }}
    </div>
@endif

<x-panel-table title="Orders included in this settlement">
    <thead>
        <tr>
            <th>Order</th>
            <th class="text-end">Payout amount</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($settlement->items as $item)
            <tr>
                <td class="fw-medium">
                    @if ($item->earning?->order)
                        #{{ $item->earning->order->order_number }}
                    @else
                        Order #{{ $item->earning?->order_id ?? '—' }}
                    @endif
                </td>
                <td class="text-end">{{ money_inr($item->amount) }}</td>
                <td class="text-end">
                    @if ($item->earning?->order)
                        <a href="{{ route('restaurant.orders.show', $item->earning->order) }}" class="btn btn-sm btn-outline-primary">Order</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-muted text-center py-3">No line items.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
