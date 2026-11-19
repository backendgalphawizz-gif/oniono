@php
    $draft = $draft ?? [];
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
        <p class="auth-tagline">Payout &amp; documents (step 2 of 2). PAN required.</p>
    </aside>

    <div class="auth-form-panel">
        <h1>Payout &amp; documents</h1>
        <p class="subtitle">Step 2 of 2 — Bank details &amp; verification files</p>

        @include('restaurant.auth.partials.register-step-indicator', ['current' => 2])

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('restaurant.register.submit') }}" enctype="multipart/form-data" class="auth-form" id="restaurant-register-step2-form">
            @csrf

            @include('restaurant.auth.partials.register-payout-fields', ['draft' => $draft])

            <div class="row g-3 mt-2">
                @include('partials.restaurant-document-uploads', ['requireAll' => true])
            </div>

            <button type="submit" class="btn btn-oniono w-100 mt-4">Submit registration</button>
        </form>

        <div class="d-flex flex-wrap gap-3 mt-3">
            <a href="{{ route('restaurant.register') }}" class="auth-secondary-link">Back to step 1</a>
            <a href="{{ route('restaurant.register.restart') }}" class="auth-secondary-link text-muted">Start over</a>
        </div>
        <a href="{{ route('restaurant.login') }}" class="auth-secondary-link d-block mt-2">Already registered? Sign in</a>
    </div>
</div>
</body>
</html>
