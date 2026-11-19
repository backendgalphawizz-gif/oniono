@php
    $masterCommission = $masterCommission ?? 10;
    $activeCommission = $activeCommission ?? null;
    $commissionScopeLabel = $commissionScopeLabel ?? 'entity';
    $commissionFormAction = $commissionFormAction ?? '#';
    $resolved = $commissionResolved ?? null;
    if (! $resolved && isset($restaurant)) {
        $resolved = app(\App\Services\CommissionService::class)->resolveRestaurantCommission($restaurant);
    }
    if (! $resolved && isset($deliveryPartner)) {
        $resolved = app(\App\Services\CommissionService::class)->resolveDeliveryPartnerCommission($deliveryPartner);
    }
    $effectiveRate = $resolved['percentage'] ?? ($activeCommission?->percentage ?? $masterCommission);
    $isCustom = ($resolved['source'] ?? ($activeCommission ? 'profile' : 'master')) === 'profile';
@endphp

<div class="card card-soft mb-3">
    <div class="card-header-brand d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="h6 mb-0">Platform commission</h3>
        @if ($isCustom)
            <span class="badge text-bg-warning">Profile {{ $effectiveRate }}%</span>
        @else
            <span class="badge text-bg-light border">Default {{ $effectiveRate }}%</span>
        @endif
    </div>
    <div class="card-body">
        <p class="small text-muted mb-0">
            Orders par abhi: <strong>{{ $effectiveRate }}%</strong>
            ({{ $isCustom ? 'profile se set' : 'default settings se' }})
            @if ($commissionScopeLabel === 'restaurant')
                · food subtotal.
            @else
                · delivery fee.
            @endif
        </p>

        <form method="POST" action="{{ $commissionFormAction }}" class="panel-form">
            @csrf
            @include('admin.partials.commission-settings-fields', [
                'masterCommission' => $masterCommission,
                'activeCommission' => $activeCommission,
                'commissionScopeLabel' => $commissionScopeLabel,
            ])
            <div class="mt-3">
                <button type="submit" class="btn btn-brand btn-sm">Save commission</button>
            </div>
        </form>
    </div>
</div>
