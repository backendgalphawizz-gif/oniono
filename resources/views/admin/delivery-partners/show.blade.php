@extends('layouts.admin')
@section('page-title', $deliveryPartner->name)
@section('content')
@if ($errors->has('approve'))
    <div class="alert alert-danger">{{ $errors->first('approve') }}</div>
@endif
@php
    $documentService = app(\App\Services\DeliveryPartnerDocumentService::class);
    $canApproveDocs = $documentService->hasAllRequiredVerified($deliveryPartner);
    $statusClass = match (true) {
        $deliveryPartner->is_blocked, $deliveryPartner->status === 'rejected' => 'status-cancelled',
        $deliveryPartner->is_suspended, $deliveryPartner->status === 'pending' => 'status-pending',
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
                        src="https://ui-avatars.com/api/?name={{ urlencode($deliveryPartner->name) }}&background=7c3aed&color=fff"
                        class="rounded-circle border"
                        width="56"
                        height="56"
                        alt=""
                    >
                    <div>
                        <h2 class="h6 mb-0">{{ $deliveryPartner->name }}</h2>
                        <span class="status-badge {{ $statusClass }}">{{ $deliveryPartner->accountStatusLabel() }}</span>
                    </div>
                </div>
                <p class="small text-muted mb-0">Partner since {{ $deliveryPartner->created_at->format('M j, Y') }}</p>
            </div>
        </div>

        <div class="card card-soft mt-3">
            <div class="card-body panel-action-stack">
                @if ($deliveryPartner->status === 'pending')
                    @if (! $canApproveDocs)
                        <p class="small text-muted mb-2 panel-action-stack__full">Verify all required documents before approval.</p>
                    @endif
                    <form method="POST" action="{{ route('admin.delivery-partners.approve', $deliveryPartner) }}">@csrf<button type="submit" class="btn btn-success btn-sm" @disabled(! $canApproveDocs)>Approve registration</button></form>
                    <form method="POST" action="{{ route('admin.delivery-partners.reject', $deliveryPartner) }}" class="panel-action-stack__full">
                        @csrf
                        <input name="rejection_reason" class="form-control form-control-sm mb-2" placeholder="Rejection reason" required>
                        <button class="btn btn-danger btn-sm w-100">Reject registration</button>
                    </form>
                @endif

                @if ($deliveryPartner->is_blocked)
                    <form method="POST" action="{{ route('admin.delivery-partners.unblock', $deliveryPartner) }}">@csrf<button class="btn btn-success btn-sm">Unblock partner</button></form>
                @else
                    <form method="POST" action="{{ route('admin.delivery-partners.block', $deliveryPartner) }}">@csrf<button class="btn btn-warning btn-sm">Block partner</button></form>
                @endif

                @if ($deliveryPartner->is_suspended)
                    <form method="POST" action="{{ route('admin.delivery-partners.unsuspend', $deliveryPartner) }}">@csrf<button class="btn btn-success btn-sm">Remove suspension</button></form>
                @else
                    <form method="POST" action="{{ route('admin.delivery-partners.suspend', $deliveryPartner) }}">@csrf<button class="btn btn-outline-warning btn-sm">Suspend partner</button></form>
                @endif

                <a href="{{ route('admin.delivery-partners.edit', $deliveryPartner) }}" class="btn btn-outline-primary btn-sm">Edit profile</a>

                <div class="panel-action-stack__danger">
                    <form method="POST" action="{{ route('admin.delivery-partners.destroy', $deliveryPartner) }}" onsubmit="return confirm('Delete this partner permanently?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete partner</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 order-lg-1">
        <ul class="nav nav-tabs nav-tabs-panel mb-3" role="tablist">
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}" href="{{ route('admin.delivery-partners.show', [$deliveryPartner, 'tab' => 'profile']) }}">Profile</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'assigned' ? 'active' : '' }}" href="{{ route('admin.delivery-partners.show', [$deliveryPartner, 'tab' => 'assigned']) }}">Assigned ({{ $assignedDeliveries->count() }})</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'completed' ? 'active' : '' }}" href="{{ route('admin.delivery-partners.show', [$deliveryPartner, 'tab' => 'completed']) }}">Completed ({{ $completedDeliveries->count() }})</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'reviews' ? 'active' : '' }}" href="{{ route('admin.delivery-partners.show', [$deliveryPartner, 'tab' => 'reviews']) }}">Reviews</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'earnings' ? 'active' : '' }}" href="{{ route('admin.delivery-partners.show', [$deliveryPartner, 'tab' => 'earnings']) }}">Earnings</a></li>
        </ul>

        @if ($activeTab === 'profile')
            <div class="card card-soft mb-3">
                <div class="card-body">
                    <h3 class="h6 mb-3">Contact &amp; vehicle</h3>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $deliveryPartner->email }}</dd>
                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8">{{ $deliveryPartner->phone }}</dd>
                        <dt class="col-sm-4 text-muted">Vehicle</dt>
                        <dd class="col-sm-8">{{ $deliveryPartner->vehicle_type ?? '—' }} @if($deliveryPartner->vehicle_number)({{ $deliveryPartner->vehicle_number }})@endif</dd>
                        <dt class="col-sm-4 text-muted">Registration</dt>
                        <dd class="col-sm-8">{{ ucfirst($deliveryPartner->status) }}</dd>
                        <dt class="col-sm-4 text-muted">Available</dt>
                        <dd class="col-sm-8">{{ $deliveryPartner->is_available ? 'Yes' : 'No' }}</dd>
                    </dl>
                    @if ($deliveryPartner->rejection_reason)
                        <div class="alert alert-danger small mt-3 mb-0">{{ $deliveryPartner->rejection_reason }}</div>
                    @endif
                </div>
            </div>

            @include('partials.delivery-partner-documents-display', [
                'deliveryPartner' => $deliveryPartner,
                'adminReview' => true,
                'showEditHint' => true,
            ])

            @include('admin.partials.commission-settings-card', [
                'deliveryPartner' => $deliveryPartner,
                'masterCommission' => $defaultDeliveryCommission,
                'activeCommission' => $deliveryPartner->activeCommission,
                'commissionScopeLabel' => 'partner',
                'commissionFormAction' => route('admin.commissions.delivery-partner', $deliveryPartner),
            ])

            @include('partials.delivery-partner-document-checklist', [
                'deliveryPartner' => $deliveryPartner,
                'adminReview' => true,
            ])
        @elseif ($activeTab === 'assigned')
            <x-panel-table title="Assigned deliveries">
                <thead><tr><th>Order</th><th>Restaurant</th><th>Status</th><th>Assigned</th><th class="text-end"></th></tr></thead>
                <tbody>
                    @forelse ($assignedDeliveries as $assignment)
                        <tr>
                            <td class="fw-semibold">#{{ $assignment->order?->order_number }}</td>
                            <td>{{ $assignment->order?->restaurant?->name ?? '—' }}</td>
                            <td>{{ $assignment->order?->status?->label() ?? '—' }}</td>
                            <td>{{ $assignment->assigned_at?->format('M j, Y') }}</td>
                            <td class="text-end"><a href="{{ route('admin.orders.show', $assignment->order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No active assignments.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @elseif ($activeTab === 'completed')
            <x-panel-table title="Completed deliveries">
                <thead><tr><th>Order</th><th>Restaurant</th><th>Total</th><th>Delivered</th><th class="text-end"></th></tr></thead>
                <tbody>
                    @forelse ($completedDeliveries as $assignment)
                        <tr>
                            <td class="fw-semibold">#{{ $assignment->order?->order_number }}</td>
                            <td>{{ $assignment->order?->restaurant?->name ?? '—' }}</td>
                            <td>{{ money_inr($assignment->order?->grand_total) }}</td>
                            <td>{{ $assignment->order?->delivered_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="text-end"><a href="{{ route('admin.orders.show', $assignment->order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No completed deliveries yet.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @elseif ($activeTab === 'reviews')
            <div class="card card-soft">
                <div class="card-body">
                    @if (! $reviewsEnabled)
                        <p class="text-muted mb-0">Delivery partner reviews are disabled. Enable <strong>Delivery partner reviews</strong> in Settings to show ratings from completed orders.</p>
                    @else
                        <x-panel-table title="Ratings &amp; reviews">
                            <thead><tr><th>Order</th><th>Restaurant</th><th>Rating</th><th>Comment</th></tr></thead>
                            <tbody>
                                @forelse ($reviews as $review)
                                    <tr>
                                        <td>#{{ $review->order?->order_number ?? '—' }}</td>
                                        <td>{{ $review->restaurant?->name ?? '—' }}</td>
                                        <td>{{ $review->rating }}/5</td>
                                        <td>{{ $review->comment ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No reviews for this partner’s deliveries.</td></tr>
                                @endforelse
                            </tbody>
                        </x-panel-table>
                    @endif
                </div>
            </div>
        @else
            <div class="row g-3 mb-3">
                <div class="col-md-4"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Total earnings</div><div class="h5 mb-0">{{ money_inr($earningsSummary['total']) }}</div></div></div></div>
                <div class="col-md-4"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Paid out</div><div class="h5 mb-0">{{ money_inr($earningsSummary['paid']) }}</div></div></div></div>
                <div class="col-md-4"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Pending payout</div><div class="h5 mb-0">{{ money_inr($earningsSummary['pending']) }}</div></div></div></div>
            </div>
            <x-panel-table title="Earnings &amp; payouts">
                <thead><tr><th>Order</th><th>Amount</th><th>Payout status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse ($earnings as $earning)
                        <tr>
                            <td class="fw-semibold">#{{ $earning->order?->order_number ?? '—' }}</td>
                            <td>{{ money_inr($earning->amount) }}</td>
                            <td><span class="status-badge status-default">{{ ucfirst($earning->status) }}</span></td>
                            <td>{{ $earning->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No earnings recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @endif
    </div>
</div>
@endsection
