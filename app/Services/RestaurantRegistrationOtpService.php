<?php

namespace App\Services;

use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
class RestaurantRegistrationOtpService
{
    private const PURPOSE = 'restaurant_registration';

    public function __construct(
        protected RestaurantAuthConfig $authConfig,
    ) {}

    public function send(string $rawPhone): array
    {
        $phone = PhoneNumber::normalize($rawPhone);
        if (strlen($phone) < 10) {
            return ['ok' => false, 'message' => 'Enter a valid mobile number.'];
        }

        $rateKey = 'restaurant-reg-otp:'.$phone;
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return ['ok' => false, 'message' => "Too many attempts. Try again in {$seconds} seconds."];
        }

        $cooldownKey = $this->cacheKey($phone).':cooldown';
        if (! Cache::add($cooldownKey, 1, $this->authConfig->otpResendCooldownSeconds())) {
            return ['ok' => false, 'message' => 'Please wait before requesting another OTP.'];
        }

        RateLimiter::hit($rateKey, 600);

        $code = $this->generateCode();
        Cache::put($this->cacheKey($phone), hash('sha256', $code), now()->addMinutes($this->authConfig->otpTtlMinutes()));

        Log::info('Restaurant registration OTP generated', [
            'phone' => $phone,
            'code' => config('app.debug') ? $code : '[hidden]',
        ]);

        $payload = [
            'ok' => true,
            'message' => 'OTP sent to your mobile number.',
            'expires_in' => $this->authConfig->otpTtlMinutes() * 60,
        ];

        if (config('app.debug')) {
            $payload['debug_code'] = $code;
        }

        return $payload;
    }

    public function verify(string $rawPhone, string $code): bool
    {
        $phone = PhoneNumber::normalize($rawPhone);
        $stored = Cache::get($this->cacheKey($phone));
        if (! is_string($stored)) {
            return false;
        }

        if (! hash_equals($stored, hash('sha256', $code))) {
            return false;
        }

        Cache::forget($this->cacheKey($phone));
        session([
            'restaurant_register_phone_verified' => $phone,
            'restaurant_register_phone_verified_at' => now()->timestamp,
        ]);

        return true;
    }

    public function isPhoneVerifiedInSession(string $rawPhone): bool
    {
        $phone = PhoneNumber::normalize($rawPhone);
        $verified = session('restaurant_register_phone_verified');
        $verifiedAt = (int) session('restaurant_register_phone_verified_at', 0);

        if ($verified !== $phone || $verifiedAt === 0) {
            return false;
        }

        return (time() - $verifiedAt) <= ($this->authConfig->otpTtlMinutes() * 60);
    }

    public function clearVerifiedSession(): void
    {
        session()->forget(['restaurant_register_phone_verified', 'restaurant_register_phone_verified_at']);
    }

    private function cacheKey(string $normalizedPhone): string
    {
        return self::PURPOSE.':'.$normalizedPhone;
    }

    private function generateCode(): string
    {
        $length = $this->authConfig->otpLength();
        $max = (10 ** $length) - 1;
        $number = random_int(0, $max);

        return str_pad((string) $number, $length, '0', STR_PAD_LEFT);
    }
}
