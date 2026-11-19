<?php

return [

    /*
    | Restaurant panel login methods (can be overridden via settings table keys
    | restaurant_login_email_enabled / restaurant_login_phone_enabled).
    */
    'login_email_enabled' => env('RESTAURANT_LOGIN_EMAIL', true),
    'login_phone_enabled' => env('RESTAURANT_LOGIN_PHONE', true),

    /*
    | When true, new registrations must verify mobile OTP before submit.
    | Override: restaurant_registration_otp_enabled in settings.
    */
    'registration_otp_enabled' => env('RESTAURANT_REGISTRATION_OTP', true),

    'otp_length' => (int) env('RESTAURANT_OTP_LENGTH', 6),
    'otp_ttl_minutes' => (int) env('RESTAURANT_OTP_TTL_MINUTES', 10),
    'otp_resend_cooldown_seconds' => (int) env('RESTAURANT_OTP_RESEND_COOLDOWN', 60),

    'gallery_max_images' => (int) env('RESTAURANT_GALLERY_MAX_IMAGES', 5),
    'gallery_max_kb' => (int) env('RESTAURANT_GALLERY_MAX_KB', 2048),

];
