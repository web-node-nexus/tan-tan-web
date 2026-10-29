<?php

namespace App\Services\Trust;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class FraudCheckService
{
    /** @var list<string> */
    private const BLOCKED = [
        'otp',
        'registration fee',
        'western union',
        'gift card',
        'processing fee',
        'lottery',
        'double your money',
        'verification fee',
        'upi pin',
    ];

    public function assertClean(string $title, string $description): void
    {
        $haystack = mb_strtolower($title.' '.$description);
        foreach (self::BLOCKED as $phrase) {
            if (str_contains($haystack, $phrase)) {
                throw ValidationException::withMessages([
                    'description' => __('api.trust.fraud_blocked'),
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    public function softFlags(User $user, string $title): array
    {
        $flags = [];
        $recent = Listing::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        if ($recent >= 4 && ($user->kyc_status ?? 'none') !== 'approved') {
            $flags[] = 'velocity';
        }

        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $title) ?? $title));
        $duplicate = $normalized !== '' && Listing::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->whereRaw('lower(title) = ?', [$normalized])
            ->exists();
        if ($duplicate) {
            $flags[] = 'duplicate_title';
        }

        return $flags;
    }
}
