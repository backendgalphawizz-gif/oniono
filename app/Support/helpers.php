<?php

if (! function_exists('money_inr')) {
    function money_inr(float|string|null $amount): string
    {
        $symbol = site_setting('currency_symbol', '₹');

        return $symbol.number_format((float) $amount, 2);
    }
}

if (! function_exists('restaurant_id')) {
    function restaurant_id(): ?int
    {
        return auth('restaurant')->user()?->restaurant_id;
    }
}

if (! function_exists('site_settings')) {
    function site_settings(): \App\Services\SiteSettingsService
    {
        return app(\App\Services\SiteSettingsService::class);
    }
}

if (! function_exists('site_name')) {
    function site_name(): string
    {
        return site_settings()->siteName();
    }
}

if (! function_exists('site_logo_url')) {
    function site_logo_url(): string
    {
        return site_settings()->logoUrl();
    }
}

if (! function_exists('site_favicon_url')) {
    function site_favicon_url(): string
    {
        return site_settings()->faviconUrl();
    }
}

if (! function_exists('site_setting')) {
    function site_setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::getValue($key, $default);
    }
}

if (! function_exists('admin_listing_per_page')) {
    function admin_listing_per_page(): int
    {
        return (int) config('admin.listing_per_page', 15);
    }
}

if (! function_exists('admin_back_link')) {
    /**
     * @return array{url: string, label: string}|null
     */
    function admin_back_link(): ?array
    {
        $route = request()->route();
        if (! $route) {
            return null;
        }

        $name = $route->getName();
        if (! is_string($name) || ! str_starts_with($name, 'admin.')) {
            return null;
        }

        $hubRoutes = [
            'admin.dashboard',
            'admin.login',
            'admin.menu-management.index',
        ];

        if (in_array($name, $hubRoutes, true) || str_ends_with($name, '.index')) {
            return null;
        }

        if ($name === 'admin.menu-management.featured') {
            return [
                'url' => route('admin.menu-management.index'),
                'label' => 'Back to menu overview',
            ];
        }

        if ($name === 'admin.settlements.create') {
            return [
                'url' => route('admin.settlements.index'),
                'label' => 'Back to settlements',
            ];
        }

        if (str_ends_with($name, '.create')) {
            $indexRoute = preg_replace('/\.create$/', '.index', $name);
            if (\Illuminate\Support\Facades\Route::has($indexRoute)) {
                return ['url' => route($indexRoute), 'label' => 'Back to list'];
            }
        }

        if (str_ends_with($name, '.edit')) {
            $showRoute = preg_replace('/\.edit$/', '.show', $name);
            $model = collect($route->parameters())->first();
            if ($model !== null && \Illuminate\Support\Facades\Route::has($showRoute)) {
                return ['url' => route($showRoute, $model), 'label' => 'Back to details'];
            }

            $indexRoute = preg_replace('/\.edit$/', '.index', $name);
            if (\Illuminate\Support\Facades\Route::has($indexRoute)) {
                return ['url' => route($indexRoute), 'label' => 'Back to list'];
            }
        }

        if (str_ends_with($name, '.show')) {
            $indexRoute = preg_replace('/\.show$/', '.index', $name);
            if (\Illuminate\Support\Facades\Route::has($indexRoute)) {
                return ['url' => route($indexRoute), 'label' => 'Back to list'];
            }
        }

        return null;
    }
}

if (! function_exists('admin_geo_scope')) {
    function admin_geo_scope(): \App\Services\AdminGeoScopeService
    {
        return app(\App\Services\AdminGeoScopeService::class);
    }
}

if (! function_exists('admin_geo_scope_for')) {
    function admin_geo_scope_for(\App\Models\Admin $admin): \App\Services\AdminGeoScopeService
    {
        return admin_geo_scope()->forAdmin($admin);
    }
}

if (! function_exists('admin_can')) {
    function admin_can(string $module, string $action): bool
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = auth('admin')->user();

        if (! $admin) {
            return false;
        }

        return $admin->canModule($module, $action);
    }
}

if (! function_exists('admin_home_url')) {
    function admin_home_url(): string
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = auth('admin')->user();

        if (! $admin) {
            return route('admin.login');
        }

        if ($admin->isSuperAdmin()) {
            return route('admin.dashboard');
        }

        $moduleRoutes = [
            'dashboard' => 'admin.dashboard',
            'users' => 'admin.users.index',
            'restaurants' => 'admin.restaurants.index',
            'delivery_partners' => 'admin.delivery-partners.index',
            'menu_management' => 'admin.menu-management.index',
            'categories' => 'admin.categories.index',
            'sub_categories' => 'admin.sub-categories.index',
            'cuisines' => 'admin.cuisines.index',
            'food_items' => 'admin.food-items.index',
            'orders' => 'admin.orders.index',
            'delivery_assignment' => 'admin.delivery-assignment.index',
            'commissions' => 'admin.commissions.index',
            'settlements' => 'admin.settlements.index',
            'payments' => 'admin.payments.index',
            'coupons' => 'admin.coupons.index',
            'banners' => 'admin.banners.index',
            'notifications' => 'admin.notifications.index',
            'support' => 'admin.support.index',
            'reviews' => 'admin.reviews.index',
            'reports_analytics' => 'admin.reports-analytics.orders',
            'settings_general' => 'admin.settings.general',
            'settings_locations' => 'admin.settings.locations.index',
            'settings_site' => 'admin.settings.site',
            'settings_cms' => 'admin.settings.cms.index',
            'settings_migrations' => 'admin.settings.migrations',
        ];

        foreach ($moduleRoutes as $module => $routeName) {
            if ($admin->canModule($module, 'view') && \Illuminate\Support\Facades\Route::has($routeName)) {
                return route($routeName);
            }
        }

        return route('admin.dashboard');
    }
}

if (! function_exists('admin_permission_groups')) {
    /**
     * @return list<array{label: string, modules: array<string, string>}>
     */
    function admin_permission_groups(): array
    {
        return config('admin_permissions.groups', []);
    }
}
