<?php

namespace App\Services\Otp;

use App\Services\Otp\Contracts\OtpDriver;
use App\Services\Otp\Drivers\LogOtpDriver;
use App\Services\Otp\Drivers\TwoFactorOtpDriver;
use InvalidArgumentException;

final class OtpService
{
    public function driver(?string $name = null): OtpDriver
    {
        $name ??= (string) config('otp.driver', 'twofactor');

        return match ($name) {
            'log' => app(LogOtpDriver::class),
            'twofactor' => app(TwoFactorOtpDriver::class),
            default => throw new InvalidArgumentException("Unsupported OTP driver [{$name}]."),
        };
    }

    /**
     * @return array{provider_ref: ?string, message: string}
     */
    public function send(string $phone10, string $purpose = 'login'): array
    {
        return $this->driver()->send($phone10, $purpose);
    }

    public function verify(string $phone10, string $code, string $purpose = 'login'): bool
    {
        return $this->driver()->verify($phone10, $code, $purpose);
    }
}
