<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IndianMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = PhoneNumber::normalize(is_string($value) ? $value : (string) $value);

        if (strlen($normalized) !== 12 || ! str_starts_with($normalized, '91')) {
            $fail('Enter a valid 10-digit Indian mobile number.');

            return;
        }

        $local = substr($normalized, 2);

        if (! preg_match('/^[6-9]\d{9}$/', $local)) {
            $fail('Enter a valid 10-digit Indian mobile number.');
        }
    }
}
