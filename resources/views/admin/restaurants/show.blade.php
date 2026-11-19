@extends('layouts.admin')
@section('page-title', $restaurant->name)
@section('content')
@if ($errors->has('approve'))
    <div class="alert alert-danger">{{ $errors->first('approve') }}</div>
@endif
@php
    $documentService = app(\App\Services\RestaurantDocumentService::class);
    $canApproveDocs = $documentService->hasAllRequiredVerified($restaurant);
    $statusClass = match (true) {
        $restaurant->is_blocked, $restaurant->status->value === 'rejected' => 'status-cancelled',
        $restaurant->status->value === 'pending', $restaurant->status->value === 'suspended' => 'status-pending',
        default => 'status-delivered',
    };
    $activeTab = request('tab', 'profile');
@endphp

<div class="row g-4">
    <div class="col-lg-4 order-lg-2">
        <div class="card card-soft entity-sidebar overflow-hidden">
            <div class="entity-sidebar-hero">
                <div class="d-flex align-items-center gap-3">
                    @if ($restaurant->logo)
                        <img src="{{ asset('storage/'.$restaurant->logo) }}" class="entity-sidebar-avatar rounded border" width="72" height="72" alt="">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($restaurant->name) }}&background=ff8c00&color=fff&size=144" class="entity-sidebar-avatar rounded-circle border" width="72" height="72" alt="">
                    @endif
                    <div class="min-w-0 flex-grow-1">
                        <h2 class="h6 mb-1 text-truncate">{{ $restaurant->name }}</h2>
                        <span class="status-badge {{ $statusClass }}">{{ $restaurant->accountStatusLabel() }}</span>
                    </div>
                </div>
                <ul class="entity-sidebar-meta list-unstyled mb-0 mt-3">
                    <li><i class="bi bi-calendar3"></i> Joined {{ $restaurant->created_at->format('M j, Y') }}</li>
                    @if ($restaurant->city)
                        <li><i class="bi bi-geo-alt"></i> {{ $restaurant->city }}, {{ $restaurant->state }}</li>
                    @endif
                    <li><i class="bi bi-shop"></i> {{ $restaurant->is_open ? 'Open for orders' : 'Closed right now' }}</li>
                </ul>
            </div>

            <div class="entity-sidebar-body">
                @if ($restaurant->status->value === 'pending')
                    <div class="entity-action-group">
                        <div class="entity-action-group-label">Registration</div>
                        @if (! $canApproveDocs)
                            <p class="entity-action-hint">Verify all required documents before approval.</p>
                        @endif
                        <form method="POST" action="{{ route('admin.restaurants.approve', $restaurant) }}" class="entity-action-form">
                            @csrf
                            <button type="submit" class="entity-action-item entity-action-item--success" @disabled(! $canApproveDocs)>
                                <span class="entity-action-icon"><i class="bi bi-check-circle"></i></span>
                                <span class="entity-action-text">
                                    <span class="entity-action-title">Approve registration</span>
                                    <span class="entity-action-desc">Allow this store to go live</span>
                                </span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.restaurants.reject', $restaurant) }}" class="entity-reject-form">
                            @csrf
                            <label class="form-label small text-muted mb-1" for="rejection-reason">Rejection reason</label>
                            <input name="rejection_reason" id="rejection-reason" class="form-control form-control-sm mb-2" placeholder="Brief reason for the restaurant" required>
                            <button type="submit" class="entity-action-item entity-action-item--danger w-100 border-0">
                                <span class="entity-action-icon"><i class="bi bi-x-circle"></i></span>
                                <span class="entity-action-text">
                                    <span class="entity-action-title">Reject registration</span>
                                </span>
                            </button>
                        </form>
                    </div>
                @endif

                <div class="entity-action-group">
                    <div class="entity-action-group-label">Account status</div>

                    @if ($restaurant->status->value === 'approved')
                        <form
                            method="POST"
                            action="{{ route('admin.restaurants.suspend', $restaurant) }}"
                            class="entity-action-form"
                            data-confirm="Suspend «{{ $restaurant->name }}»? They will stop receiving orders until you activate them again."
                            data-confirm-variant="warning"
                            data-confirm-title="Suspend restaurant"
                            data-confirm-label="Yes, suspend"
                        >
                            @csrf
                            <button type="submit" class="entity-action-item entity-action-item--warning">
                                <span class="entity-action-icon"><i class="bi bi-pause-circle"></i></span>
                                <span class="entity-action-text">
                                    <span class="entity-action-title">Suspend restaurant</span>
                                    <span class="entity-action-desc">Temporarily stop new orders</span>
                                </span>
                                <i class="bi bi-chevron-right entity-action-chevron" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif

                    @if ($restaurant->status->value === 'suspended')
                        <form method="POST" action="{{ route('admin.restaurants.activate', $restaurant) }}" class="entity-action-form">
                            @csrf
                            <button type="submit" class="entity-action-item entity-action-item--success">
                                <span class="entity-action-icon"><i class="bi bi-play-circle"></i></span>
                                <span class="entity-action-text">
                                    <span class="entity-action-title">Activate restaurant</span>
                                    <span class="entity-action-desc">Restore live operations</span>
                                </span>
                            </button>
                        </form>
                    @endif

                    @if ($restaurant->is_blocked)
                        <form method="POST" action="{{ route('admin.restaurants.unblock', $restaurant) }}" class="entity-action-form">
                            @csrf
                            <button type="submit" class="entity-action-item entity-action-item--success">
                                <span class="entity-action-icon"><i class="bi bi-unlock"></i></span>
                                <span class="entity-action-text">
                                    <span class="entity-action-title">Unblock restaurant</span>
                                    <span class="entity-action-desc">Restore login access</span>
                                </span>
                            </button>
                        </form>
                    @else
                        <form
                            method="POST"
                            action="{{ route('admin.restaurants.block', $restaurant) }}"
                            class="entity-action-form"
                            data-confirm="Block «{{ $restaurant->name }}»? They will not be able to log in until you unblock them."
                            data-confirm-variant="danger"
                            data-confirm-title="Block restaurant"
                            data-confirm-label="Yes, block"
                        >
                            @csrf
                            <button type="submit" class="entity-action-item entity-action-item--danger-soft">
                                <span class="entity-action-icon"><i class="bi bi-slash-circle"></i></span>
                                <span class="entity-action-text">
                                    <span class="entity-action-title">Block restaurant</span>
                                    <span class="entity-action-desc">Prevent login and access</span>
                                </span>
                                <i class="bi bi-chevron-right entity-action-chevron" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <div class="entity-action-group">
                    <div class="entity-action-group-label">Manage</div>
                    <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="entity-action-item entity-action-item--neutral">
                        <span class="entity-action-icon"><i class="bi bi-pencil-square"></i></span>
                        <span class="entity-action-text">
                            <span class="entity-action-title">Edit profile</span>
                            <span class="entity-action-desc">Update details &amp; documents</span>
                        </span>
                        <i class="bi bi-chevron-right entity-action-chevron" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('admin.settlements.create', $restaurant) }}" class="entity-action-item entity-action-item--neutral">
                        <span class="entity-action-icon"><i class="bi bi-wallet2"></i></span>
                        <span class="entity-action-text">
                            <span class="entity-action-title">Create settlement</span>
                            <span class="entity-action-desc">Record a payout for this store</span>
                        </span>
                        <i class="bi bi-chevron-right entity-action-chevron" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="entity-action-group entity-action-group--danger">
                    <div class="entity-action-group-label">Danger zone</div>
                    <form
                        method="POST"
                        action="{{ route('admin.restaurants.destroy', $restaurant) }}"
                        class="entity-action-form"
                        data-confirm="Delete {{ $restaurant->name }} permanently? This cannot be undone."
                        data-confirm-variant="danger"
                        data-confirm-title="Delete restaurant"
                        data-confirm-label="Yes, delete"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="entity-action-item entity-action-item--danger">
                            <span class="entity-action-icon"><i class="bi bi-trash3"></i></span>
                            <span class="entity-action-text">
                                <span class="entity-action-title">Delete restaurant</span>
                                <span class="entity-action-desc">Remove all data permanently</span>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 order-lg-1">
        <ul class="nav nav-tabs nav-tabs-panel mb-3" role="tablist">
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}" href="{{ route('admin.restaurants.show', [$restaurant, 'tab' => 'profile']) }}">Profile</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'menu' ? 'active' : '' }}" href="{{ route('admin.restaurants.show', [$restaurant, 'tab' => 'menu']) }}">Menu ({{ $menuItems->count() }})</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'orders' ? 'active' : '' }}" href="{{ route('admin.restaurants.show', [$restaurant, 'tab' => 'orders']) }}">Orders &amp; sales</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'reviews' ? 'active' : '' }}" href="{{ route('admin.restaurants.show', [$restaurant, 'tab' => 'reviews']) }}">Reviews</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab === 'earnings' ? 'active' : '' }}" href="{{ route('admin.restaurants.show', [$restaurant, 'tab' => 'earnings']) }}">Commission &amp; earnings</a></li>
        </ul>

        @if ($activeTab === 'profile')
            <div class="card card-soft mb-3">
                <div class="card-body">
                    <h3 class="h6 mb-3">Restaurant profile</h3>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Owner</dt>
                        <dd class="col-sm-8">{{ $restaurant->owner_name }}</dd>
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $restaurant->email }}</dd>
                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8">{{ $restaurant->phone }}</dd>
                        <dt class="col-sm-4 text-muted">Address</dt>
                        <dd class="col-sm-8">{{ $restaurant->address }}, {{ $restaurant->city }}, {{ $restaurant->state }} {{ $restaurant->pincode }}</dd>
                        <dt class="col-sm-4 text-muted">Open now</dt>
                        <dd class="col-sm-8">{{ $restaurant->is_open ? 'Yes' : 'No' }}</dd>
                        <dt class="col-sm-4 text-muted">GSTIN</dt>
                        <dd class="col-sm-8">{{ $restaurant->gst_number ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted">GST billing</dt>
                        <dd class="col-sm-8">
                            @if ($restaurant->gst_enabled)
                                Enabled · default {{ $restaurant->default_gst_percentage ?? 'master' }}%
                            @else
                                Disabled
                            @endif
                        </dd>
                        @if ($restaurant->description)
                            <dt class="col-sm-4 text-muted">About</dt>
                            <dd class="col-sm-8">{{ $restaurant->description }}</dd>
                        @endif
                    </dl>
                    @if ($restaurant->rejection_reason)
                        <div class="alert alert-danger small mt-3 mb-0">{{ $restaurant->rejection_reason }}</div>
                    @endif
                </div>
            </div>

            @include('partials.restaurant-documents-display', [
                'restaurant' => $restaurant,
                'adminReview' => true,
                'showEditHint' => true,
            ])

            @include('admin.partials.commission-settings-card', [
                'restaurant' => $restaurant,
                'masterCommission' => $defaultCommission,
                'activeCommission' => $restaurant->activeCommission,
                'commissionScopeLabel' => 'restaurant',
                'commissionFormAction' => route('admin.commissions.restaurant', $restaurant),
            ])
        @elseif ($activeTab === 'menu')
            <x-panel-table title="Menu &amp; food items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Available</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menuItems as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td>{{ $item->category?->displayLabel() ?? '—' }}</td>
                            <td>{{ money_inr($item->price) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $item->food_type ?? '—')) }}</td>
                            <td>{{ $item->is_available && ! $item->is_sold_out ? 'Yes' : 'No' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.food-items.show', $item) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No menu items yet.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @elseif ($activeTab === 'orders')
            <div class="row g-3 mb-3">
                <div class="col-md-4"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Total orders</div><div class="h5 mb-0">{{ $salesSummary['orders'] }}</div></div></div></div>
                <div class="col-md-4"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Delivered</div><div class="h5 mb-0">{{ $salesSummary['delivered'] }}</div></div></div></div>
                <div class="col-md-4"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Sales revenue</div><div class="h5 mb-0">{{ money_inr($salesSummary['revenue']) }}</div></div></div></div>
            </div>
            <x-panel-table title="Recent orders">
                <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th class="text-end"></th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="fw-semibold">#{{ $order->order_number }}</td>
                            <td>{{ $order->customer_name ?? '—' }}</td>
                            <td>{{ money_inr($order->grand_total) }}</td>
                            <td><span class="status-badge status-default">{{ $order->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @elseif ($activeTab === 'reviews')
            <div class="card card-soft mb-3">
                <div class="card-body py-3">
                    <span class="small text-muted">Average rating</span>
                    <div class="h5 mb-0">{{ $averageRating ? number_format($averageRating, 1).' / 5' : 'No ratings yet' }}</div>
                </div>
            </div>
            <x-panel-table title="Ratings &amp; reviews">
                <thead><tr><th>Rating</th><th>Comment</th><th>Visible</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>{{ $review->rating }}/5</td>
                            <td>{{ $review->comment ?? '—' }}</td>
                            <td>{{ $review->is_visible ? 'Yes' : 'No' }}</td>
                            <td>{{ $review->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No reviews yet.</td></tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @else
            <div class="card card-soft mb-3">
                <div class="card-body small">
                    @php($commissionResolved = app(\App\Services\CommissionService::class)->resolveRestaurantCommission($restaurant))
                    <strong>Commission rate:</strong>
                    {{ $commissionResolved['percentage'] }}%
                    ({{ $commissionResolved['source'] === 'profile' ? 'profile se set' : 'default settings' }})
                    · <a href="{{ route('admin.commissions.index') }}">Manage commissions</a>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Net earnings</div><div class="h6 mb-0">{{ money_inr($earningsSummary['total']) }}</div></div></div></div>
                <div class="col-md-3"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Commission paid</div><div class="h6 mb-0">{{ money_inr($earningsSummary['commission']) }}</div></div></div></div>
                <div class="col-md-3"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Settled</div><div class="h6 mb-0">{{ money_inr($earningsSummary['settled']) }}</div></div></div></div>
                <div class="col-md-3"><div class="card card-soft"><div class="card-body"><div class="small text-muted">Pending</div><div class="h6 mb-0">{{ money_inr($earningsSummary['pending']) }}</div></div></div></div>
            </div>
            <x-panel-table title="Earnings by order">
                <thead><tr><th>Order</th><th>Net earning</th><th>Commission</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($earnings as $earning)
                        <tr>
                            <td class="fw-semibold">#{{ $earning->order?->order_number ?? '—' }}</td>
                            <td>{{ money_inr($earning->net_earning) }}</td>
                            <td>{{ money_inr($earning->commission_amount) }} ({{ $earning->commission_percentage }}%)</td>
                            <td>{{ ucfirst($earning->status) }}</td>
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
