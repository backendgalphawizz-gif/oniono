@extends('layouts.admin')
@section('page-title', 'Staff & permissions')
@section('content')
<x-panel-toolbar>
    <x-slot:actions>
        <a href="{{ route('admin.settings.employees.create') }}" class="btn btn-brand">
            <i class="bi bi-person-plus me-1"></i>Add staff member
        </a>
    </x-slot:actions>
</x-panel-toolbar>

<x-panel-table title="Admin staff" :paginator="$employees">
    <thead>
        <tr>
            <th>Name</th>
            <th>Admin ID</th>
            <th>Email</th>
            <th>Password</th>
            <th>State</th>
            <th>City</th>
            <th>Status</th>
            <th>Modules</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($employees as $employee)
            <tr>
                <td class="fw-semibold">{{ $employee->name }}</td>
                <td><code>{{ $employee->login_id }}</code></td>
                <td>{{ $employee->email }}</td>
                <td>
                    @if ($employee->password_plain)
                        <div class="table-password-field">
                            <span class="table-password-value font-monospace small" data-masked="••••••••">{{ '••••••••' }}</span>
                            <button
                                type="button"
                                class="btn btn-sm btn-link text-secondary p-0 table-password-toggle"
                                aria-label="Show password"
                                aria-pressed="false"
                                data-password="{{ $employee->password_plain }}"
                            >
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    @else
                        <span class="text-muted small" title="Set a new password when editing this account">Not stored</span>
                    @endif
                </td>
                <td class="small align-top" style="min-width: 120px">
                    @if ($employee->scope_all_regions)
                        <span class="text-muted">All regions</span>
                    @elseif ($employee->geoScopes->isEmpty())
                        <span class="text-muted">Not assigned</span>
                    @else
                        <table class="table table-sm table-borderless geo-scope-summary-table mb-0">
                            <tbody>
                                @foreach ($employee->geoScopes as $scope)
                                    <tr>
                                        <td class="ps-0 py-0">{{ $scope->state }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
                <td class="small align-top text-muted" style="min-width: 100px">
                    @if ($employee->scope_all_regions || $employee->geoScopes->isEmpty())
                        <span class="text-muted">—</span>
                    @else
                        <table class="table table-sm table-borderless geo-scope-summary-table mb-0">
                            <tbody>
                                @foreach ($employee->geoScopes as $scope)
                                    <tr>
                                        <td class="ps-0 py-0">{{ $scope->city ?: 'All cities' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
                <td>
                    @if ($employee->is_active)
                        <span class="status-badge status-delivered">Active</span>
                    @else
                        <span class="status-badge status-default">Inactive</span>
                    @endif
                </td>
                <td class="text-muted small">{{ $employee->permissions_count }} configured</td>
                <td class="text-end">
                    <a href="{{ route('admin.settings.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route('admin.settings.employees.toggle-active', $employee) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.settings.employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this staff member?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">No staff members yet. Add an employee and assign module permissions.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection

@push('scripts')
    @include('admin.partials.table-password-toggle-script')
@endpush
