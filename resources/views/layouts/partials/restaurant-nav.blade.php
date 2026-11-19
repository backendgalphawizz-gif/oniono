@php
    $restaurantUser = auth('restaurant')->user();
    $restaurantName = $restaurantUser->restaurant->name ?? 'Restaurant';

    $isActive = function (string $route): bool {
        if ($route === 'restaurant.dashboard') {
            return request()->routeIs('restaurant.dashboard');
        }

        $prefix = str_replace('.index', '', $route);
        $prefix = str_replace('.edit', '', $prefix);

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

    $menuGroups = [
        [
            'id' => 'operations',
            'label' => 'Operations',
            'icon' => 'boxes',
            'items' => [
                ['route' => 'restaurant.orders.index', 'icon' => 'bag-check', 'label' => 'Orders'],
                ['route' => 'restaurant.menu.index', 'icon' => 'egg-fried', 'label' => 'Menu'],
                ['route' => 'restaurant.menu-categories.index', 'icon' => 'folder2-open', 'label' => 'Categories'],
            ],
        ],
        [
            'id' => 'finance',
            'label' => 'Finance',
            'icon' => 'wallet2',
            'items' => [
                ['route' => 'restaurant.earnings.index', 'icon' => 'graph-up-arrow', 'label' => 'Earnings & payments'],
                ['route' => 'restaurant.settlements.index', 'icon' => 'cash-stack', 'label' => 'Settlements'],
            ],
        ],
        [
            'id' => 'store',
            'label' => 'Your store',
            'icon' => 'shop',
            'items' => [
                ['route' => 'restaurant.profile.edit', 'icon' => 'person-badge', 'label' => 'Profile management'],
                ['route' => 'restaurant.reviews.index', 'icon' => 'star', 'label' => 'Ratings & reviews'],
            ],
        ],
        [
            'id' => 'support',
            'label' => 'Help & alerts',
            'icon' => 'life-preserver',
            'items' => [
                ['route' => 'restaurant.notifications.index', 'icon' => 'bell', 'label' => 'Notifications'],
                ['route' => 'restaurant.help.index', 'icon' => 'life-preserver', 'label' => 'Support & help'],
            ],
        ],
    ];
@endphp

<div class="sidebar-inner d-flex flex-column h-100 p-3 pb-3">
    <a href="{{ route('restaurant.dashboard') }}" class="d-block text-center mb-3 px-1 text-decoration-none">
        <img src="{{ site_logo_url() }}" alt="{{ site_name() }}" class="sidebar-logo sidebar-logo-compact mx-auto">
    </a>

    <div class="user-card user-card-compact mb-3">
        <div class="d-flex align-items-center gap-2">
            @php
                $sidebarLogo = $restaurantUser->restaurant->logo
                    ? asset('storage/'.$restaurantUser->restaurant->logo)
                    : 'https://ui-avatars.com/api/?name='.urlencode($restaurantName).'&background=ff8c00&color=fff';
            @endphp
            <img
                src="{{ $sidebarLogo }}"
                class="rounded-circle border border-2 border-white shadow-sm object-fit-cover"
                width="36"
                height="36"
                alt=""
            >
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold small text-truncate">{{ $restaurantName }}</div>
                <div class="text-muted text-truncate" style="font-size: 0.7rem">{{ $restaurantUser->name }}</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav flex-grow-1" aria-label="Restaurant navigation">
        <a href="{{ route('restaurant.dashboard') }}" class="nav-pill-link nav-pill-link-compact {{ $isActive('restaurant.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        @foreach ($menuGroups as $group)
            @php
                $routes = array_column($group['items'], 'route');
                $open = $groupActive($routes);
            @endphp
            <div class="nav-group {{ $open ? 'is-open' : '' }}">
                <button
                    type="button"
                    class="nav-group-toggle nav-pill-link-compact w-100"
                    data-bs-toggle="collapse"
                    data-bs-target="#rest-nav-group-{{ $group['id'] }}"
                    aria-expanded="{{ $open ? 'true' : 'false' }}"
                    aria-controls="rest-nav-group-{{ $group['id'] }}"
                >
                    <i class="bi bi-{{ $group['icon'] }}"></i>
                    <span class="flex-grow-1 text-start">{{ $group['label'] }}</span>
                    <i class="bi bi-chevron-down nav-group-chevron"></i>
                </button>
                <div class="collapse {{ $open ? 'show' : '' }}" id="rest-nav-group-{{ $group['id'] }}">
                    <div class="nav-sub-menu">
                        @foreach ($group['items'] as $link)
                            <a href="{{ route($link['route']) }}" class="nav-sub-link {{ $isActive($link['route']) ? 'active' : '' }}">
                                <i class="bi bi-{{ $link['icon'] }}"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <a href="{{ route('restaurant.settings.edit') }}" class="nav-pill-link nav-pill-link-compact {{ $isActive('restaurant.settings.edit') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
    </nav>

    <form action="{{ route('restaurant.logout') }}" method="POST" class="sidebar-footer mt-2 pt-2 border-top">
        @csrf
        <button type="submit" class="btn btn-outline-brand btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </button>
    </form>
</div>
