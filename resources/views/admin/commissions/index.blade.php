@extends('layouts.admin')
@section('page-title', 'Commissions & GST')
@section('content')

<x-panel-form-card title="Master settings (admin)" subtitle="Default rates for all restaurants and delivery partners. Set custom commission on each restaurant or partner profile / edit page.">
    <form method="POST" action="{{ route('admin.commissions.global') }}" class="panel-form">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="restaurant-percent">Restaurant commission (platform share on food)</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="default_commission_percentage" id="restaurant-percent" class="form-control" value="{{ $globalRestaurantPercentage }}" required>
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="delivery-percent">Delivery partner commission (platform share on delivery fee)</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="default_delivery_partner_commission_percentage" id="delivery-percent" class="form-control" value="{{ $globalDeliveryPercentage }}" required>
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="gst-percent">Default GST on food (when applicable)</label>
                <div class="input-group">
                    <input type="number" step="0.01" name="default_gst_percentage" id="gst-percent" class="form-control" value="{{ $defaultGstPercentage }}" required>
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="commission_enabled" id="commission-enabled" value="1" @checked($commissionEnabled)>
                    <label class="form-check-label" for="commission-enabled">Commission calculations enabled</label>
                </div>
            </div>
            <div class="col-12">
                @admincan('commissions', 'edit')
                    <button type="submit" class="btn btn-brand">Save master settings</button>
                @endadmincan
            </div>
        </div>
    </form>
</x-panel-form-card>

<p class="small text-muted mb-0">
    Per-restaurant and per-partner commission: open
    <a href="{{ route('admin.restaurants.index') }}">Restaurant Management</a> or
    <a href="{{ route('admin.delivery-partners.index') }}">Delivery Partners</a> → profile or edit.
</p>

@endsection
