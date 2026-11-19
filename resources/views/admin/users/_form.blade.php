<x-panel-form-card :title="$user ? 'Edit user' : 'User details'" subtitle="Profile and account access">
    <form method="POST" action="{{ $action }}" class="panel-form">
        @csrf
        @if (! empty($method))
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field-name">Full name</label>
                <input
                    type="text"
                    name="name"
                    id="field-name"
                    class="form-control"
                    value="{{ old('name', $user?->name) }}"
                    required
                >
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-email">Email</label>
                <input
                    type="email"
                    name="email"
                    id="field-email"
                    class="form-control"
                    value="{{ old('email', $user?->email) }}"
                    required
                >
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-phone">Phone</label>
                <input
                    type="tel"
                    name="phone"
                    id="field-phone"
                    class="form-control"
                    value="{{ old('phone', $user?->phone) }}"
                    placeholder="Optional"
                >
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-password">
                    Password
                    @if ($user)
                        <span class="text-muted fw-normal">(leave blank to keep current)</span>
                    @endif
                </label>
                <input
                    type="password"
                    name="password"
                    id="field-password"
                    class="form-control"
                    autocomplete="new-password"
                    @if (! $user) required @endif
                >
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        name="is_verified"
                        id="field-is-verified"
                        value="1"
                        @checked(old('is_verified', $user?->is_verified))
                    >
                    <label class="form-check-label" for="field-is-verified">Mark account as verified</label>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-brand">{{ $user ? 'Save changes' : 'Create user' }}</button>
            <a href="{{ $user ? route('admin.users.show', $user) : route('admin.users.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
