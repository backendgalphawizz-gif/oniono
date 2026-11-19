@php
    $draft = $draft ?? [];
    $field = fn (string $key) => old($key, $draft[$key] ?? '');
@endphp

<h2 class="auth-section-title">Payout details</h2>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-medium small text-secondary" for="bank_account_name">Account holder name</label>
        <input name="bank_account_name" id="bank_account_name" class="form-control" value="{{ $field('bank_account_name') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium small text-secondary" for="bank_name">Bank name</label>
        <input name="bank_name" id="bank_name" class="form-control" value="{{ $field('bank_name') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium small text-secondary" for="bank_account_number">Account number</label>
        <input name="bank_account_number" id="bank_account_number" class="form-control" value="{{ $field('bank_account_number') }}" inputmode="numeric" pattern="\d{9,18}" maxlength="18" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium small text-secondary" for="bank_ifsc">IFSC code</label>
        <input name="bank_ifsc" id="bank_ifsc" class="form-control" value="{{ $field('bank_ifsc') }}" pattern="[A-Za-z]{4}0[A-Za-z0-9]{6}" maxlength="11" style="text-transform: uppercase" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium small text-secondary" for="gst_number">GST number (optional)</label>
        <input name="gst_number" id="gst_number" class="form-control" value="{{ $field('gst_number') }}">
    </div>
</div>
