<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IfscCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = strtoupper(trim(is_string($value) ? $value : (string) $value));

        if (! preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $code)) {
            $fail('Enter a valid IFSC code (e.g. HDFC0001234).');
        }
    }
}
