<?php

return [
    'disk' => 'public',
    'directory' => 'site',

    /*
    | Default values seeded and used when a key is missing.
    */
    'defaults' => [
        'platform_name' => ['value' => 'Oniono', 'type' => 'string', 'group' => 'site'],
        'site_tagline' => ['value' => 'Food delivery, simplified.', 'type' => 'string', 'group' => 'site'],
        'site_logo' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'site_logo_dark' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'site_favicon' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'site_email' => ['value' => 'hello@oniono.test', 'type' => 'string', 'group' => 'site'],
        'site_phone' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'site_address' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'support_email' => ['value' => 'support@oniono.test', 'type' => 'string', 'group' => 'site'],
        'meta_description' => ['value' => 'Order food from your favourite restaurants with Oniono.', 'type' => 'string', 'group' => 'site'],
        'meta_keywords' => ['value' => 'food delivery, restaurants, online ordering', 'type' => 'string', 'group' => 'site'],
        'social_facebook' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'social_instagram' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'social_twitter' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'social_youtube' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'social_linkedin' => ['value' => '', 'type' => 'string', 'group' => 'site'],
        'currency_code' => ['value' => 'INR', 'type' => 'string', 'group' => 'site'],
        'currency_symbol' => ['value' => '₹', 'type' => 'string', 'group' => 'site'],
        'timezone' => ['value' => 'Asia/Kolkata', 'type' => 'string', 'group' => 'site'],
        'theme_primary_color' => ['value' => '#ff8c00', 'type' => 'string', 'group' => 'site'],
        'theme_accent_color' => ['value' => '#7c3aed', 'type' => 'string', 'group' => 'site'],
        'footer_text' => ['value' => '© Oniono. All rights reserved.', 'type' => 'string', 'group' => 'site'],
        'maintenance_mode' => ['value' => 'false', 'type' => 'boolean', 'group' => 'site'],
    ],

    'image_fields' => [
        'site_logo' => ['label' => 'Site logo', 'max' => 2048, 'mimes' => 'jpg,jpeg,png,webp,svg'],
        'site_logo_dark' => ['label' => 'Logo (dark background)', 'max' => 2048, 'mimes' => 'jpg,jpeg,png,webp,svg'],
        'site_favicon' => ['label' => 'Favicon', 'max' => 512, 'mimes' => 'jpg,jpeg,png,ico,webp,svg'],
    ],
];
