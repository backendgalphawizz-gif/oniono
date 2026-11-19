@php
    /** @var \App\Services\RestaurantAuthConfig $authConfig */
    use App\Support\PhoneNumber;
    $draft = $draft ?? [];
    $field = fn (string $key) => old($key, $draft[$key] ?? '');
    $phoneDisplay = old('phone', isset($draft['phone']) ? PhoneNumber::display($draft['phone']) : '');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Restaurant Registration — {{ site_name() }}</title>
    @include('layouts.partials.site-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/auth-login.css') }}" rel="stylesheet">
</head>
<body class="auth-page auth-page--register">
<div class="auth-shell auth-shell--register">
    <aside class="auth-brand">
        <img src="{{ site_logo_url() }}" alt="{{ site_name() }}" class="auth-logo" width="240" height="auto">
        <p class="auth-tagline">Partner signup — business profile &amp; login (step 1 of 2).</p>
    </aside>

    <div class="auth-form-panel auth-form-panel--scroll">
        <header class="auth-page-header">
            <h1>Register your restaurant</h1>
            <p class="subtitle">Step 1 of 2 — Business &amp; contact</p>
        </header>

        @include('restaurant.auth.partials.register-step-indicator', ['current' => 1])

        @if (session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('restaurant.register.step1') }}" enctype="multipart/form-data" class="auth-form" id="restaurant-register-step1-form">
            @csrf

            <section class="auth-form-section" aria-labelledby="register-section-business">
            <h2 class="auth-section-title" id="register-section-business">Business &amp; contact</h2>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="name">Restaurant name</label>
                    <input name="name" id="name" class="form-control" value="{{ $field('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="owner_name">Owner / contact name</label>
                    <input name="owner_name" id="owner_name" class="form-control" value="{{ $field('owner_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ $field('email') }}" autocomplete="email" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="phone">Mobile number</label>
                    <div class="auth-phone-send-row">
                        <input type="tel" name="phone" id="phone" class="form-control" value="{{ $phoneDisplay }}" autocomplete="tel" inputmode="numeric" pattern="[0-9+\s-]{10,15}" placeholder="10-digit mobile" required>
                        <button type="button" class="btn btn-outline-secondary btn-sm text-nowrap" id="btn-send-otp">Send OTP</button>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium small text-secondary" for="otp">OTP verification</label>
                    <div class="auth-phone-send-row auth-phone-send-row--otp">
                        <input name="otp" id="otp" class="form-control" inputmode="numeric" maxlength="{{ $authConfig->otpLength() }}" placeholder="{{ str_repeat('•', $authConfig->otpLength()) }}" autocomplete="one-time-code">
                        <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" id="btn-verify-otp">Verify</button>
                    </div>
                    <p class="small mb-0 mt-2" id="otp-status" aria-live="polite"></p>
                </div>
            </div>
            </section>

            <section class="auth-form-section" aria-labelledby="register-section-password">
            <h2 class="auth-section-title mt-4" id="register-section-password">Account password</h2>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="password">Password</label>
                    <div class="auth-input-wrap auth-input-wrap--password">
                        <i class="bi bi-lock auth-input-icon" aria-hidden="true"></i>
                        <input type="password" name="password" id="password" class="form-control js-password-field" required autocomplete="new-password">
                        <button type="button" class="auth-password-toggle" aria-label="Show password" data-password-toggle>
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="password_confirmation">Confirm password</label>
                    <div class="auth-input-wrap auth-input-wrap--password">
                        <i class="bi bi-lock auth-input-icon" aria-hidden="true"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control js-password-field" required autocomplete="new-password">
                        <button type="button" class="auth-password-toggle" aria-label="Show password" data-password-toggle>
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            </section>

            <section class="auth-form-section" aria-labelledby="register-section-location">
            <h2 class="auth-section-title mt-4" id="register-section-location">Location</h2>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label fw-medium small text-secondary" for="address">Address</label>
                    <input name="address" id="address" class="form-control" value="{{ $field('address') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium small text-secondary" for="state_id">State</label>
                    <select name="state_id" id="state_id" class="form-select" required data-register-state-select>
                        <option value="">Select state…</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->id }}" @selected((string) $selectedStateId === (string) $state->id)>{{ $state->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium small text-secondary" for="city_id">City</label>
                    <select
                        name="city_id"
                        id="city_id"
                        class="form-select"
                        required
                        data-register-city-select
                        data-cities-url="{{ route('restaurant.register.locations.cities') }}"
                        @disabled(! $selectedStateId)
                    >
                        <option value="">@if ($selectedStateId) Select city… @else Select state first @endif</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) $selectedCityId === (string) $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium small text-secondary" for="pincode">Pincode</label>
                    <input name="pincode" id="pincode" class="form-control" value="{{ $field('pincode') }}" inputmode="numeric" pattern="\d{6}" maxlength="6" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium small text-secondary" for="description">About your restaurant</label>
                    <textarea name="description" id="description" class="form-control" rows="2">{{ $field('description') }}</textarea>
                </div>
            </div>
            </section>

            <section class="auth-form-section" aria-labelledby="register-section-media">
            <h2 class="auth-section-title mt-4" id="register-section-media">Logo &amp; photos</h2>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="logo">Restaurant logo</label>
                    <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                    @if (! empty($draft['logo_path']))
                        <div class="form-text">Logo saved from your last step 1 visit. Upload again to replace.</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium small text-secondary" for="gallery_images">Gallery images</label>
                    <input type="file" name="gallery_images[]" id="gallery_images" class="form-control" accept="image/*" multiple>
                    <div class="form-text">Up to {{ $authConfig->galleryMaxImages() }} images ({{ number_format($authConfig->galleryMaxKb() / 1024, 1) }} MB each).</div>
                </div>
            </div>
            </section>

            <button type="submit" class="btn btn-oniono w-100 mt-2">Continue to payout &amp; documents</button>
        </form>

        <div class="auth-footer-divider">
            <a href="{{ route('restaurant.login') }}" class="auth-secondary-link">
                Already registered? Sign in <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
            <p class="auth-footer-note mb-0">Your application will be reviewed by our team.</p>
        </div>
    </div>
</div>
@include('restaurant.auth.partials.password-toggle-script')
@include('restaurant.auth.partials.register-state-city-script')
@include('restaurant.auth.partials.register-otp-script', ['phoneVerified' => $phoneVerified ?? false])
</body>
</html>
