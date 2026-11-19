@extends('layouts.admin')
@section('page-title', 'Customer reviews')
@section('content')

<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input type="search" name="search" id="filter-search" class="form-control" placeholder="Customer, restaurant, comment…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-rating">Rating</label>
            <select name="rating" id="filter-rating" class="form-select">
                <option value="">All</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected((string) request('rating') === (string) $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-visibility">Visibility</label>
            <select name="visibility" id="filter-visibility" class="form-select">
                <option value="">All</option>
                <option value="visible" @selected(request('visibility') === 'visible')>Visible</option>
                <option value="hidden" @selected(request('visibility') === 'hidden')>Hidden</option>
            </select>
        </div>
    </x-slot:filter>
</x-panel-toolbar>

<x-panel-table title="Customer reviews" :paginator="$reviews">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Restaurant</th>
            <th>Order</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($reviews as $review)
            <tr>
                <td>
                    @if ($review->user)
                        <a href="{{ route('admin.users.show', $review->user_id) }}" class="fw-semibold">{{ $review->user->name }}</a>
                        <div class="small text-muted">{{ $review->user->email ?? $review->user->phone ?? '—' }}</div>
                    @else
                        <span class="text-muted">Guest</span>
                    @endif
                </td>
                <td>
                    @if ($review->restaurant)
                        <a href="{{ route('admin.restaurants.show', $review->restaurant_id) }}">{{ $review->restaurant->name }}</a>
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if ($review->order)
                        <a href="{{ route('admin.orders.show', $review->order_id) }}">{{ $review->order->order_number }}</a>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <span class="text-warning" aria-label="{{ $review->rating }} out of 5 stars">
                        @for ($s = 1; $s <= 5; $s++)
                            <i class="bi bi-star{{ $s <= $review->rating ? '-fill' : '' }}"></i>
                        @endfor
                    </span>
                </td>
                <td class="review-comment-cell">{{ $review->comment ?: '—' }}</td>
                <td>
                    @if ($review->is_visible)
                        <span class="status-badge status-delivered">Visible</span>
                    @else
                        <span class="status-badge status-default">Hidden</span>
                    @endif
                </td>
                <td class="text-muted small text-nowrap">{{ $review->created_at->format('M j, Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">No customer reviews yet.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>

@endsection
