@extends('layouts.restaurant')

@section('page-title', 'Ticket '.$support->ticket_number)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h2 class="h5 mb-1">{{ $support->subject }}</h2>
        <p class="text-muted small mb-0">
            {{ $support->ticket_number }}
            · <span class="badge text-bg-{{ $support->statusBadgeClass() }}">{{ $support->statusLabel() }}</span>
            · Priority: <span class="text-capitalize">{{ $support->priority }}</span>
        </p>
    </div>
    <a href="{{ route('restaurant.support.index') }}" class="btn btn-sm btn-outline-secondary">All tickets</a>
</div>

@include('restaurant.help.partials.subnav')

<div class="card card-soft mb-4">
    <div class="card-header bg-transparent border-0 fw-semibold">Conversation</div>
    <div class="card-body pt-0">
        @foreach ($support->messages as $msg)
            <div class="d-flex mb-3 {{ $msg->isFromRestaurant() ? 'justify-content-end' : '' }}">
                <div class="rounded-3 px-3 py-2 border {{ $msg->isFromRestaurant() ? 'bg-white border-warning-subtle' : 'bg-light' }}" style="max-width: min(100%, 36rem);">
                    <div class="small fw-semibold mb-1">{{ $msg->senderLabel() }}</div>
                    <div class="small" style="white-space: pre-wrap;">{{ $msg->message }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem">{{ $msg->created_at->format('M j, Y g:i A') }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if ($support->status !== 'closed')
    <div class="card card-soft">
        <div class="card-header bg-transparent border-0 fw-semibold">Reply</div>
        <div class="card-body pt-0">
            <form method="POST" action="{{ route('restaurant.support.reply', $support) }}">
                @csrf
                <textarea name="message" rows="3" class="form-control mb-2 @error('message') is-invalid @enderror" placeholder="Add a follow-up message…" required>{{ old('message') }}</textarea>
                @error('message')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-brand btn-sm">Send reply</button>
            </form>
        </div>
    </div>
@else
    <p class="text-muted small">This ticket is closed. Send a reply to reopen it, or <a href="{{ route('restaurant.support.index') }}#new-ticket">open a new ticket</a>.</p>
@endif
@endsection
