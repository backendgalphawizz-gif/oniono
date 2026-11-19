@extends('layouts.admin')
@section('page-title', $user->name)
@section('content')
@php
    $statusClass = match (true) {
        $user->is_blocked => 'status-cancelled',
        $user->is_suspended => 'status-pending',
        default => 'status-delivered',
    };
    $activeTab = request('tab', 'profile');
@endphp

<div class="row g-4">
    <div class="col-lg-4 order-lg-2">
        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=ff8c00&color=fff"
                        class="rounded-circle border"
                        width="56"
                        height="56"
                        alt=""
                    >
                    <div>
                        <h2 class="h6 mb-0">{{ $user->name }}</h2>
                        <span class="status-badge {{ $statusClass }}">{{ $user->accountStatusLabel() }}</span>
                    </div>
                </div>
                <p class="small text-muted mb-0">Member since {{ $user->created_at->format('M j, Y') }}</p>
            </div>
        </div>

        <div class="card card-soft mt-3">
            <div class="card-body panel-action-stack">
                @if ($user->is_blocked)
                    <form method="POST" action="{{ route('admin.users.unblock', $user) }}">@csrf<button type="submit" class="btn btn-success btn-sm">Unblock user</button></form>
                @else
                    <form method="POST" action="{{ route('admin.users.block', $user) }}">@csrf<button type="submit" class="btn btn-warning btn-sm">Block user</button></form>
                @endif

                @if ($user->is_suspended)
                    <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">@csrf<button type="submit" class="btn btn-success btn-sm">Remove suspension</button></form>
                @else
                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}">@csrf<button type="submit" class="btn btn-outline-warning btn-sm">Suspend user</button></form>
                @endif

                @if ($user->is_verified)
                    <form method="POST" action="{{ route('admin.users.unverify', $user) }}">@csrf<button type="submit" class="btn btn-outline-secondary btn-sm">Remove verification</button></form>
                @else
                    <form method="POST" action="{{ route('admin.users.verify', $user) }}">@csrf<button type="submit" class="btn btn-success btn-sm">Verify account</button></form>
                @endif

                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm">Edit profile</a>

                <div class="panel-action-stack__danger">
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user permanently?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete user</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 order-lg-1">
        <ul class="nav nav-tabs nav-tabs-panel mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}" href="{{ route('admin.users.show', [$user, 'tab' => 'profile']) }}">Profile</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === 'orders' ? 'active' : '' }}" href="{{ route('admin.users.show', [$user, 'tab' => 'orders']) }}">Orders ({{ $orders->count() }})</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === 'payments' ? 'active' : '' }}" href="{{ route('admin.users.show', [$user, 'tab' => 'payments']) }}">Payments ({{ $payments->count() }})</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === 'complaints' ? 'active' : '' }}" href="{{ route('admin.users.show', [$user, 'tab' => 'complaints']) }}">Complaints ({{ $complaints->count() }})</a>
            </li>
        </ul>

        @if ($activeTab === 'profile')
            <div class="card card-soft">
                <div class="card-body">
                    <h3 class="h6 mb-3">Profile details</h3>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8">{{ $user->phone ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Verified</dt>
                        <dd class="col-sm-8">{{ $user->is_verified ? 'Yes' : 'No' }}</dd>
                        <dt class="col-sm-4 text-muted">Email verified at</dt>
                        <dd class="col-sm-8">{{ $user->email_verified_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Last updated</dt>
                        <dd class="col-sm-8">{{ $user->updated_at->format('M j, Y g:i A') }}</dd>
                    </dl>

                    @if ($user->addresses->isNotEmpty())
                        <hr>
                        <h3 class="h6 mb-3">Saved addresses</h3>
                        <ul class="list-group list-group-flush">
                            @foreach ($user->addresses as $address)
                                <li class="list-group-item px-0">
                                    <div class="fw-semibold">{{ $address->label ?? 'Address' }} @if($address->is_default)<span class="badge text-bg-light border">Default</span>@endif</div>
                                    <div class="small text-muted">{{ $address->address_line }}, {{ $address->city }}, {{ $address->state }} {{ $address->pincode }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @elseif ($activeTab === 'orders')
            <x-panel-table title="Order history">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Restaurant</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="fw-semibold">#{{ $order->order_number }}</td>
                            <td>{{ $order->restaurant?->name ?? '—' }}</td>
                            <td>{{ money_inr($order->grand_total) }}</td>
                            <td><span class="status-badge status-default">{{ $order->status->label() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @elseif ($activeTab === 'payments')
            <x-panel-table title="Payment history">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="fw-semibold">#{{ $payment->order?->order_number ?? '—' }}</td>
                            <td>{{ strtoupper($payment->method ?? '—') }}</td>
                            <td>{{ money_inr($payment->amount) }}</td>
                            <td><span class="status-badge status-default">{{ ucfirst(str_replace('_', ' ', $payment->status->value)) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @else
            <x-panel-table title="Complaint history">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($complaints as $ticket)
                        <tr>
                            <td class="fw-semibold">{{ $ticket->ticket_number }}</td>
                            <td>{{ $ticket->subject }}</td>
                            <td>{{ ucfirst($ticket->priority ?? 'normal') }}</td>
                            <td>{{ ucfirst($ticket->status ?? 'open') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No support tickets from this user.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @endif
    </div>
</div>
@endsection
