@extends('layouts.restaurant')

@section('page-title', 'Order #'.$order->order_number)

@section('content')
@php
    $statusClass = match ($order->status) {
        \App\Enums\OrderStatus::Delivered => 'status-delivered',
        \App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Rejected, \App\Enums\OrderStatus::Failed => 'status-cancelled',
        \App\Enums\OrderStatus::Pending => 'status-pending',
        default => 'status-default',
    };
    $allowed = collect($allowedStatusUpdates);
    $canAccept = $allowed->contains(\App\Enums\OrderStatus::Accepted);
    $canReject = $allowed->contains(\App\Enums\OrderStatus::Rejected);
    $otherActions = $allowed->filter(fn ($s) => ! in_array($s, [\App\Enums\OrderStatus::Accepted, \App\Enums\OrderStatus::Rejected], true));
@endphp

@if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-4 order-lg-2">
        <div class="card card-soft mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <h2 class="h6 mb-0">Order #{{ $order->order_number }}</h2>
                    <span class="status-badge {{ $statusClass }}">{{ $order->status->label() }}</span>
                </div>
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Order ID</dt>
                    <dd class="col-7 mb-1">{{ $order->id }}</dd>
                    <dt class="col-5 text-muted">Placed</dt>
                    <dd class="col-7 mb-1">{{ $order->created_at->format('M j, Y g:i A') }}</dd>
                    @if ($order->accepted_at)
                        <dt class="col-5 text-muted">Accepted</dt>
                        <dd class="col-7 mb-1">{{ $order->accepted_at->format('M j, Y g:i A') }}</dd>
                    @endif
                    @if ($order->delivered_at)
                        <dt class="col-5 text-muted">Delivered</dt>
                        <dd class="col-7 mb-1">{{ $order->delivered_at->format('M j, Y g:i A') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @if ($canAccept || $canReject || $otherActions->isNotEmpty())
            <x-panel-form-card title="Order actions" subtitle="Accept, reject, or update kitchen status.">
                @if ($canAccept)
                    <form method="POST" action="{{ route('restaurant.orders.status', $order) }}" class="mb-3">
                        @csrf
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="btn btn-brand btn-sm w-100">Accept order</button>
                    </form>
                @endif

                @if ($canReject)
                    <form method="POST" action="{{ route('restaurant.orders.status', $order) }}">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <label class="form-label small" for="reject-note">Reject order</label>
                        <textarea name="note" id="reject-note" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for rejection" required maxlength="500"></textarea>
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Reject this order?');">Reject order</button>
                    </form>
                @endif

                @foreach ($otherActions as $next)
                    <form method="POST" action="{{ route('restaurant.orders.status', $order) }}" class="@if (! $loop->first || $canAccept || $canReject) mt-2 @endif">
                        @csrf
                        <input type="hidden" name="status" value="{{ $next->value }}">
                        <button type="submit" class="btn btn-outline-success btn-sm w-100">
                            @if ($next === \App\Enums\OrderStatus::Preparing)
                                Start preparing
                            @elseif ($next === \App\Enums\OrderStatus::ReadyForPickup)
                                Mark ready for pickup
                            @else
                                Mark as {{ $next->label() }}
                            @endif
                        </button>
                    </form>
                @endforeach
            </x-panel-form-card>
        @else
            <div class="card card-soft mb-3">
                <div class="card-body small text-muted">
                    No actions available at this stage. Delivery progress is handled by the rider and platform.
                </div>
            </div>
        @endif

        <a href="{{ route('restaurant.orders.index') }}" class="btn btn-outline-secondary btn-sm w-100">Back to orders</a>
    </div>

    <div class="col-lg-8 order-lg-1">
        <div class="card card-soft mb-3">
            <div class="card-header-brand"><h3 class="h6 mb-0">Customer &amp; delivery</h3></div>
            <div class="card-body small">
                <p class="mb-1 fw-semibold">{{ $order->customer_name ?? $order->user?->name ?? '—' }}</p>
                <p class="mb-1">{{ $order->customer_phone ?? $order->user?->phone ?? '—' }}</p>
                @if ($order->address)
                    <p class="text-muted mb-0 mt-2">
                        {{ $order->address->address_line }}@if($order->address->landmark), {{ $order->address->landmark }}@endif<br>
                        {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->pincode }}
                    </p>
                @endif
                @if ($order->delivery_instructions)
                    <div class="alert alert-light border small mt-3 mb-0">
                        <span class="fw-semibold">Delivery instructions:</span> {{ $order->delivery_instructions }}
                    </div>
                @else
                    <p class="text-muted mb-0 mt-2">No special delivery instructions.</p>
                @endif
            </div>
        </div>

        <div class="card card-soft mb-3">
            <div class="card-header-brand"><h3 class="h6 mb-0">Delivery partner</h3></div>
            <div class="card-body small">
                @php($partner = $order->deliveryAssignment?->deliveryPartner)
                @if ($partner)
                    <p class="mb-1 fw-semibold">{{ $partner->name }}</p>
                    <p class="mb-1">{{ $partner->phone }}</p>
                    <p class="mb-0 text-muted">Assignment status: {{ ucfirst($order->deliveryAssignment->status ?? 'assigned') }}</p>
                @else
                    <p class="text-muted mb-0">No rider assigned yet. A partner will be assigned when the order is ready for pickup.</p>
                @endif
            </div>
        </div>

        <div class="card card-soft mb-3">
            <div class="card-header-brand"><h3 class="h6 mb-0">Order items</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Details</th>
                                <th>Qty</th>
                                <th class="text-end">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="fw-medium">{{ $item->item_name }}</td>
                                    <td class="small text-muted">
                                        @if ($item->variant_snapshot)
                                            <div>Variant: {{ $item->variant_snapshot['name'] ?? json_encode($item->variant_snapshot) }}</div>
                                        @endif
                                        @if (! empty($item->addons_snapshot))
                                            <div>Add-ons:
                                                @foreach ($item->addons_snapshot as $addon)
                                                    {{ $addon['name'] ?? 'Add-on' }}@if(!$loop->last), @endif
                                                @endforeach
                                            </div>
                                        @endif
                                        @if (! $item->variant_snapshot && empty($item->addons_snapshot))
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }} × {{ money_inr($item->unit_price) }}</td>
                                    <td class="text-end">{{ money_inr($item->total_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card card-soft h-100">
                    <div class="card-header-brand"><h3 class="h6 mb-0">Amount summary</h3></div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-7 text-muted">Food subtotal</dt>
                            <dd class="col-5 text-end">{{ money_inr($order->food_subtotal) }}</dd>
                            @if ((float) $order->discount_amount > 0)
                                <dt class="col-7 text-muted">Discount</dt>
                                <dd class="col-5 text-end">−{{ money_inr($order->discount_amount) }}</dd>
                            @endif
                            <dt class="col-7 text-muted">Delivery charge</dt>
                            <dd class="col-5 text-end">{{ money_inr($order->delivery_charge) }}</dd>
                            <dt class="col-7 fw-semibold">Grand total</dt>
                            <dd class="col-5 text-end fw-semibold">{{ money_inr($order->grand_total) }}</dd>
                        </dl>
                        @if ($order->cancellation_reason)
                            <div class="alert alert-warning small mt-3 mb-0">{{ $order->cancellation_reason }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-soft h-100">
                    <div class="card-header-brand"><h3 class="h6 mb-0">Payment status</h3></div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted">Method</dt>
                            <dd class="col-7">{{ strtoupper($order->payment_method->value) }}</dd>
                            <dt class="col-5 text-muted">Status</dt>
                            <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $order->payment_status->value)) }}</dd>
                            <dt class="col-5 text-muted">Amount</dt>
                            <dd class="col-7">{{ money_inr($order->grand_total) }}</dd>
                            @if ($order->payment?->gateway_payment_id)
                                <dt class="col-5 text-muted">Reference</dt>
                                <dd class="col-7 text-break">{{ $order->payment->gateway_payment_id }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        @if ($order->statusHistories->isNotEmpty())
            <div class="card card-soft">
                <div class="card-header-brand"><h3 class="h6 mb-0">Status timeline</h3></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        @foreach ($order->statusHistories as $history)
                            <li class="border-start border-2 ps-3 pb-3 ms-1 @if($loop->last) border-0 pb-0 @endif">
                                <div class="fw-semibold">
                                    {{ ucfirst(str_replace('_', ' ', $history->from_status)) }}
                                    → {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                                </div>
                                <div class="text-muted">{{ $history->created_at->format('M j, Y g:i A') }}</div>
                                @if ($history->note)
                                    <div class="mt-1">{{ $history->note }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
