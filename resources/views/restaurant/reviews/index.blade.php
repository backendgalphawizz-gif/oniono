@extends('layouts.restaurant')

@section('page-title', 'Ratings & reviews')

@section('content')
<div class="mb-4">
    <h2 class="h5 mb-1">Ratings &amp; reviews</h2>
    <p class="text-muted small mb-0">Customer feedback for your restaurant. Reviews are submitted by customers after orders.</p>
</div>

<div class="row g-3 g-xl-4 mb-4">
    <div class="col-md-4">
        <div class="dashboard-kpi dashboard-kpi--revenue h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-orange"><i class="bi bi-star-fill"></i></span>
            </div>
            <div class="dashboard-kpi-label">Average rating</div>
            <div class="dashboard-kpi-value">
                @if ($summary['total'] > 0)
                    {{ number_format($summary['average'], 1) }}<span class="fs-6 fw-normal text-muted"> / 5</span>
                @else
                    —
                @endif
            </div>
            <div class="dashboard-kpi-meta text-warning" aria-hidden="true">
                @for ($s = 1; $s <= 5; $s++)
                    <i class="bi bi-star{{ $s <= round($summary['average']) ? '-fill' : '' }}"></i>
                @endfor
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-kpi h-100">
            <div class="dashboard-kpi-head">
                <span class="dashboard-kpi-icon tone-purple"><i class="bi bi-chat-square-text"></i></span>
            </div>
            <div class="dashboard-kpi-label">Total reviews</div>
            <div class="dashboard-kpi-value">{{ number_format($summary['total']) }}</div>
            <div class="dashboard-kpi-meta">Visible customer reviews</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-soft h-100">
            <div class="card-body py-3">
                <div class="small fw-semibold mb-2">Rating breakdown</div>
                @for ($star = 5; $star >= 1; $star--)
                    @php
                        $count = $starCounts[$star] ?? 0;
                        $pct = $summary['total'] > 0 ? round(($count / $summary['total']) * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-center gap-2 small mb-1">
                        <span class="text-nowrap" style="width: 3rem;">{{ $star }} <i class="bi bi-star-fill text-warning"></i></span>
                        <div class="progress flex-grow-1" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-muted text-end" style="width: 2rem;">{{ $count }}</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search comment or customer name" value="{{ request('search') }}">
    </div>
    <div class="col-md-3">
        <select name="rating" class="form-select form-select-sm">
            <option value="">All ratings</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((string) request('rating') === (string) $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
            @endfor
        </select>
    </div>
    <div class="col-md-auto">
        <button type="submit" class="btn btn-sm btn-brand">Filter</button>
        @if (request()->hasAny(['search', 'rating']))
            <a href="{{ route('restaurant.reviews.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

<x-panel-table title="Customer reviews" :paginator="$reviews">
    <thead>
        <tr>
            <th>Rating</th>
            <th>Customer</th>
            <th>Order</th>
            <th>Review</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($reviews as $review)
            <tr>
                <td>
                    <span class="text-warning" aria-label="{{ $review->rating }} out of 5 stars">
                        @for ($s = 1; $s <= 5; $s++)
                            <i class="bi bi-star{{ $s <= $review->rating ? '-fill' : '' }}"></i>
                        @endfor
                    </span>
                    <span class="small text-muted d-block">{{ $review->rating }}/5</span>
                </td>
                <td>
                    <div class="fw-medium">{{ $review->user?->name ?? 'Customer' }}</div>
                    @if ($review->user?->phone)
                        <div class="small text-muted">{{ $review->user->phone }}</div>
                    @endif
                </td>
                <td class="small">
                    @if ($review->order)
                        <a href="{{ route('restaurant.orders.show', $review->order) }}">#{{ $review->order->order_number }}</a>
                    @else
                        —
                    @endif
                </td>
                <td class="review-comment-cell">{{ $review->comment ?: '—' }}</td>
                <td class="small text-muted">{{ $review->created_at->format('M j, Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    No reviews yet. Ratings appear here when customers review completed orders.
                </td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
