<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — {{ site_name() }}</title>
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
        <p class="auth-tagline">{{ site_setting('site_tagline') ?: 'Admin control center for restaurants, orders, and platform operations.' }}</p>
    </aside>

    <div class="auth-form-panel">
        <h1>Welcome back</h1>
        <p class="subtitle">Sign in with your admin ID or email and password</p>

        @if (session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="auth-form">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="login_id">Admin ID / Email ID</label>
                <div class="auth-input-wrap">
                    <i class="bi bi-person-badge auth-input-icon" aria-hidden="true"></i>
                    <input
                        type="text"
                        name="login_id"
                        id="login_id"
                        class="form-control"
                        value="{{ old('login_id') }}"
                        placeholder="admin or you@example.com"
                        autocomplete="username"
                        autocapitalize="off"
                        spellcheck="false"
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
                        class="form-control"
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

            <button type="submit" class="btn btn-oniono w-100">Sign in to Admin</button>
        </form>

        <p class="auth-footer-note mb-0">Oniono Multi-Vendor Food Delivery · Secure admin access</p>
    </div>
</div>
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.auth-input-wrap').querySelector('#password');
            var icon = btn.querySelector('i');
            var visible = input.type === 'text';

            input.type = visible ? 'password' : 'text';
            icon.classList.toggle('bi-eye', visible);
            icon.classList.toggle('bi-eye-slash', !visible);
            btn.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            btn.setAttribute('aria-pressed', visible ? 'false' : 'true');
        });
    });
</script>
</body>
</html>
