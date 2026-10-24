<?php

namespace App\Services\Otp\Drivers;

use App\Models\Otp;
use App\Services\Otp\Contracts\OtpDriver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Local/dev driver — no SMS. Uses OTP_DEV_CODE (default 123456) when APP_ENV=local.
 */
final class LogOtpDriver implements OtpDriver
{
    public function send(string $phone10, string $purpose = 'login'): array
    {
        $code = (string) config('otp.dev_code', '123456');

        Otp::query()
            ->where('phone', $phone10)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        Otp::query()->create([
            'phone' => $phone10,
            'code_hash' => Hash::make($code),
            'provider_ref' => 'log',
            'channel' => 'sms',
            'purpose' => $purpose,
            'expires_at' => now()->addSeconds((int) config('otp.ttl_seconds', 300)),
        ]);

        Log::info('Tan Tan OTP (log driver)', [
            'phone' => $phone10,
            'code' => $code,
            'purpose' => $purpose,
        ]);

        return [
            'provider_ref' => 'log',
            'message' => 'OTP sent (log driver).',
        ];
    }

    public function verify(string $phone10, string $code, string $purpose = 'login'): bool
    {
        /** @var Otp|null $otp */
        $otp = Otp::query()
            ->where('phone', $phone10)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->isExpired()) {
            return false;
        }

        $otp->increment('attempts');

        if ($otp->attempts > 5) {
            return false;
        }

        if (! is_string($otp->code_hash) || ! Hash::check($code, $otp->code_hash)) {
            return false;
        }

        $otp->forceFill(['verified_at' => now()])->save();

        return true;
    }
}
