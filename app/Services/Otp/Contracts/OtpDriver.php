<?php

namespace App\Services\Otp\Contracts;

interface OtpDriver
{
    /**
     * Send an OTP to the given 10-digit Indian mobile.
     *
     * @return array{provider_ref: ?string, message: string}
     */
    public function send(string $phone10, string $purpose = 'login'): array;

    /**
     * Verify OTP for the given 10-digit Indian mobile.
     */
    public function verify(string $phone10, string $code, string $purpose = 'login'): bool;
}
