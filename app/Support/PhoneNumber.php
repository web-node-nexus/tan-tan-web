<?php

namespace App\Support;

use InvalidArgumentException;

final class PhoneNumber
{
    /**
     * Normalize to 10-digit Indian mobile (no country code).
     */
    public static function normalizeIn(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (! preg_match('/^[6-9]\d{9}$/', $digits)) {
            throw new InvalidArgumentException('Invalid Indian mobile number.');
        }

        return $digits;
    }

    /** 91XXXXXXXXXX for providers that expect country code. */
    public static function withCountryCode(string $tenDigit): string
    {
        return '91'.$tenDigit;
    }

    public static function masked(string $tenDigit): string
    {
        return substr($tenDigit, 0, 2).'******'.substr($tenDigit, -2);
    }
}
