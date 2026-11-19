<?php

return [

    'exempt_routes' => [
        'admin.logout',
    ],

    'super_admin_only_route_prefixes' => [
        'admin.settings.employees',
    ],

    /**
     * Route name prefix => module key (longer / more specific prefixes are matched first).
     */
    'route_prefixes' => [
        'admin.settings.employees' => null,
        'admin.settings.migrations' => 'settings_migrations',
        'admin.settings.cms' => 'settings_cms',
        'admin.settings.site' => 'settings_site',
        'admin.settings.locations' => 'settings_locations',
        'admin.settings.general' => 'settings_general',
        'admin.settings' => 'settings_general',
        'admin.reports-analytics' => 'reports_analytics',
        'admin.menu-management' => 'menu_management',
        'admin.sub-categories' => 'sub_categories',
        'admin.delivery-partners' => 'delivery_partners',
        'admin.delivery-assignment' => 'delivery_assignment',
        'admin.food-items' => 'food_items',
        'admin.dashboard' => 'dashboard',
        'admin.restaurants' => 'restaurants',
        'admin.users' => 'users',
        'admin.categories' => 'categories',
        'admin.cuisines' => 'cuisines',
        'admin.orders' => 'orders',
        'admin.payments' => 'payments',
        'admin.commissions' => 'commissions',
        'admin.settlements' => 'settlements',
        'admin.coupons' => 'coupons',
        'admin.banners' => 'banners',
        'admin.reviews' => 'reviews',
        'admin.support' => 'support',
        'admin.notifications' => 'notifications',
    ],

    'action_overrides' => [
        // route_name => action (view|edit|delete)
    ],

    'groups' => [
        [
            'label' => 'Core',
            'modules' => [
                'dashboard' => 'Dashboard',
            ],
        ],
        [
            'label' => 'People',
            'modules' => [
                'users' => 'User management',
                'restaurants' => 'Restaurants',
                'delivery_partners' => 'Delivery partners',
            ],
        ],
        [
            'label' => 'Food & menu',
            'modules' => [
                'menu_management' => 'Menu overview & featured',
                'categories' => 'Categories',
                'sub_categories' => 'Sub-categories',
                'cuisines' => 'Cuisines',
                'food_items' => 'Food listings',
            ],
        ],
        [
            'label' => 'Operations',
            'modules' => [
                'orders' => 'Orders',
                'delivery_assignment' => 'Delivery assignment',
            ],
        ],
        [
            'label' => 'Finance',
            'modules' => [
                'commissions' => 'Commission',
                'settlements' => 'Settlements',
                'payments' => 'Payments',
            ],
        ],
        [
            'label' => 'Marketing',
            'modules' => [
                'coupons' => 'Coupons',
                'banners' => 'Banners',
                'notifications' => 'Messages / notifications',
            ],
        ],
        [
            'label' => 'Support',
            'modules' => [
                'support' => 'Support tickets',
                'reviews' => 'Reviews',
            ],
        ],
        [
            'label' => 'Reports & analytics',
            'modules' => [
                'reports_analytics' => 'Reports & analytics',
            ],
        ],
        [
            'label' => 'Settings',
            'modules' => [
                'settings_general' => 'General settings',
                'settings_locations' => 'States & cities',
                'settings_site' => 'Site settings',
                'settings_cms' => 'CMS & static pages',
                'settings_migrations' => 'Database migrations',
            ],
        ],
    ],

    'module_keys' => [],

];
