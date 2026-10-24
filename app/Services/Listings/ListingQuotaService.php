<?php

namespace App\Services\Listings;

use App\Models\Listing;
use App\Models\ListingPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ListingQuotaService
{
    public function freeLimit(): int
    {
        return max(0, (int) config('services.tantan.free_ad_limit', 2));
    }

    public function extraAdFee(): float
    {
        return round((float) config('services.tantan.extra_ad_fee', 9), 2);
    }

    public function postedCount(User $user): int
    {
        return Listing::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['rejected', 'blocked'])
            ->count();
    }

    /**
     * @return array<string, mixed>
     */
    public function quotaFor(User $user): array
    {
        $limit = $this->freeLimit();
        $used = $this->postedCount($user);
        $freeRemaining = max(0, $limit - $used);
        $fee = $this->extraAdFee();
        $requiresPayment = $freeRemaining <= 0;
        $unusedCredits = ListingPayment::query()
            ->where('user_id', $user->id)
            ->where('purpose', 'extra_ad')
            ->where('status', 'paid')
            ->whereNull('consumed_at')
            ->whereNull('listing_id')
            ->count();

        return [
            'free_limit' => $limit,
            'posted_count' => $used,
            'free_remaining' => $freeRemaining,
            'extra_ad_fee' => $fee,
            'currency' => 'INR',
            'requires_payment' => $requiresPayment && $unusedCredits < 1,
            'has_unused_credit' => $unusedCredits > 0,
            'unused_credits' => $unusedCredits,
            'next_slot_label' => $requiresPayment
                ? 'Paid slot · ₹'.number_format($fee, 0)
                : 'Free slot · '.($freeRemaining).' left',
        ];
    }

    public function purchaseExtraAdCredit(User $user): ListingPayment
    {
        return ListingPayment::query()->create([
            'user_id' => $user->id,
            'purpose' => 'extra_ad',
            'amount' => $this->extraAdFee(),
            'currency' => 'INR',
            'status' => 'paid',
            'provider' => 'demo',
            'provider_ref' => 'demo_'.uniqid(),
            'paid_at' => now(),
        ]);
    }

    public function consumeCreditOrFree(User $user, ?int $paymentId = null): array
    {
        $quota = $this->quotaFor($user);

        if ($quota['free_remaining'] > 0) {
            return ['fee' => 0.0, 'payment' => null];
        }

        $payment = null;
        if ($paymentId) {
            $payment = ListingPayment::query()
                ->whereKey($paymentId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (! $payment) {
            $payment = ListingPayment::query()
                ->where('user_id', $user->id)
                ->where('purpose', 'extra_ad')
                ->where('status', 'paid')
                ->whereNull('consumed_at')
                ->whereNull('listing_id')
                ->latest('id')
                ->first();
        }

        if (! $payment || ! $payment->isUsableForExtraAd()) {
            throw ValidationException::withMessages([
                'payment' => [
                    __('api.listings.payment_required', [
                        'fee' => number_format($this->extraAdFee(), 0),
                    ]),
                ],
            ]);
        }

        return ['fee' => (float) $payment->amount, 'payment' => $payment];
    }

    public function attachPaymentToListing(Listing $listing, ?ListingPayment $payment): void
    {
        if (! $payment) {
            return;
        }

        DB::transaction(function () use ($listing, $payment): void {
            $payment->forceFill([
                'listing_id' => $listing->id,
                'status' => 'consumed',
                'consumed_at' => now(),
            ])->save();

            $listing->forceFill([
                'posting_fee' => $payment->amount,
                'posting_payment_id' => $payment->id,
            ])->save();
        });
    }
}
