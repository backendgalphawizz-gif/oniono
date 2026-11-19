<x-panel-form-card :title="$restaurant ? 'Edit restaurant' : 'Restaurant details'" subtitle="Basic profile and contact information">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @if (! empty($method))
            @method($method)
        @endif

        <div class="row g-3">
            @foreach ([
                ['name', 'Restaurant name'],
                ['owner_name', 'Owner name'],
                ['email', 'Email', 'email'],
                ['phone', 'Phone', 'tel'],
                ['password', 'Password', 'password', empty($restaurant)],
                ['address', 'Address'],
                ['city', 'City'],
                ['state', 'State'],
                ['pincode', 'Pincode'],
            ] as $field)
                @php
                    $name = $field[0];
                    $label = $field[1];
                    $type = $field[2] ?? 'text';
                    $show = $field[3] ?? true;
                @endphp
                @if ($show)
                    <div class="col-md-6">
                        <label class="form-label" for="field-{{ $name }}">{{ $label }}</label>
                        <input
                            type="{{ $type }}"
                            name="{{ $name }}"
                            id="field-{{ $name }}"
                            class="form-control"
                            value="{{ old($name, $restaurant?->$name) }}"
                            @if ($name !== 'password') required @endif
                        >
                    </div>
                @endif
            @endforeach

            <div class="col-12">
                <label class="form-label" for="field-description">Description</label>
                <textarea name="description" id="field-description" class="form-control" rows="3">{{ old('description', $restaurant?->description) }}</textarea>
            </div>
        </div>

        <hr class="form-section-divider">

        <p class="form-section-title">GST (restaurant default for menu items)</p>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="field-gst_number">GSTIN</label>
                <input name="gst_number" id="field-gst_number" class="form-control" value="{{ old('gst_number', $restaurant?->gst_number) }}" placeholder="22AAAAA0000A1Z5">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-default_gst_percentage">Default GST %</label>
                <input type="number" step="0.01" min="0" max="100" name="default_gst_percentage" id="field-default_gst_percentage" class="form-control" value="{{ old('default_gst_percentage', $restaurant?->default_gst_percentage) }}">
                <div class="form-text">Used for items marked “with GST” when item has no own rate.</div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="gst_enabled" id="field-gst_enabled" value="1" @checked(old('gst_enabled', $restaurant?->gst_enabled ?? false))>
                    <label class="form-check-label" for="field-gst_enabled">GST billing enabled for this restaurant</label>
                </div>
            </div>
        </div>

        @if ($restaurant)
            @include('admin.partials.commission-settings-fields', [
                'masterCommission' => $masterCommission ?? \App\Models\Setting::getValue('default_commission_percentage', 10),
                'activeCommission' => $restaurant->activeCommission,
                'commissionScopeLabel' => 'restaurant',
            ])
        @endif

        <hr class="form-section-divider">

        <p class="form-section-title">Branding &amp; banking</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field-logo">Logo</label>
                <input type="file" name="logo" id="field-logo" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-bank_account_name">Bank account name</label>
                <input name="bank_account_name" id="field-bank_account_name" class="form-control" value="{{ old('bank_account_name', $restaurant?->bank_account_name) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-bank_account_number">Account number</label>
                <input name="bank_account_number" id="field-bank_account_number" class="form-control" value="{{ old('bank_account_number', $restaurant?->bank_account_number) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-bank_ifsc">IFSC</label>
                <input name="bank_ifsc" id="field-bank_ifsc" class="form-control" value="{{ old('bank_ifsc', $restaurant?->bank_ifsc) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-bank_name">Bank name</label>
                <input name="bank_name" id="field-bank_name" class="form-control" value="{{ old('bank_name', $restaurant?->bank_name) }}">
            </div>
        </div>

        @if ($restaurant)
            <hr class="form-section-divider">
            <p class="form-section-title">Update documents</p>
            <div class="row g-3">
                @include('partials.restaurant-document-uploads', [
                    'restaurant' => $restaurant,
                    'requireAll' => false,
                ])
            </div>
        @endif

        <div class="form-actions">
            <button type="submit" class="btn btn-brand">
                <i class="bi bi-check2-circle me-1"></i>Save restaurant
            </button>
            <a href="{{ route('admin.restaurants.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
