@extends('layouts.admin')
@section('page-title', 'Support')
@section('content')
<x-panel-table title="Support tickets" :paginator="$tickets">
    <thead>
        <tr>
            <th>Ticket</th>
            <th>Subject</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tickets as $ticket)
            <tr>
                <td class="fw-semibold">{{ $ticket->ticket_number }}</td>
                <td>{{ $ticket->subject }}</td>
                <td><span class="status-badge status-default">{{ $ticket->status }}</span></td>
                <td class="text-end">
                    <a href="{{ route('admin.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Open</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-4">No support tickets.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
