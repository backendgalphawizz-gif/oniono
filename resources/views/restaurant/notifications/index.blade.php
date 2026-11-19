@extends('layouts.restaurant')

@section('page-title', 'Notifications')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="h5 mb-1">Notifications</h2>
        <p class="text-muted small mb-0">Orders, payouts, and platform messages for your restaurant.</p>
    </div>
    @if ($unreadCount > 0)
        <form method="POST" action="{{ route('restaurant.notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark all as read</button>
        </form>
    @endif
</div>

<div class="row g-2 mb-4">
    @foreach ($types as $typeKey => $meta)
        @php $count = (int) ($typeCounts[$typeKey] ?? 0); @endphp
        <div class="col-md-6 col-lg-3">
            <a
                href="{{ route('restaurant.notifications.index', ['type' => $typeKey]) }}"
                class="card card-soft text-decoration-none h-100 {{ request('type') === $typeKey ? 'border-primary' : '' }}"
            >
                <div class="card-body py-3 d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-light d-inline-flex p-2">
                        <i class="bi bi-{{ $meta['icon'] ?? 'bell' }} text-secondary"></i>
                    </span>
                    <div>
                        <div class="small fw-semibold text-body">{{ $meta['label'] ?? $typeKey }}</div>
                        <div class="small text-muted">{{ $count }} total</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<form method="GET" class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    @if (request('type'))
        <input type="hidden" name="type" value="{{ request('type') }}">
    @endif
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="unread_only" value="1" id="unread_only" @checked(request()->boolean('unread_only')) onchange="this.form.submit()">
        <label class="form-check-label small" for="unread_only">Unread only ({{ $unreadCount }})</label>
    </div>
    @if (request()->hasAny(['type', 'unread_only']))
        <a href="{{ route('restaurant.notifications.index') }}" class="small">Clear filters</a>
    @endif
</form>

<x-panel-table title="Inbox" :paginator="$notifications">
    <thead>
        <tr>
            <th>Type</th>
            <th>Title</th>
            <th>Message</th>
            <th>When</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($notifications as $notification)
            @php
                $meta = $types[$notification->type] ?? ['label' => $notification->typeLabel(), 'badge' => 'secondary', 'icon' => 'bell'];
            @endphp
            <tr class="{{ $notification->is_read ? '' : 'table-light fw-medium' }}">
                <td>
                    <span class="badge text-bg-{{ $meta['badge'] ?? 'secondary' }}">
                        <i class="bi bi-{{ $meta['icon'] ?? 'bell' }} me-1"></i>
                        {{ $meta['label'] ?? $notification->typeLabel() }}
                    </span>
                </td>
                <td>{{ $notification->title }}</td>
                <td class="small text-muted">{{ $notification->body ?: '—' }}</td>
                <td class="small text-muted">{{ $notification->created_at->diffForHumans() }}</td>
                <td class="text-end text-nowrap">
                    @if (! $notification->is_read)
                        <form method="POST" action="{{ route('restaurant.notifications.read', $notification) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-brand">Open</button>
                        </form>
                    @elseif ($notification->actionUrl())
                        <a href="{{ $notification->actionUrl() }}" class="btn btn-sm btn-outline-primary">View</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">No notifications yet.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
