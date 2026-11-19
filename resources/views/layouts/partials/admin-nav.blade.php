@php
    $isActive = function (string $route): bool {
        if ($route === 'admin.dashboard') {
            return request()->routeIs('admin.dashboard');
        }

        $prefix = str_replace('.index', '', $route);

        return request()->routeIs($prefix.'.*') || request()->routeIs($route);
    };

    $groupActive = function (array $routes) use ($isActive): bool {
        foreach ($routes as $route) {
            if ($isActive($route)) {
                return true;
            }
        }

        return false;
    };

    $canViewModule = fn (string $module): bool => admin_can($module, 'view');

    $filterNavItems = function (array $items) use ($canViewModule): array {
        return array_values(array_filter($items, function (array $link) use ($canViewModule): bool {
            return $canViewModule($link['module'] ?? '');
        }));
    };

    $menuGroups = [
        [
            'id' => 'operations',
            'label' => 'Operations',
            'icon' => 'boxes',
            'items' => [
                ['route' => 'admin.orders.index', 'icon' => 'bag-check', 'label' => 'Orders', 'module' => 'orders'],
                ['route' => 'admin.delivery-assignment.index', 'icon' => 'truck', 'label' => 'Delivery', 'module' => 'delivery_assignment'],
            ],
        ],
        [
            'id' => 'finance',
            'label' => 'Finance',
            'icon' => 'wallet2',
            'items' => [
                ['route' => 'admin.commissions.index', 'icon' => 'percent', 'label' => 'Commission', 'module' => 'commissions'],
                ['route' => 'admin.settlements.index', 'icon' => 'cash-stack', 'label' => 'Settlements', 'module' => 'settlements'],
                ['route' => 'admin.payments.index', 'icon' => 'credit-card', 'label' => 'Payments', 'module' => 'payments'],
            ],
        ],
        [
            'id' => 'growth',
            'label' => 'Marketing',
            'icon' => 'megaphone',
            'items' => [
                ['route' => 'admin.coupons.index', 'icon' => 'ticket-perforated', 'label' => 'Coupons', 'module' => 'coupons'],
                ['route' => 'admin.banners.index', 'icon' => 'images', 'label' => 'Banners', 'module' => 'banners'],
                ['route' => 'admin.notifications.index', 'icon' => 'chat-dots', 'label' => 'Messages', 'module' => 'notifications'],
            ],
        ],
        [
            'id' => 'people',
            'label' => 'Support',
            'icon' => 'life-preserver',
            'items' => [
                ['route' => 'admin.support.index', 'icon' => 'life-preserver', 'label' => 'Support tickets', 'module' => 'support'],
                ['route' => 'admin.reviews.index', 'icon' => 'star', 'label' => 'Reviews', 'module' => 'reviews'],
            ],
        ],
    ];

    $foodMenuSubItems = $filterNavItems([
        ['route' => 'admin.menu-management.index', 'icon' => 'grid', 'label' => 'Overview', 'module' => 'menu_management'],
        ['route' => 'admin.categories.index', 'icon' => 'folder2', 'label' => 'Categories', 'module' => 'categories'],
        ['route' => 'admin.sub-categories.index', 'icon' => 'folder2-open', 'label' => 'Sub-categories', 'module' => 'sub_categories'],
        ['route' => 'admin.cuisines.index', 'icon' => 'egg', 'label' => 'Cuisines', 'module' => 'cuisines'],
        ['route' => 'admin.food-items.index', 'icon' => 'list-ul', 'label' => 'Food listings', 'module' => 'food_items'],
        ['route' => 'admin.menu-management.featured', 'icon' => 'star', 'label' => 'Featured', 'module' => 'menu_management'],
    ]);

    $foodMenuRoutes = array_column($foodMenuSubItems, 'route');
    $foodMenuOpen = $groupActive($foodMenuRoutes);

    $reportsAnalyticsRouteNames = [
        'admin.reports-analytics.orders',
        'admin.reports-analytics.revenue',
        'admin.reports-analytics.user-activity',
        'admin.reports-analytics.restaurant-performance',
        'admin.reports-analytics.delivery-partner-performance',
        'admin.reports-analytics.popular',
        'admin.reports-analytics.order-trends',
    ];
    $reportsAnalyticsOpen = request()->routeIs('admin.reports-analytics.*');
    if (! $reportsAnalyticsOpen) {
        foreach ($reportsAnalyticsRouteNames as $reportsRoute) {
            if ($isActive($reportsRoute)) {
                $reportsAnalyticsOpen = true;
                break;
            }
        }
    }
    $reportsAnalyticsVisible = $canViewModule('reports_analytics');

    $settingsSubItems = $filterNavItems([
        ['route' => 'admin.settings.general', 'icon' => 'sliders', 'label' => 'General', 'module' => 'settings_general'],
        ['route' => 'admin.settings.locations.index', 'icon' => 'geo-alt', 'label' => 'States & cities', 'module' => 'settings_locations'],
        ['route' => 'admin.settings.site', 'icon' => 'globe2', 'label' => 'Site', 'module' => 'settings_site'],
        ['route' => 'admin.settings.cms.index', 'icon' => 'file-earmark-richtext', 'label' => 'CMS & Static Pages', 'module' => 'settings_cms'],
        ['route' => 'admin.settings.migrations', 'icon' => 'database', 'label' => 'Migrations', 'module' => 'settings_migrations'],
    ]);

    if (auth('admin')->user()->isSuperAdmin()) {
        $settingsSubItems[] = [
            'route' => 'admin.settings.employees.index',
            'icon' => 'people',
            'label' => 'Staff & permissions',
            'module' => 'settings_general',
            'super_only' => true,
        ];
    }

    $settingsRoutes = array_column($settingsSubItems, 'route');
    $settingsRoutes = array_merge($settingsRoutes, [
        'admin.settings.cms.edit',
        'admin.settings.locations.states.create',
        'admin.settings.locations.states.edit',
        'admin.settings.locations.cities.create',
        'admin.settings.locations.cities.edit',
    ]);
    $settingsOpen = false;
    foreach ($settingsRoutes as $settingsRoute) {
        if ($isActive($settingsRoute)) {
            $settingsOpen = true;
            break;
        }
    }
    if (! $settingsOpen && request()->routeIs('admin.settings.*')) {
        $settingsOpen = true;
    }
    $settingsVisible = count($settingsSubItems) > 0;

    $settingsPendingMigrations = app(\App\Services\AdminMigrationService::class)->summary()['pending'] ?? 0;
@endphp

<div class="sidebar-inner d-flex flex-column h-100 p-3 pb-3">
    <a href="{{ route('admin.dashboard') }}" class="d-block text-center mb-3 px-1 text-decoration-none">
        <img src="{{ site_logo_url() }}" alt="{{ site_name() }}" class="sidebar-logo sidebar-logo-compact mx-auto">
    </a>

    <div class="user-card user-card-compact mb-3">
        <div class="d-flex align-items-center gap-2">
            <img
                src="https://ui-avatars.com/api/?name={{ urlencode(auth('admin')->user()->name) }}&background=ff8c00&color=fff"
                class="rounded-circle border border-2 border-white shadow-sm"
                width="36"
                height="36"
                alt=""
            >
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold small text-truncate">{{ auth('admin')->user()->name }}</div>
                <div class="text-muted" style="font-size: 0.7rem">{{ auth('admin')->user()->isSuperAdmin() ? 'Super Admin' : 'Staff' }}</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav flex-grow-1" aria-label="Admin navigation">
        @if ($canViewModule('dashboard'))
            <a href="{{ route('admin.dashboard') }}" class="nav-pill-link nav-pill-link-compact {{ $isActive('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        @endif

        @if ($canViewModule('users'))
            <a href="{{ route('admin.users.index') }}" class="nav-pill-link nav-pill-link-compact {{ $isActive('admin.users.index') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>User Management</span>
            </a>
        @endif

        @if ($canViewModule('restaurants'))
            <a href="{{ route('admin.restaurants.index') }}" class="nav-pill-link nav-pill-link-compact {{ $isActive('admin.restaurants.index') ? 'active' : '' }}">
                <i class="bi bi-shop"></i>
                <span>Restaurant Management</span>
            </a>
        @endif

        @if ($canViewModule('delivery_partners'))
            <a href="{{ route('admin.delivery-partners.index') }}" class="nav-pill-link nav-pill-link-compact {{ $isActive('admin.delivery-partners.index') ? 'active' : '' }}">
                <i class="bi bi-bicycle"></i>
                <span>Delivery Partners</span>
            </a>
        @endif

        @if (count($foodMenuSubItems) > 0)
            <div class="nav-group {{ $foodMenuOpen ? 'is-open' : '' }}">
                <button
                    type="button"
                    class="nav-group-toggle nav-pill-link-compact w-100"
                    data-bs-toggle="collapse"
                    data-bs-target="#nav-group-menu"
                    aria-expanded="{{ $foodMenuOpen ? 'true' : 'false' }}"
                    aria-controls="nav-group-menu"
                >
                    <i class="bi bi-egg-fried"></i>
                    <span class="flex-grow-1 text-start">Food Category &amp; Menu</span>
                    <i class="bi bi-chevron-down nav-group-chevron"></i>
                </button>
                <div class="collapse {{ $foodMenuOpen ? 'show' : '' }}" id="nav-group-menu">
                    <div class="nav-sub-menu">
                        @foreach ($foodMenuSubItems as $link)
                            <a href="{{ route($link['route']) }}" class="nav-sub-link {{ $isActive($link['route']) ? 'active' : '' }}">
                                <i class="bi bi-{{ $link['icon'] }}"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @foreach ($menuGroups as $group)
            @php
                $visibleItems = $filterNavItems($group['items']);
                $routes = array_column($visibleItems, 'route');
                $open = $groupActive($routes);
            @endphp
            @if (count($visibleItems) > 0)
                <div class="nav-group {{ $open ? 'is-open' : '' }}">
                    <button
                        type="button"
                        class="nav-group-toggle nav-pill-link-compact w-100"
                        data-bs-toggle="collapse"
                        data-bs-target="#nav-group-{{ $group['id'] }}"
                        aria-expanded="{{ $open ? 'true' : 'false' }}"
                        aria-controls="nav-group-{{ $group['id'] }}"
                    >
                        <i class="bi bi-{{ $group['icon'] }}"></i>
                        <span class="flex-grow-1 text-start">{{ $group['label'] }}</span>
                        <i class="bi bi-chevron-down nav-group-chevron"></i>
                    </button>
                    <div class="collapse {{ $open ? 'show' : '' }}" id="nav-group-{{ $group['id'] }}">
                        <div class="nav-sub-menu">
                            @foreach ($visibleItems as $link)
                                <a href="{{ route($link['route']) }}" class="nav-sub-link {{ $isActive($link['route']) ? 'active' : '' }}">
                                    <i class="bi bi-{{ $link['icon'] }}"></i>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($reportsAnalyticsVisible)
            @php
                $reportsAnalyticsSections = [
                    [
                        'heading' => 'Reports',
                        'items' => [
                            ['route' => 'admin.reports-analytics.orders', 'icon' => 'bag-check', 'label' => 'Order reports'],
                            ['route' => 'admin.reports-analytics.revenue', 'icon' => 'currency-rupee', 'label' => 'Revenue reports'],
                            ['route' => 'admin.reports-analytics.user-activity', 'icon' => 'people', 'label' => 'User activity'],
                            ['route' => 'admin.reports-analytics.restaurant-performance', 'icon' => 'shop', 'label' => 'Restaurant performance'],
                            ['route' => 'admin.reports-analytics.delivery-partner-performance', 'icon' => 'bicycle', 'label' => 'Delivery partner performance'],
                        ],
                    ],
                    [
                        'heading' => 'Analytics',
                        'items' => [
                            ['route' => 'admin.reports-analytics.popular', 'icon' => 'fire', 'label' => 'Popular items & restaurants'],
                            ['route' => 'admin.reports-analytics.order-trends', 'icon' => 'graph-up', 'label' => 'Order trends'],
                        ],
                    ],
                ];
            @endphp
            <div class="nav-group {{ $reportsAnalyticsOpen ? 'is-open' : '' }}">
                <button
                    type="button"
                    class="nav-group-toggle nav-pill-link-compact w-100"
                    data-bs-toggle="collapse"
                    data-bs-target="#nav-group-reports-analytics"
                    aria-expanded="{{ $reportsAnalyticsOpen ? 'true' : 'false' }}"
                    aria-controls="nav-group-reports-analytics"
                >
                    <i class="bi bi-bar-chart-line"></i>
                    <span class="flex-grow-1 text-start">Reports &amp; Analytics</span>
                    <i class="bi bi-chevron-down nav-group-chevron"></i>
                </button>
                <div class="collapse {{ $reportsAnalyticsOpen ? 'show' : '' }}" id="nav-group-reports-analytics">
                    <div class="nav-sub-menu">
                        @foreach ($reportsAnalyticsSections as $section)
                            <div class="nav-sub-heading">{{ $section['heading'] }}</div>
                            @foreach ($section['items'] as $link)
                                <a href="{{ route($link['route']) }}" class="nav-sub-link {{ $isActive($link['route']) ? 'active' : '' }}">
                                    <i class="bi bi-{{ $link['icon'] }}"></i>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($settingsVisible)
            <div class="nav-group {{ $settingsOpen ? 'is-open' : '' }}">
                <button
                    type="button"
                    class="nav-group-toggle nav-pill-link-compact w-100"
                    data-bs-toggle="collapse"
                    data-bs-target="#nav-group-settings"
                    aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}"
                    aria-controls="nav-group-settings"
                >
                    <i class="bi bi-gear"></i>
                    <span class="flex-grow-1 text-start">Settings</span>
                    <i class="bi bi-chevron-down nav-group-chevron"></i>
                </button>
                <div class="collapse {{ $settingsOpen ? 'show' : '' }}" id="nav-group-settings">
                    <div class="nav-sub-menu">
                        @foreach ($settingsSubItems as $link)
                            @php
                                $settingsLinkActive = $isActive($link['route']);
                                if ($link['route'] === 'admin.settings.cms.index' && request()->routeIs('admin.settings.cms.*')) {
                                    $settingsLinkActive = true;
                                }
                                if ($link['route'] === 'admin.settings.locations.index' && request()->routeIs('admin.settings.locations.*')) {
                                    $settingsLinkActive = true;
                                }
                                if ($link['route'] === 'admin.settings.employees.index' && request()->routeIs('admin.settings.employees.*')) {
                                    $settingsLinkActive = true;
                                }
                                $badge = ($link['route'] === 'admin.settings.migrations') ? $settingsPendingMigrations : 0;
                            @endphp
                            <a href="{{ route($link['route']) }}" class="nav-sub-link {{ $settingsLinkActive ? 'active' : '' }}">
                                <i class="bi bi-{{ $link['icon'] }}"></i>
                                <span>{{ $link['label'] }}</span>
                                @if (! empty($badge))
                                    <span class="badge rounded-pill text-bg-warning ms-auto">{{ $badge }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </nav>

    <form action="{{ route('admin.logout') }}" method="POST" class="sidebar-footer mt-2 pt-2 border-top">
        @csrf
        <button type="submit" class="btn btn-outline-brand btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </button>
    </form>
</div>
