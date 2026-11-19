<?php

namespace App\Services;

class RestaurantAuthConfig
{
    public function loginEmailEnabled(): bool
    {
        return (bool) site_setting(
            'restaurant_login_email_enabled',
            config('restaurant_auth.login_email_enabled', true)
        );
    }

    public function loginPhoneEnabled(): bool
    {
        return (bool) site_setting(
            'restaurant_login_phone_enabled',
            config('restaurant_auth.login_phone_enabled', true)
        );
    }

    /** @return list<string> */
    public function enabledLoginMethods(): array
    {
        $methods = [];
        if ($this->loginEmailEnabled()) {
            $methods[] = 'email';
        }
        if ($this->loginPhoneEnabled()) {
            $methods[] = 'phone';
        }

        return $methods;
    }

    public function defaultLoginMethod(): string
    {
        $methods = $this->enabledLoginMethods();

        return $methods[0] ?? 'email';
    }

    public function registrationOtpEnabled(): bool
    {
        return (bool) site_setting(
            'restaurant_registration_otp_enabled',
            config('restaurant_auth.registration_otp_enabled', false)
        );
    }

    public function otpLength(): int
    {
        return max(4, (int) config('restaurant_auth.otp_length', 6));
    }

    public function otpTtlMinutes(): int
    {
        return max(1, (int) config('restaurant_auth.otp_ttl_minutes', 10));
    }

    public function otpResendCooldownSeconds(): int
    {
        return max(30, (int) config('restaurant_auth.otp_resend_cooldown_seconds', 60));
    }

    public function galleryMaxImages(): int
    {
        return max(1, (int) config('restaurant_auth.gallery_max_images', 5));
    }

    public function galleryMaxKb(): int
    {
        return max(512, (int) config('restaurant_auth.gallery_max_kb', 2048));
    }
}
