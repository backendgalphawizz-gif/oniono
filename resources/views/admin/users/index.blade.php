@extends('layouts.admin')
@section('page-title', 'User Management')
@section('content')
<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input
                type="search"
                name="search"
                id="filter-search"
                class="form-control"
                placeholder="Name, email, phone…"
                value="{{ request('search') }}"
            >
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-status">Account status</label>
            <select name="status" id="filter-status" class="form-select">
                <option value="">All</option>
                @foreach (['active' => 'Active', 'blocked' => 'Blocked', 'suspended' => 'Suspended'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-verification">Verification</label>
            <select name="verification" id="filter-verification" class="form-select">
                <option value="">All</option>
                @foreach (['verified' => 'Verified', 'unverified' => 'Unverified'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('verification') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </x-slot:filter>
    <x-slot:actions>
        @admincan('users', 'edit')
            <a href="{{ route('admin.users.create') }}" class="btn btn-brand">
                <i class="bi bi-plus-lg me-1"></i>Add user
            </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Users" :paginator="$users">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Orders</th>
            <th>Status</th>
            <th>Verified</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $user)
            @php
                $statusClass = match (true) {
                    $user->is_blocked => 'status-cancelled',
                    $user->is_suspended => 'status-pending',
                    default => 'status-delivered',
                };
            @endphp
            <tr>
                <td class="fw-semibold">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone ?? '—' }}</td>
                <td>{{ $user->orders_count }}</td>
                <td><span class="status-badge {{ $statusClass }}">{{ $user->accountStatusLabel() }}</span></td>
                <td>
                    @if ($user->is_verified)
                        <span class="status-badge status-delivered">Yes</span>
                    @else
                        <span class="status-badge status-default">No</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                        @admincan('users', 'view')
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @endadmincan
                        @admincan('users', 'edit')
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        @endadmincan
                        @admincan('users', 'delete')
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        @endadmincan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">No users match your filters.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
