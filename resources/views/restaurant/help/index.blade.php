@extends('layouts.restaurant')

@section('page-title', 'Support & help')

@section('content')
<div class="mb-3">
    <h2 class="h5 mb-1">Support &amp; help</h2>
    <p class="text-muted small mb-0">Raise tickets, reach our team, and read partner FAQs and policies.</p>
</div>

@include('restaurant.help.partials.subnav')

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-4">
        <a href="{{ route('restaurant.support.index') }}#new-ticket" class="card card-soft h-100 text-decoration-none">
            <div class="card-body">
                <span class="dashboard-kpi-icon tone-orange d-inline-flex mb-2"><i class="bi bi-plus-circle"></i></span>
                <h3 class="h6 text-body mb-1">Raise a support ticket</h3>
                <p class="small text-muted mb-2">Describe payouts, orders, menu, or account issues. Our team will reply in the thread.</p>
                @if ($openTickets > 0)
                    <span class="badge text-bg-warning">{{ $openTickets }} open</span>
                @else
                    <span class="small text-brand">Create ticket →</span>
                @endif
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="{{ route('restaurant.help.contact') }}" class="card card-soft h-100 text-decoration-none">
            <div class="card-body">
                <span class="dashboard-kpi-icon tone-teal d-inline-flex mb-2"><i class="bi bi-headset"></i></span>
                <h3 class="h6 text-body mb-1">Contact support team</h3>
                <p class="small text-muted mb-2">Phone, email, and office details managed by {{ site_name() }}.</p>
                <span class="small text-brand">View contacts →</span>
            </div>
        </a>
    </div>
    @foreach ($featured as $card)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('restaurant.help.page', $card['slug']) }}" class="card card-soft h-100 text-decoration-none {{ ! $card['active'] ? 'opacity-75' : '' }}">
                <div class="card-body">
                    <span class="dashboard-kpi-icon tone-purple d-inline-flex mb-2"><i class="bi bi-{{ $card['icon'] }}"></i></span>
                    <h3 class="h6 text-body mb-1">{{ $card['title'] }}</h3>
                    <p class="small text-muted mb-2">{{ $card['description'] }}</p>
                    <span class="small text-brand">Read FAQs →</span>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-soft">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent tickets</span>
                <a href="{{ route('restaurant.support.index') }}" class="small">View all</a>
            </div>
            <div class="card-body pt-0">
                @forelse ($recentTickets as $ticket)
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">{{ $ticket->subject }}</div>
                            <div class="text-muted" style="font-size: 0.75rem">{{ $ticket->ticket_number }} · {{ $ticket->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge text-bg-{{ $ticket->statusBadgeClass() }}">{{ $ticket->statusLabel() }}</span>
                            <a href="{{ route('restaurant.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Open</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-2">No tickets yet. Use <a href="{{ route('restaurant.support.index') }}#new-ticket">My tickets</a> to get help.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-soft h-100">
            <div class="card-header bg-transparent border-0 fw-semibold">Policies &amp; information</div>
            <div class="list-group list-group-flush">
                @foreach ($legal as $item)
                    <a
                        href="{{ route('restaurant.help.page', $item['slug']) }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                    >
                        <span>{{ $item['title'] }}</span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
