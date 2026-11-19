@extends('layouts.restaurant')

@section('page-title', 'Support tickets')

@section('content')
<div class="mb-3">
    <h2 class="h5 mb-1">My support tickets</h2>
    <p class="text-muted small mb-0">Track conversations with the {{ site_name() }} partner support team.</p>
</div>

@include('restaurant.help.partials.subnav')

<div class="card card-soft mb-4" id="new-ticket">
    <div class="card-header bg-transparent border-0 fw-semibold">Raise a new ticket</div>
    <div class="card-body pt-0">
        <form method="POST" action="{{ route('restaurant.support.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="subject">Subject</label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        class="form-control @error('subject') is-invalid @enderror"
                        value="{{ old('subject') }}"
                        placeholder="e.g. Settlement not received for last week"
                        required
                    >
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="priority">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        @foreach (config('restaurant_help.ticket_priorities', []) as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="message">Message</label>
                    <textarea
                        name="message"
                        id="message"
                        rows="4"
                        class="form-control @error('message') is-invalid @enderror"
                        placeholder="Include order numbers, dates, and any screenshots if relevant."
                        required
                    >{{ old('message') }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-brand">Submit ticket</button>
                </div>
            </div>
        </form>
    </div>
</div>

<x-panel-table title="Your tickets" :paginator="$tickets">
    <thead>
        <tr>
            <th>Ticket</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Updated</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tickets as $ticket)
            <tr>
                <td class="text-nowrap"><code class="small">{{ $ticket->ticket_number }}</code></td>
                <td>{{ $ticket->subject }}</td>
                <td><span class="badge text-bg-{{ $ticket->statusBadgeClass() }}">{{ $ticket->statusLabel() }}</span></td>
                <td class="text-capitalize small">{{ $ticket->priority }}</td>
                <td class="small text-muted">{{ $ticket->updated_at->diffForHumans() }}</td>
                <td class="text-end">
                    <a href="{{ route('restaurant.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Open</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No tickets yet. Submit the form above to get help.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
