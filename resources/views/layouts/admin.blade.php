<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — {{ site_name() }}</title>
    @include('layouts.partials.site-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="{{ asset('css/oniono-panel.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="panel-body admin-panel">
<div class="panel-shell d-flex">
    <aside class="panel-sidebar d-none d-lg-block">
        @include('layouts.partials.admin-nav')
    </aside>

    <div class="offcanvas offcanvas-start panel-sidebar-offcanvas d-lg-none" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="adminSidebarLabel">{{ site_name() }} Admin</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @include('layouts.partials.admin-nav')
        </div>
    </div>

    <div class="panel-main flex-grow-1">
        <header class="panel-topbar d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button
                    type="button"
                    class="btn btn-light border topbar-menu-btn d-lg-none flex-shrink-0"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#adminSidebar"
                    aria-controls="adminSidebar"
                >
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="panel-topbar-title text-truncate">@yield('page-title', 'Dashboard')</h1>
                    <div class="panel-topbar-sub">Oniono Admin · Multi-vendor food delivery</div>
                </div>
            </div>
            <form class="d-none d-lg-flex flex-grow-1 mx-2 search-pill" style="max-width:400px" action="{{ route('admin.orders.index') }}" method="GET">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control" placeholder="Search orders by ID..." value="{{ request('search') }}">
                </div>
            </form>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                @hasSection('back-url')
                    <a href="@yield('back-url')" class="btn btn-light border panel-back-btn">
                        <i class="bi bi-arrow-left"></i>
                        <span class="d-none d-md-inline">@yield('back-label', 'Back')</span>
                    </a>
                @else
                    @php($adminBack = admin_back_link())
                    @if ($adminBack)
                        <a href="{{ $adminBack['url'] }}" class="btn btn-light border panel-back-btn" title="{{ $adminBack['label'] }}">
                            <i class="bi bi-arrow-left"></i>
                            <span class="d-none d-md-inline">{{ $adminBack['label'] }}</span>
                        </a>
                    @endif
                @endif
                @admincan('notifications', 'view')
                    <a href="{{ route('admin.notifications.index') }}" class="topbar-icon-btn" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </a>
                @endadmincan
                @if (admin_can('settings_general', 'view') || admin_can('settings_locations', 'view') || admin_can('settings_site', 'view') || admin_can('settings_cms', 'view') || admin_can('settings_migrations', 'view') || auth('admin')->user()->isSuperAdmin())
                    <a href="{{ route('admin.settings.general') }}" class="topbar-icon-btn d-none d-md-inline-flex" title="Settings">
                        <i class="bi bi-sliders"></i>
                    </a>
                @endif
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
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
