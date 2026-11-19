<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BankAccountNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $number = preg_replace('/\s+/', '', is_string($value) ? $value : (string) $value);

        if ($number === null || $number === '' || ! preg_match('/^\d{9,18}$/', $number)) {
            $fail('Enter a valid bank account number (9–18 digits).');
        }
    }
}
