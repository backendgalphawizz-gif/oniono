<x-panel-form-card title="Platform settings" subtitle="Payments, commission, and catalog options. Branding is under Site settings.">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="panel-form">
        @csrf
        <p class="small text-muted">Restaurant &amp; delivery commission master rates are under <a href="{{ route('admin.commissions.index') }}">Finance → Commission</a>.</p>
        <div class="row g-3">
            <div class="col-12">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="cod_enabled" id="cod_enabled" value="1" @checked(\App\Models\Setting::getValue('cod_enabled', true))>
                    <label class="form-check-label" for="cod_enabled">COD enabled</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="online_payment_enabled" id="online_payment_enabled" value="1" @checked(\App\Models\Setting::getValue('online_payment_enabled', true))>
                    <label class="form-check-label" for="online_payment_enabled">Online payments enabled</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="delivery_partner_reviews_enabled" id="delivery_partner_reviews_enabled" value="1" @checked(\App\Models\Setting::getValue('delivery_partner_reviews_enabled', false))>
                    <label class="form-check-label" for="delivery_partner_reviews_enabled">Delivery partner reviews</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="admin_food_price_editing" id="admin_food_price_editing" value="1" @checked(\App\Models\Setting::getValue('admin_food_price_editing', true))>
                    <label class="form-check-label" for="admin_food_price_editing">Allow admin to edit food prices</label>
                </div>
            </div>
        </div>

        <div class="mt-4">
            @admincan('settings_general', 'edit')
                <button type="submit" class="btn btn-brand">Save settings</button>
            @endadmincan
        </div>
    </form>
</x-panel-form-card>
