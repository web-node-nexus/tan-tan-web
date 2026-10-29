<?php

namespace App\Services\Search;

use App\Models\Category;
use App\Models\Listing;
use App\Models\PushToken;
use App\Models\RecentlyViewed;
use App\Models\SavedSearch;
use App\Models\SellerFollow;
use App\Models\User;
use App\Models\UserAlert;
use App\Support\SearchText;
use Illuminate\Support\Facades\Http;
use Throwable;

final class SavedSearchAlertService
{
    public function __construct(private readonly SearchText $text) {}

    public function notifyMatches(Listing $listing): void
    {
        if ($listing->status !== 'active') {
            return;
        }

        $listing->loadMissing('category');

        SavedSearch::query()
            ->with('user')
            ->where('notify', true)
            ->where('user_id', '!=', $listing->user_id)
            ->orderBy('id')
            ->each(function (SavedSearch $search) use ($listing): void {
                if (! $this->matches($listing, $search->filters ?? [])) {
                    return;
                }

                $this->deliver(
                    $search->user_id,
                    $listing,
                    'saved_search',
                    'search:'.$search->id.':'.$listing->id,
                    ($search->user?->locale === 'hi') ? 'आपकी खोज का नया विज्ञापन' : 'New ad for your search',
                    $search->name.': '.$listing->title,
                    $search->id,
                );
            });

        $this->notifyFollowers($listing);
    }

    public function notifyFollowers(Listing $listing): void
    {
        if ($listing->status !== 'active') {
            return;
        }

        SellerFollow::query()
            ->where('seller_id', $listing->user_id)
            ->pluck('follower_id')
            ->each(function (int $followerId) use ($listing): void {
                $this->deliver(
                    $followerId,
                    $listing,
                    'follow',
                    'follow:'.$followerId.':'.$listing->id,
                    'New post from a seller you follow',
                    $listing->title,
                );
            });
    }

    public function notifyPriceDrop(Listing $listing, float $from, float $to): void
    {
        if ($listing->status !== 'active' || $to >= $from) {
            return;
        }

        $price = number_format($to, 2, '.', '');
        RecentlyViewed::query()
            ->where('listing_id', $listing->id)
            ->where('user_id', '!=', $listing->user_id)
            ->pluck('user_id')
            ->each(function (int $userId) use ($listing, $from, $price): void {
                $this->deliver(
                    $userId,
                    $listing,
                    'price_drop',
                    'drop:'.$userId.':'.$listing->id.':'.$price,
                    'Price drop on an ad you viewed',
                    $listing->title.' is now ₹'.$price.' (was ₹'.number_format($from, 0, '.', '').').',
                );
            });
    }

    public function notifyStatus(int $userId, Listing $listing, string $kind, string $dedupe, string $title, string $body): void
    {
        $this->deliver($userId, $listing, $kind, $dedupe, $title, $body);
    }

    private function deliver(int $userId, Listing $listing, string $kind, string $dedupe, string $title, string $body, ?int $savedSearchId = null): void
    {
        $user = User::query()->find($userId);
        if ($user?->locale === 'hi' && $kind === 'follow') {
            $title = 'जिस विक्रेता को आप फॉलो करते हैं, उसकी नई पोस्ट';
        }
        if ($user?->locale === 'hi' && $kind === 'price_drop') {
            $title = 'आपके देखे विज्ञापन पर कीमत घटी';
        }

        $alert = UserAlert::query()->firstOrCreate(
            ['dedupe' => $dedupe],
            [
                'user_id' => $userId,
                'kind' => $kind,
                'saved_search_id' => $savedSearchId,
                'listing_id' => $listing->id,
                'title' => $title,
                'body' => $body,
            ],
        );

        if ($alert->wasRecentlyCreated) {
            $this->push($userId, $alert->title, $alert->body, $listing->id);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function matches(Listing $listing, array $filters): bool
    {
        if (! $this->textMatches($listing, (string) ($filters['q'] ?? ''))) {
            return false;
        }

        if (! empty($filters['category_id'])) {
            $id = (int) $filters['category_id'];
            $ids = Category::query()->where('parent_id', $id)->pluck('id')->push($id);
            if (! $ids->contains($listing->category_id)) {
                return false;
            }
        }

        if (! empty($filters['city_id']) && (int) $listing->city_id !== (int) $filters['city_id']) {
            return false;
        }

        if (! empty($filters['min_price']) && (float) $listing->price < (float) $filters['min_price']) {
            return false;
        }

        if (! empty($filters['max_price']) && (float) $listing->price > (float) $filters['max_price']) {
            return false;
        }

        if (! empty($filters['condition']) && $listing->condition !== $filters['condition']) {
            return false;
        }

        $attributes = is_array($listing->attributes) ? $listing->attributes : [];
        foreach (['brand', 'model', 'fuel', 'bhk', 'purpose', 'furnishing', 'transmission'] as $key) {
            if (empty($filters[$key])) {
                continue;
            }
            if (mb_strtolower((string) ($attributes[$key] ?? '')) !== mb_strtolower((string) $filters[$key])) {
                return false;
            }
        }

        if (! empty($filters['variant'])) {
            $wanted = mb_strtolower((string) $filters['variant']);
            $actual = mb_strtolower((string) ($attributes['variant'] ?? $attributes['storage'] ?? ''));
            if ($actual !== $wanted) {
                return false;
            }
        }

        return true;
    }

    private function textMatches(Listing $listing, string $query): bool
    {
        $groups = $this->text->groups($query);
        if ($groups === []) {
            return true;
        }

        $haystack = mb_strtolower(implode(' ', array_filter([
            $listing->title,
            $listing->description,
            $listing->location_label,
            is_array($listing->attributes) ? implode(' ', array_map(fn ($value): string => is_scalar($value) ? (string) $value : '', $listing->attributes)) : '',
        ])));

        foreach ($groups as $forms) {
            $hit = false;
            foreach ($forms as $form) {
                if (str_contains($haystack, $form)) {
                    $hit = true;
                    break;
                }
            }
            if (! $hit) {
                return false;
            }
        }

        return true;
    }

    private function push(int $userId, string $title, string $body, int $listingId): void
    {
        $tokens = PushToken::query()->where('user_id', $userId)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        try {
            Http::timeout(8)->post('https://exp.host/--/api/v2/push/send', $tokens->map(fn (string $token): array => [
                'to' => $token,
                'title' => $title,
                'body' => $body,
                'data' => ['listing_id' => $listingId],
            ])->values()->all());
        } catch (Throwable) {
            // The in-app alert is already stored.
        }
    }
}
