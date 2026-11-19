@extends('layouts.admin')
@section('page-title', 'Delivery Partner Management')
@section('content')
<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-search">Search</label>
            <input type="search" name="search" id="filter-search" class="form-control" placeholder="Name, phone, vehicle…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-registration">Registration</label>
            <select name="registration" id="filter-registration" class="form-select">
                <option value="">All</option>
                @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('registration') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2 filter-field">
            <label class="form-label" for="filter-account">Account</label>
            <select name="account" id="filter-account" class="form-select">
                <option value="">All</option>
                @foreach (['active' => 'Active', 'blocked' => 'Blocked', 'suspended' => 'Suspended'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('account') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </x-slot:filter>
    <x-slot:actions>
        @admincan('delivery_partners', 'edit')
            <a href="{{ route('admin.delivery-partners.create') }}" class="btn btn-brand">
                <i class="bi bi-plus-lg me-1"></i>Add partner
            </a>
        @endadmincan
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Delivery partners" :paginator="$partners">
    <thead>
        <tr>
            <th>Name</th>
            <th>Contact</th>
            <th>Vehicle</th>
            <th>Registration</th>
            <th>Account</th>
            <th>Deliveries</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($partners as $partner)
            @php
                $regClass = match ($partner->status) {
                    'approved' => 'status-delivered',
                    'pending' => 'status-pending',
                    'rejected' => 'status-cancelled',
                    default => 'status-default',
                };
                $accountClass = match (true) {
                    $partner->is_blocked, $partner->is_suspended => 'status-cancelled',
                    default => 'status-delivered',
                };
            @endphp
            <tr>
                <td class="fw-semibold">{{ $partner->name }}</td>
                <td>
                    <div>{{ $partner->email }}</div>
                    <div class="small text-muted">{{ $partner->phone }}</div>
                </td>
                <td>
                    {{ $partner->vehicle_type ?? '—' }}
                    @if ($partner->vehicle_number)
                        <div class="small text-muted">{{ $partner->vehicle_number }}</div>
                    @endif
                </td>
                <td><span class="status-badge {{ $regClass }}">{{ ucfirst($partner->status) }}</span></td>
                <td><span class="status-badge {{ $accountClass }}">{{ $partner->accountStatusLabel() }}</span></td>
                <td>{{ $partner->assignments_count }}</td>
                <td class="text-end">
                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                        @admincan('delivery_partners', 'view')
                            <a href="{{ route('admin.delivery-partners.show', $partner) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @endadmincan
                        @admincan('delivery_partners', 'edit')
                            <a href="{{ route('admin.delivery-partners.edit', $partner) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        @endadmincan
                        @admincan('delivery_partners', 'delete')
                            <form method="POST" action="{{ route('admin.delivery-partners.destroy', $partner) }}" class="d-inline" onsubmit="return confirm('Delete this delivery partner?');">
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
                <td colspan="7" class="text-center text-muted py-4">No delivery partners match your filters.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
