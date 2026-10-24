<?php

namespace App\Services\Otp\Drivers;

use App\Models\Otp;
use App\Services\Otp\Contracts\OtpDriver;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * 2factor.in — AUTOGEN send + session VERIFY.
 *
 * Send:   GET {base}/{key}/SMS/{10digit}/AUTOGEN/{template}
 *         → Details = session_id
 * Verify: GET {base}/{key}/SMS/VERIFY/{session_id}/{otp}
 */
final class TwoFactorOtpDriver implements OtpDriver
{
    public function send(string $phone10, string $purpose = 'login'): array
    {
        $apiKey = (string) config('otp.twofactor.api_key');
        $template = (string) config('otp.twofactor.template', 'OTP1');
        $base = (string) config('otp.twofactor.base_url');

        if ($apiKey === '') {
            throw new RuntimeException('TWOFACTOR_API_KEY is not configured.');
        }

        $url = sprintf('%s/%s/SMS/%s/AUTOGEN/%s', $base, $apiKey, $phone10, rawurlencode($template));

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('otp.twofactor.timeout', 15))
                ->get($url)
                ->throw();
        } catch (RequestException $e) {
            Log::error('2factor send OTP HTTP error', [
                'phone' => $phone10,
                'status' => $e->response?->status(),
                'body' => $e->response?->body(),
            ]);

            throw new RuntimeException('Unable to send OTP right now. Please try again.');
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];
        $status = strtolower((string) ($json['Status'] ?? $json['status'] ?? ''));
        $details = (string) ($json['Details'] ?? $json['details'] ?? '');

        if ($status !== 'success' || $details === '') {
            Log::warning('2factor send OTP rejected', ['phone' => $phone10, 'payload' => $json]);

            throw new RuntimeException($details !== '' ? $details : 'OTP could not be sent.');
        }

        Otp::query()
            ->where('phone', $phone10)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        Otp::query()->create([
            'phone' => $phone10,
            'code_hash' => null,
            'provider_ref' => $details, // 2factor session id
            'channel' => 'sms',
            'purpose' => $purpose,
            'expires_at' => now()->addSeconds((int) config('otp.ttl_seconds', 300)),
        ]);

        return [
            'provider_ref' => $details,
            'message' => 'OTP sent successfully.',
        ];
    }

    public function verify(string $phone10, string $code, string $purpose = 'login'): bool
    {
        $apiKey = (string) config('otp.twofactor.api_key');
        $base = (string) config('otp.twofactor.base_url');

        if ($apiKey === '') {
            throw new RuntimeException('TWOFACTOR_API_KEY is not configured.');
        }

        /** @var Otp|null $otp */
        $otp = Otp::query()
            ->where('phone', $phone10)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp || ! is_string($otp->provider_ref) || $otp->provider_ref === '') {
            Log::info('2factor verify missing session', ['phone' => $phone10]);

            return false;
        }

        if ($otp->isExpired()) {
            return false;
        }

        $otp->increment('attempts');
        if ($otp->attempts > 5) {
            return false;
        }

        $sessionId = $otp->provider_ref;
        $url = sprintf('%s/%s/SMS/VERIFY/%s/%s', $base, $apiKey, rawurlencode($sessionId), rawurlencode($code));

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('otp.twofactor.timeout', 15))
                ->get($url);
        } catch (\Throwable $e) {
            Log::error('2factor verify OTP exception', ['phone' => $phone10, 'error' => $e->getMessage()]);

            return false;
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];
        $status = strtolower((string) ($json['Status'] ?? $json['status'] ?? ''));
        $details = strtolower((string) ($json['Details'] ?? $json['details'] ?? ''));

        $ok = $response->successful()
            && $status === 'success'
            && (str_contains($details, 'match') || $details === 'otp matched');

        if ($ok) {
            $otp->forceFill(['verified_at' => now()])->save();
        } else {
            Log::info('2factor verify failed', [
                'phone' => $phone10,
                'session' => $sessionId,
                'payload' => $json,
            ]);
        }

        return $ok;
    }
}
