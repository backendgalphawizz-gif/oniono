<?php

/**
 * Table-wise migration groups for the admin Settings → Migrations panel.
 * Files are run in listed order when a group is migrated.
 */
return [
    'groups' => [
        'framework' => [
            'label' => 'Framework',
            'tables' => 'users (base), cache, jobs, sessions, API tokens',
            'files' => [
                '0001_01_01_000000_create_users_table.php',
                '0001_01_01_000001_create_cache_table.php',
                '0001_01_01_000002_create_jobs_table.php',
                '2026_11_04_092104_create_personal_access_tokens_table.php',
            ],
        ],
        'admins' => [
            'label' => 'Admins',
            'tables' => 'admins',
            'files' => [
                '2026_03_28_000001_create_admins_table.php',
                '2026_11_05_000004_add_login_id_to_admins_table.php',
            ],
        ],
        'settings' => [
            'label' => 'Settings & CMS',
            'tables' => 'settings, static pages',
            'files' => [
                '2026_03_28_000002_create_settings_table.php',
            ],
        ],
        'restaurants' => [
            'label' => 'Restaurants',
            'tables' => 'restaurants, restaurant users, documents, images',
            'files' => [
                '2026_03_28_000003_create_restaurants_table.php',
                '2026_11_05_000003_add_is_blocked_to_restaurants_table.php',
                '2026_11_05_000005_add_commission_gst_fields.php',
            ],
        ],
        'catalog' => [
            'label' => 'Catalog & menu',
            'tables' => 'categories, cuisines, food items, variants',
            'files' => [
                '2026_03_28_000004_create_catalog_tables.php',
            ],
        ],
        'delivery' => [
            'label' => 'Delivery & addresses',
            'tables' => 'delivery partners, addresses, documents',
            'files' => [
                '2026_03_28_000005_create_delivery_and_addresses.php',
                '2026_11_05_000002_add_partner_moderation_fields_to_delivery_partners_table.php',
                '2026_11_05_000006_add_admin_note_to_delivery_documents.php',
            ],
        ],
        'orders' => [
            'label' => 'Orders',
            'tables' => 'orders, order items, delivery assignments',
            'files' => [
                '2026_03_28_000006_create_orders_tables.php',
            ],
        ],
        'financial' => [
            'label' => 'Payments & settlements',
            'tables' => 'payments, settlements, earnings',
            'files' => [
                '2026_03_28_000007_create_financial_tables.php',
            ],
        ],
        'engagement' => [
            'label' => 'Engagement',
            'tables' => 'reviews, coupons, banners, support',
            'files' => [
                '2026_03_28_000008_create_engagement_tables.php',
            ],
        ],
        'customers' => [
            'label' => 'Customers (users)',
            'tables' => 'users — profile & moderation fields',
            'files' => [
                '2026_11_05_000001_add_is_suspended_to_users_table.php',
            ],
        ],
    ],
];
