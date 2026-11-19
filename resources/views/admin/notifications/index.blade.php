@extends('layouts.admin')
@section('page-title', 'Notifications')
@section('content')
<x-panel-table title="Notifications" :paginator="$notifications">
    <thead><tr><th>Title</th><th>Message</th><th>Read</th><th></th></tr></thead>
    <tbody>
    @forelse($notifications as $n)
        <tr>
            <td>{{ $n->title }}</td>
            <td>{{ $n->body }}</td>
            <td>{{ $n->is_read ? 'Yes' : 'No' }}</td>
            <td>@unless($n->is_read)<form method="POST" action="{{ route('admin.notifications.read', $n) }}">@csrf<button class="btn btn-sm btn-outline-secondary">Mark read</button></form>@endunless</td>
        </tr>
    @empty
        <tr><td colspan="4">No notifications yet.</td></tr>
    @endforelse
    </tbody>
</x-panel-table>
@endsection
