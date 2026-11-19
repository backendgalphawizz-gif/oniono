<?php

namespace App\Support;

use App\Rules\BankAccountNumber;
use App\Rules\IfscCode;
use App\Rules\IndianMobile;
class RestaurantRegistrationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function stepOneRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,filter', 'max:255', 'unique:restaurants,email', 'unique:restaurant_users,email'],
            'phone' => ['required', 'string', 'max:20', new IndianMobile],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'address' => ['required', 'string', 'max:500'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'pincode' => ['required', 'string', 'regex:/^\d{6}$/'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function payoutRules(): array
    {
        return [
            'bank_account_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s.\'-]+$/u'],
            'bank_account_number' => ['required', 'string', 'max:50', new BankAccountNumber],
            'bank_ifsc' => ['required', 'string', 'max:11', new IfscCode],
            'bank_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s.\'-]+$/u'],
            'gst_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function otpPhoneRules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20', new IndianMobile],
        ];
    }
}
