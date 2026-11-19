<x-panel-form-card :title="$partner ? 'Edit delivery partner' : 'Partner profile'" subtitle="Contact, vehicle, and access">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @if (! empty($method))
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field-name">Full name</label>
                <input type="text" name="name" id="field-name" class="form-control" value="{{ old('name', $partner?->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-email">Email</label>
                <input type="email" name="email" id="field-email" class="form-control" value="{{ old('email', $partner?->email) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-phone">Phone</label>
                <input type="tel" name="phone" id="field-phone" class="form-control" value="{{ old('phone', $partner?->phone) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-password">
                    Password
                    @if ($partner)
                        <span class="text-muted fw-normal">(leave blank to keep current)</span>
                    @endif
                </label>
                <input type="password" name="password" id="field-password" class="form-control" autocomplete="new-password" @if (! $partner) required @endif>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-vehicle-type">Vehicle type</label>
                <input type="text" name="vehicle_type" id="field-vehicle-type" class="form-control" value="{{ old('vehicle_type', $partner?->vehicle_type) }}" placeholder="Bike, scooter…">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-vehicle-number">Vehicle number</label>
                <input type="text" name="vehicle_number" id="field-vehicle-number" class="form-control" value="{{ old('vehicle_number', $partner?->vehicle_number) }}">
            </div>

            @if ($partner)
                @php($partner->loadMissing('documents'))
            @endif

            @include('partials.delivery-partner-document-uploads', [
                'partner' => $partner,
                'requireAll' => ! $partner,
            ])

            @if ($partner)
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_available" id="field-available" value="1" @checked(old('is_available', $partner->is_available))>
                        <label class="form-check-label" for="field-available">Available for deliveries</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="field-active" value="1" @checked(old('is_active', $partner->is_active))>
                        <label class="form-check-label" for="field-active">Account active</label>
                    </div>
                </div>
            @endif
        </div>

        @if ($partner)
            @php($partner->loadMissing('activeCommission'))
            @include('admin.partials.commission-settings-fields', [
                'masterCommission' => $masterCommission ?? \App\Models\Setting::getValue('default_delivery_partner_commission_percentage', 15),
                'activeCommission' => $partner->activeCommission,
                'commissionScopeLabel' => 'partner',
            ])
        @endif

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-brand">{{ $partner ? 'Save changes' : 'Create partner' }}</button>
            <a href="{{ $partner ? route('admin.delivery-partners.show', $partner) : route('admin.delivery-partners.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
