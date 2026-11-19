@extends('layouts.admin')
@section('page-title', 'Order #'.$order->order_number)
@section('content')
@php
    $statusClass = match ($order->status) {
        \App\Enums\OrderStatus::Delivered => 'status-delivered',
        \App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Rejected, \App\Enums\OrderStatus::Failed => 'status-cancelled',
        \App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Preparing, \App\Enums\OrderStatus::ReadyForPickup => 'status-pending',
        default => 'status-default',
    };
    $statusActions = collect($allowedStatusUpdates)->filter(fn ($s) => $s !== \App\Enums\OrderStatus::Cancelled);
@endphp

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row g-4">
    <div class="col-lg-4 order-lg-2">
        <div class="card card-soft mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <h2 class="h6 mb-0">#{{ $order->order_number }}</h2>
                    <span class="status-badge {{ $statusClass }}">{{ $order->status->label() }}</span>
                </div>
                <dl class="row small mb-0">
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
                    <dt class="col-5 text-muted">Payment</dt>
                    <dd class="col-7 mb-0">
                        {{ strtoupper($order->payment_method->value) }}
                        · {{ ucfirst(str_replace('_', ' ', $order->payment_status->value)) }}
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card card-soft mb-3">
            <div class="card-header-brand"><h3 class="h6 mb-0">Manage order</h3></div>
            <div class="card-body d-grid gap-3">
                @if ($canAdminCancel)
                    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">
                        @csrf
                        <label class="form-label small mb-1">Cancel order</label>
                        <input class="form-control form-control-sm mb-2" name="cancellation_reason" placeholder="Cancellation reason" required maxlength="500">
                        <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Cancel this order?');">Cancel order</button>
                    </form>
                @elseif (! in_array($order->status, [\App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Delivered, \App\Enums\OrderStatus::Rejected], true))
                    <p class="small text-muted mb-0">Cancellation is only allowed before the order is ready for pickup.</p>
                @endif

                @if ($statusActions->isNotEmpty())
                    <div>
                        <p class="small fw-semibold mb-2">Update delivery status</p>
                        @foreach ($statusActions as $next)
                            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mb-2">
                                @csrf
                                <input type="hidden" name="status" value="{{ $next->value }}">
                                <button type="submit" class="btn btn-outline-success btn-sm w-100">Mark as {{ $next->label() }}</button>
                            </form>
                        @endforeach
                    </div>
                @endif

                @if ($canReassignDelivery && $deliveryPartners->isNotEmpty())
                    <form method="POST" action="{{ route('admin.delivery-assignment.assign', $order) }}">
                        @csrf
                        <label class="form-label small mb-1">
                            {{ $order->deliveryAssignment?->deliveryPartner ? 'Reassign delivery partner' : 'Assign delivery partner' }}
                        </label>
                        <select name="delivery_partner_id" class="form-select form-select-sm mb-2" required>
                            @foreach ($deliveryPartners as $partner)
                                <option value="{{ $partner->id }}" @selected($order->deliveryAssignment?->delivery_partner_id === $partner->id)>
                                    {{ $partner->name }} ({{ $partner->phone }})
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="note" class="form-control form-control-sm mb-2" placeholder="Note (optional)" maxlength="500">
                        <button type="submit" class="btn btn-brand btn-sm w-100">
                            {{ $order->deliveryAssignment?->deliveryPartner ? 'Reassign rider' : 'Assign rider' }}
                        </button>
                    </form>
                @elseif ($canReassignDelivery)
                    <p class="small text-warning mb-0">No eligible delivery partners available.</p>
                @endif

                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Back to orders</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8 order-lg-1">
        <div class="card card-soft mb-3">
            <div class="card-header-brand"><h3 class="h6 mb-0">Status timeline</h3></div>
            <div class="card-body">
                @if ($order->statusHistories->isEmpty())
                    <p class="small text-muted mb-0">No status changes recorded yet. Current status: {{ $order->status->label() }}.</p>
                @else
                    <ul class="list-unstyled mb-0 small">
                        @foreach ($order->statusHistories as $history)
                            <li class="border-start border-2 ps-3 pb-3 ms-1 @if($loop->last) border-0 pb-0 @endif">
                                <div class="fw-semibold">
                                    {{ ucfirst(str_replace('_', ' ', $history->from_status)) }}
                                    → {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                                </div>
                                <div class="text-muted">{{ $history->created_at->format('M j, Y g:i A') }}</div>
                                @if ($history->actor_type)
                                    <div class="text-muted">By {{ $history->actor_type }}@if($history->actor_id) #{{ $history->actor_id }}@endif</div>
                                @endif
                                @if ($history->note)
                                    <div class="mt-1">{{ $history->note }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card card-soft h-100">
                    <div class="card-header-brand"><h3 class="h6 mb-0">Customer</h3></div>
                    <div class="card-body small">
                        <p class="mb-1 fw-semibold">{{ $order->customer_name ?? $order->user?->name ?? '—' }}</p>
                        <p class="mb-1">{{ $order->customer_phone ?? $order->user?->phone ?? '—' }}</p>
                        @if ($order->user?->email)
                            <p class="mb-1"><a href="{{ route('admin.users.show', $order->user) }}">{{ $order->user->email }}</a></p>
                        @endif
                        @if ($order->address)
                            <p class="text-muted mb-0 mt-2">
                                {{ $order->address->address_line }}@if($order->address->landmark), {{ $order->address->landmark }}@endif<br>
                                {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->pincode }}
                            </p>
                        @endif
                        @if ($order->delivery_instructions)
                            <p class="mb-0 mt-2"><span class="text-muted">Instructions:</span> {{ $order->delivery_instructions }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100">
                    <div class="card-header-brand"><h3 class="h6 mb-0">Restaurant</h3></div>
                    <div class="card-body small">
                        @if ($order->restaurant)
                            <p class="mb-1 fw-semibold"><a href="{{ route('admin.restaurants.show', $order->restaurant) }}">{{ $order->restaurant->name }}</a></p>
                            <p class="mb-1">{{ $order->restaurant->phone }}</p>
                            <p class="mb-0 text-muted">{{ $order->restaurant->address }}, {{ $order->restaurant->city }}</p>
                        @else
                            <p class="text-muted mb-0">—</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100">
                    <div class="card-header-brand"><h3 class="h6 mb-0">Delivery partner</h3></div>
                    <div class="card-body small">
                        @php($partner = $order->deliveryAssignment?->deliveryPartner)
                        @if ($partner)
                            <p class="mb-1 fw-semibold"><a href="{{ route('admin.delivery-partners.show', $partner) }}">{{ $partner->name }}</a></p>
                            <p class="mb-1">{{ $partner->phone }}</p>
                            <p class="mb-0 text-muted">Assignment: {{ ucfirst($order->deliveryAssignment->status) }}</p>
                        @else
                            <p class="text-muted mb-0">Not assigned yet</p>
                        @endif
                    </div>
                </div>
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
                                <th>Unit price</th>
                                <th>Qty</th>
                                <th class="text-end">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ money_inr($item->unit_price) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end">{{ money_inr($item->total_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card card-soft h-100">
                    <div class="card-header-brand"><h3 class="h6 mb-0">Amount summary</h3></div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-7 text-muted">Food subtotal</dt><dd class="col-5 text-end">{{ money_inr($order->food_subtotal) }}</dd>
                            @if ((float) $order->discount_amount > 0)
                                <dt class="col-7 text-muted">Discount</dt><dd class="col-5 text-end">−{{ money_inr($order->discount_amount) }}</dd>
                            @endif
                            @if ((float) $order->food_gst_amount > 0)
                                <dt class="col-7 text-muted">Food GST</dt><dd class="col-5 text-end">{{ money_inr($order->food_gst_amount) }}</dd>
                            @endif
                            <dt class="col-7 text-muted">Delivery charge</dt><dd class="col-5 text-end">{{ money_inr($order->delivery_charge) }}</dd>
                            @if ((float) $order->packaging_charge > 0)
                                <dt class="col-7 text-muted">Packaging</dt><dd class="col-5 text-end">{{ money_inr($order->packaging_charge) }}</dd>
                            @endif
                            <dt class="col-7 fw-semibold">Grand total</dt><dd class="col-5 text-end fw-semibold">{{ money_inr($order->grand_total) }}</dd>
                        </dl>
                        @if ($order->cancellation_reason)
                            <div class="alert alert-warning small mt-3 mb-0">{{ $order->cancellation_reason }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-soft h-100">
                    <div class="card-header-brand"><h3 class="h6 mb-0">Payment details</h3></div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted">Method</dt>
                            <dd class="col-7">{{ strtoupper($order->payment_method->value) }}</dd>
                            <dt class="col-5 text-muted">Status</dt>
                            <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $order->payment_status->value)) }}</dd>
                            <dt class="col-5 text-muted">Amount</dt>
                            <dd class="col-7">{{ money_inr($order->grand_total) }}</dd>
                            @if ($order->payment)
                                @if ($order->payment->gateway)
                                    <dt class="col-5 text-muted">Gateway</dt><dd class="col-7">{{ $order->payment->gateway }}</dd>
                                @endif
                                @if ($order->payment->gateway_payment_id)
                                    <dt class="col-5 text-muted">Txn ID</dt><dd class="col-7 text-break">{{ $order->payment->gateway_payment_id }}</dd>
                                @endif
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        @if ($order->deliveryAssignments->count() > 1)
            <div class="card card-soft mt-3">
                <div class="card-header-brand"><h3 class="h6 mb-0">Assignment history</h3></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Rider</th><th>Status</th><th>Assigned</th><th>Note</th></tr></thead>
                            <tbody>
                                @foreach ($order->deliveryAssignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->deliveryPartner?->name ?? '—' }}</td>
                                        <td>{{ ucfirst($assignment->status) }}</td>
                                        <td>{{ $assignment->assigned_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                        <td>{{ $assignment->note ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
