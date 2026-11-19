<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Oniono') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5 text-center">
        <h1 class="mb-3">{{ config('app.name', 'Oniono') }}</h1>
        <p class="lead text-muted mb-4">Multi-vendor food delivery platform</p>
        <a href="{{ route('admin.login') }}" class="btn btn-primary me-2">Admin login</a>
        <a href="{{ route('restaurant.login') }}" class="btn btn-outline-primary">Restaurant login</a>
    </div>
</body>
</html>
