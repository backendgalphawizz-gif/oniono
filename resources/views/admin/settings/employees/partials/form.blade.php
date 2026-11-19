@php
    use App\Services\AdminGeoScopeService;
    use App\Services\AdminGeoScopeSyncService;

    $isEdit = isset($admin) && $admin;
    $geoService = app(AdminGeoScopeService::class);
    $stateOptions = $stateOptions ?? $geoService->stateOptions();
    $citiesByState = $citiesByState ?? $geoService->citiesByState();
    $geoScopeRows = $geoScopeRows ?? ($isEdit ? app(AdminGeoScopeSyncService::class)->rowsForAdmin($admin) : []);
@endphp

<x-panel-form-card
    :title="$isEdit ? $admin->name : 'Add staff member'"
    :subtitle="$isEdit ? 'Update sign-in details and what this person can access in the admin panel.' : 'Employees sign in with Admin ID or email. Permissions apply immediately after save.'"
    class="staff-form-card"
>
    @if ($isEdit)
        <div class="staff-form-hero mb-4">
            <div class="staff-form-avatar" aria-hidden="true">{{ strtoupper(mb_substr($admin->name, 0, 1)) }}</div>
            <div class="staff-form-hero-body min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <code class="staff-form-login-id">{{ $admin->login_id }}</code>
                    @if ($admin->is_active)
                        <span class="status-badge status-delivered">Active</span>
                    @else
                        <span class="status-badge status-default">Inactive</span>
                    @endif
                </div>
                <span class="small text-muted text-truncate d-block">{{ $admin->email }}</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="panel-form staff-form">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <p class="form-section-title">Profile</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Full name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $admin->name ?? '') }}" required autocomplete="name">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" class="form-control" value="{{ old('phone', $admin->phone ?? '') }}" placeholder="Optional" autocomplete="tel">
            </div>
        </div>

        <hr class="form-section-divider">

        <p class="form-section-title">Sign-in credentials</p>
        <p class="small text-muted mb-3">Used on the admin login page with either Admin ID or email.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="login_id">Admin ID</label>
                <input type="text" name="login_id" id="login_id" class="form-control font-monospace" value="{{ old('login_id', $admin->login_id ?? '') }}" required autocomplete="off" spellcheck="false">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $admin->email ?? '') }}" required autocomplete="email">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">
                    Password
                    @if ($isEdit)
                        <span class="text-muted fw-normal">(optional)</span>
                    @endif
                </label>
                <div class="panel-password-wrap">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        {{ $isEdit ? '' : 'required' }}
                        autocomplete="new-password"
                    >
                    <button type="button" class="panel-password-toggle" aria-label="Show password" data-password-toggle aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @if ($isEdit)
                    <div class="form-text">Leave blank to keep the current password.</div>
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirm password</label>
                <div class="panel-password-wrap">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="form-control"
                        autocomplete="new-password"
                    >
                    <button type="button" class="panel-password-toggle" aria-label="Show password" data-password-toggle aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <hr class="form-section-divider">

        <div class="staff-status-card">
            <div class="staff-status-card-copy">
                <span class="staff-status-card-title">Account access</span>
                <p class="small text-muted mb-0">Inactive accounts cannot sign in until you activate them again.</p>
            </div>
            <div class="form-check form-switch staff-status-switch mb-0">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    name="is_active"
                    id="is_active"
                    value="1"
                    @checked(old('is_active', $admin->is_active ?? true))
                >
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>

        <hr class="form-section-divider">

        @include('admin.settings.employees.partials.geo-scope', [
            'admin' => $admin,
            'stateOptions' => $stateOptions,
            'citiesByState' => $citiesByState,
            'geoScopeRows' => $geoScopeRows,
        ])

        <hr class="form-section-divider">

        @include('admin.settings.employees.partials.permission-matrix', ['permissionMatrix' => $permissionMatrix])

        <div class="form-actions staff-form-actions">
            <button type="submit" class="btn btn-brand">
                <i class="bi bi-check-lg me-1"></i>{{ $isEdit ? 'Save changes' : 'Create staff member' }}
            </button>
            <a href="{{ route('admin.settings.employees.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>

@push('scripts')
    @include('admin.partials.panel-password-toggle-script')
@endpush
