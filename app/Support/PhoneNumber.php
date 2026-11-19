<?php

namespace App\Support;

class PhoneNumber
{
    public static function normalize(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        return $digits;
    }

    public static function display(?string $stored): string
    {
        if (! is_string($stored) || $stored === '') {
            return '';
        }

        if (str_starts_with($stored, '91') && strlen($stored) === 12) {
            return '+91 '.substr($stored, 2, 5).' '.substr($stored, 7);
        }

        return $stored;
    }
}
