@php
    $loginMethods = $loginMethods ?? ['email'];
    $defaultLoginMethod = $defaultLoginMethod ?? 'email';
    $activeMethod = old('login_method', $defaultLoginMethod);
    if (! in_array($activeMethod, $loginMethods, true)) {
        $activeMethod = $loginMethods[0];
    }
    $showMethodSwitch = count($loginMethods) > 1;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Restaurant Login — {{ site_name() }}</title>
    @include('layouts.partials.site-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/auth-login.css') }}" rel="stylesheet">
</head>
<body class="auth-page auth-page--login">
<div class="auth-shell">
    <aside class="auth-brand">
        <img
            src="{{ site_logo_url() }}"
            alt="{{ site_name() }}"
            class="auth-logo"
            width="280"
            height="auto"
        >
        <p class="auth-tagline">{{ site_setting('site_tagline') ?: 'Manage your menu, orders, and earnings from one partner dashboard.' }}</p>
    </aside>

    <div class="auth-form-panel">
        <header class="auth-page-header">
            <span class="auth-eyebrow"><i class="bi bi-shop-window" aria-hidden="true"></i> Partner portal</span>
            <h1>Welcome back</h1>
            <p class="subtitle">Sign in to your restaurant account</p>
        </header>

        @if (session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('restaurant.login.submit') }}" class="auth-form" id="restaurant-login-form">
            @csrf
            <input type="hidden" name="login_method" id="login_method" value="{{ $activeMethod }}">

            @if ($showMethodSwitch)
                <div class="auth-login-methods mb-3" role="tablist" aria-label="Sign in method">
                    @if (in_array('email', $loginMethods, true))
                        <button
                            type="button"
                            class="auth-login-method-btn @if($activeMethod === 'email') is-active @endif"
                            data-login-method="email"
                            role="tab"
                            aria-selected="{{ $activeMethod === 'email' ? 'true' : 'false' }}"
                        >
                            <i class="bi bi-envelope" aria-hidden="true"></i> Email
                        </button>
                    @endif
                    @if (in_array('phone', $loginMethods, true))
                        <button
                            type="button"
                            class="auth-login-method-btn @if($activeMethod === 'phone') is-active @endif"
                            data-login-method="phone"
                            role="tab"
                            aria-selected="{{ $activeMethod === 'phone' ? 'true' : 'false' }}"
                        >
                            <i class="bi bi-phone" aria-hidden="true"></i> Phone
                        </button>
                    @endif
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label" for="identifier" id="identifier-label">
                    @if ($activeMethod === 'phone')
                        Mobile number
                    @else
                        Email address
                    @endif
                </label>
                <div class="auth-input-wrap">
                    <i class="bi @if($activeMethod === 'phone') bi-phone @else bi-envelope @endif auth-input-icon" id="identifier-icon" aria-hidden="true"></i>
                    <input
                        type="@if($activeMethod === 'phone') tel @else email @endif"
                        name="identifier"
                        id="identifier"
                        class="form-control"
                        value="{{ old('identifier') }}"
                        placeholder="@if($activeMethod === 'phone') 98765 43210 @else demo@restaurant.test @endif"
                        autocomplete="@if($activeMethod === 'phone') tel @else email @endif"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="auth-input-wrap auth-input-wrap--password">
                    <i class="bi bi-lock auth-input-icon" aria-hidden="true"></i>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control js-password-field"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="auth-password-toggle"
                        aria-label="Show password"
                        aria-pressed="false"
                        data-password-toggle
                    >
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn btn-oniono w-100">Sign in to Restaurant</button>
        </form>

        <div class="auth-footer-divider">
            <a href="{{ route('restaurant.register') }}" class="auth-secondary-link">
                Register your restaurant <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
            <p class="auth-footer-note mb-0">Oniono Multi-Vendor Food Delivery · Partner access</p>
        </div>
    </div>
</div>
@include('restaurant.auth.partials.password-toggle-script')
@if ($showMethodSwitch)
<script>
    (function () {
        var form = document.getElementById('restaurant-login-form');
        var methodInput = document.getElementById('login_method');
        var identifier = document.getElementById('identifier');
        var identifierLabel = document.getElementById('identifier-label');
        var identifierIcon = document.getElementById('identifier-icon');

        function setMethod(method) {
            methodInput.value = method;
            document.querySelectorAll('.auth-login-method-btn').forEach(function (btn) {
                var active = btn.getAttribute('data-login-method') === method;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            if (method === 'phone') {
                identifierLabel.textContent = 'Mobile number';
                identifier.type = 'tel';
                identifier.placeholder = '98765 43210';
                identifier.autocomplete = 'tel';
                identifierIcon.className = 'bi bi-phone auth-input-icon';
            } else {
                identifierLabel.textContent = 'Email address';
                identifier.type = 'email';
                identifier.placeholder = 'demo@restaurant.test';
                identifier.autocomplete = 'email';
                identifierIcon.className = 'bi bi-envelope auth-input-icon';
            }
        }

        document.querySelectorAll('[data-login-method]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setMethod(btn.getAttribute('data-login-method'));
                identifier.focus();
            });
        });
    })();
</script>
@endif
</body>
</html>
