<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant') — {{ site_name() }}</title>
    @include('layouts.partials.site-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/oniono-panel.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="panel-body restaurant-panel">
<div class="panel-shell d-flex">
    <aside class="panel-sidebar d-none d-lg-block">
        @include('layouts.partials.restaurant-nav')
    </aside>

    <div class="offcanvas offcanvas-start panel-sidebar-offcanvas d-lg-none" tabindex="-1" id="restaurantSidebar" aria-labelledby="restaurantSidebarLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="restaurantSidebarLabel">Oniono Restaurant</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @include('layouts.partials.restaurant-nav')
        </div>
    </div>

    <div class="panel-main flex-grow-1">
        <header class="panel-topbar d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button
                    type="button"
                    class="btn btn-light border topbar-menu-btn d-lg-none flex-shrink-0"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#restaurantSidebar"
                    aria-controls="restaurantSidebar"
                >
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="panel-topbar-title text-truncate">@yield('page-title', 'Dashboard')</h1>
                    <div class="panel-topbar-sub">Oniono Restaurant · Manage your store</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @php
                    $restaurantUnreadNotifications = auth('restaurant')->check()
                        ? \App\Models\PanelNotification::query()->forRestaurant(restaurant_id())->unread()->count()
                        : 0;
                @endphp
                <a href="{{ route('restaurant.notifications.index') }}" class="topbar-icon-btn position-relative" title="Notifications">
                    <i class="bi bi-bell"></i>
                    @if ($restaurantUnreadNotifications > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ $restaurantUnreadNotifications > 9 ? '9+' : $restaurantUnreadNotifications }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('restaurant.settings.edit') }}" class="topbar-icon-btn d-none d-md-inline-flex" title="Settings">
                    <i class="bi bi-sliders"></i>
                </a>
            </div>
        </header>
        <main class="page-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@include('layouts.partials.confirm-dialog')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/oniono-confirm.js') }}"></script>
<script>
    document.querySelectorAll('.nav-group .collapse').forEach(function (el) {
        el.addEventListener('show.bs.collapse', function () {
            el.closest('.nav-group')?.classList.add('is-open');
        });
        el.addEventListener('hide.bs.collapse', function () {
            el.closest('.nav-group')?.classList.remove('is-open');
        });
    });
</script>
@stack('scripts')
</body>
</html>
