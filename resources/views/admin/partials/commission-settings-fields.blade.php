@php
    $masterCommission = $masterCommission ?? 10;
    $activeCommission = $activeCommission ?? null;
    $commissionScopeLabel = $commissionScopeLabel ?? 'entity';
    $useMaster = old('commission_use_master', $activeCommission ? '0' : '1');
    $commissionPct = old('commission_percentage', $activeCommission?->percentage);
@endphp

<hr class="form-section-divider">
<p class="form-section-title">Platform commission</p>
<p class="small text-muted mb-3">
    Jab tak profile par custom commission set nahi hai, orders par <strong>Finance → Commission</strong> wala default
    (<strong>{{ $masterCommission }}%</strong>) lagega. Profile se custom save karne par sirf wahi rate use hoga.
    @if ($commissionScopeLabel === 'restaurant')
        Food subtotal par platform share.
    @else
        Delivery fee par platform share.
    @endif
    <a href="{{ route('admin.commissions.index') }}" class="ms-1">Default settings</a>
</p>

<div class="row g-3">
    <div class="col-12">
        <div class="form-check">
            <input
                class="form-check-input"
                type="radio"
                name="commission_use_master"
                id="commission-use-master-{{ $commissionScopeLabel }}"
                value="1"
                @checked((string) $useMaster === '1')
            >
            <label class="form-check-label" for="commission-use-master-{{ $commissionScopeLabel }}">
                Use master default ({{ $masterCommission }}%)
            </label>
        </div>
        <div class="form-check">
            <input
                class="form-check-input"
                type="radio"
                name="commission_use_master"
                id="commission-use-custom-{{ $commissionScopeLabel }}"
                value="0"
                @checked((string) $useMaster === '0')
            >
            <label class="form-check-label" for="commission-use-custom-{{ $commissionScopeLabel }}">
                Custom commission for this {{ $commissionScopeLabel }}
            </label>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="commission-percentage-{{ $commissionScopeLabel }}">Custom %</label>
        <div class="input-group">
            <input
                type="number"
                step="0.01"
                min="0"
                max="100"
                name="commission_percentage"
                id="commission-percentage-{{ $commissionScopeLabel }}"
                class="form-control"
                value="{{ $commissionPct }}"
                placeholder="{{ $masterCommission }}"
            >
            <span class="input-group-text">%</span>
        </div>
        <div class="form-text">Required when custom commission is selected.</div>
    </div>
</div>
