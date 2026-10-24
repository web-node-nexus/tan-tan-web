<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Otp\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class PhoneAuthService
{
    public function __construct(
        private readonly OtpService $otp,
    ) {}

    /**
     * @return array{phone: string, masked: string, expires_in: int, message: string}
     */
    public function sendLoginOtp(string $rawPhone): array
    {
        try {
            $phone = PhoneNumber::normalizeIn($rawPhone);
        } catch (InvalidArgumentException) {
            throw new InvalidArgumentException(__('api.auth.invalid_phone'));
        }

        $result = $this->otp->send($phone, 'login');

        return [
            'phone' => $phone,
            'masked' => PhoneNumber::masked($phone),
            'expires_in' => (int) config('otp.ttl_seconds', 300),
            'message' => $result['message'],
        ];
    }

    /**
     * @return array{token: string, token_type: string, is_new_user: bool, user: User}
     */
    public function verifyLoginOtp(string $rawPhone, string $code, ?string $name = null): array
    {
        try {
            $phone = PhoneNumber::normalizeIn($rawPhone);
        } catch (InvalidArgumentException) {
            throw new InvalidArgumentException(__('api.auth.invalid_phone'));
        }

        $code = trim($code);
        if (! preg_match('/^\d{4,8}$/', $code)) {
            throw new InvalidArgumentException(__('api.auth.invalid_otp'));
        }

        $ok = $this->otp->verify($phone, $code, 'login');
        if (! $ok) {
            throw new RuntimeException(__('api.auth.otp_mismatch'));
        }

        $user = User::query()->where('phone', $phone)->first();
        $isNew = false;

        if ($user?->isBanned()) {
            throw new RuntimeException(__('api.auth.account_banned'));
        }

        if (! $user) {
            $isNew = true;
            $display = trim((string) $name);
            if ($display === '') {
                $display = 'User '.$phone;
            }

            $user = User::query()->create([
                'name' => $display,
                'email' => $phone.'@phone.tantan.local',
                'password' => Hash::make(Str::random(40)),
                'phone' => $phone,
                'phone_verified_at' => now(),
                'locale' => app()->getLocale() === 'hi' ? 'hi' : 'en',
                'theme' => 'system',
                'referral_code' => $this->uniqueReferralCode(),
                'last_active_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'phone_verified_at' => $user->phone_verified_at ?? now(),
                'last_active_at' => now(),
            ])->save();
        }

        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'is_new_user' => $isNew,
            'user' => $user->fresh(),
        ];
    }

    private function uniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::query()->where('referral_code', $code)->exists());

        return $code;
    }
}
