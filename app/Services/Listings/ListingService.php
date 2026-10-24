<?php

namespace App\Services\Listings;

use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\RecentlyViewed;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ListingService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Listing::query()
            ->with(['category', 'city.state', 'images', 'user:id,name,is_verified,phone_verified_at,rating_avg,rating_count,show_phone,phone,created_at'])
            ->active()
            ->latest('published_at');

        if (! empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $like = '%'.$q.'%';
            $query->where(function ($builder) use ($like): void {
                $builder->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        if (! empty($filters['negotiable'])) {
            $query->where('is_negotiable', true);
        }

        if (! empty($filters['price_drop'])) {
            $query->whereNotNull('previous_price')->whereColumn('price', '<', 'previous_price');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['city_id'])) {
            $query->where('city_id', (int) $filters['city_id']);
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        $sort = (string) ($filters['sort'] ?? 'newest');
        match ($sort) {
            'price_asc' => $query->reorder()->orderBy('price'),
            'price_desc' => $query->reorder()->orderByDesc('price'),
            'featured' => $query->reorder()->orderByDesc('is_featured')->latest('published_at'),
            default => $query->reorder()->latest('published_at'),
        };

        $page = $query->paginate($perPage);

        if (! empty($filters['q']) && ! empty($filters['user_id'])) {
            SearchHistory::query()->create([
                'user_id' => (int) $filters['user_id'],
                'query' => trim((string) $filters['q']),
                'filters' => collect($filters)->only([
                    'category_id', 'city_id', 'min_price', 'max_price', 'condition', 'sort',
                ])->all(),
                'results_count' => $page->total(),
            ]);
        }

        return $page;
    }

    public function mine(User $user, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Listing::query()
            ->with(['category', 'city', 'images'])
            ->where('user_id', $user->id)
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function create(User $user, array $data, array $images = [], ?int $paymentId = null): Listing
    {
        return DB::transaction(function () use ($user, $data, $images, $paymentId): Listing {
            $quota = app(ListingQuotaService::class);
            $slot = $quota->consumeCreditOrFree($user, $paymentId);

            $listing = Listing::query()->create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'city_id' => $data['city_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'posting_fee' => $slot['fee'],
                'is_negotiable' => (bool) ($data['is_negotiable'] ?? true),
                'condition' => $data['condition'] ?? 'used',
                'status' => $data['status'] ?? 'active',
                'location_label' => $data['location_label'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'published_at' => now(),
                'expires_at' => now()->addDays((int) config('services.tantan.ad_expiry_days', 30)),
            ]);

            $this->storeImages($listing, $images);
            $quota->attachPaymentToListing($listing, $slot['payment']);

            return $listing->load(['category', 'city.state', 'images', 'user']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function update(Listing $listing, array $data, array $images = []): Listing
    {
        return DB::transaction(function () use ($listing, $data, $images): Listing {
            if (isset($data['price']) && (float) $data['price'] < (float) $listing->price) {
                $listing->previous_price = $listing->price;
            }

            $listing->fill(collect($data)->only([
                'category_id',
                'city_id',
                'title',
                'description',
                'price',
                'is_negotiable',
                'condition',
                'status',
                'location_label',
                'latitude',
                'longitude',
            ])->all());

            if (($data['status'] ?? null) === 'sold' && $listing->sold_at === null) {
                $listing->sold_at = now();
            }

            $listing->save();

            if ($images !== []) {
                $this->storeImages($listing, $images);
            }

            return $listing->fresh(['category', 'city.state', 'images', 'user']);
        });
    }

    public function markSold(Listing $listing): Listing
    {
        $listing->forceFill([
            'status' => 'sold',
            'sold_at' => now(),
        ])->save();

        return $listing->fresh(['category', 'city', 'images', 'user']);
    }

    public function incrementViews(Listing $listing, ?User $viewer = null): void
    {
        $listing->increment('views_count');

        if ($viewer && $viewer->id !== $listing->user_id) {
            RecentlyViewed::query()->updateOrCreate(
                ['user_id' => $viewer->id, 'listing_id' => $listing->id],
                ['viewed_at' => now()],
            );
        }
    }

    public function recentlyViewed(User $user, int $perPage = 20): LengthAwarePaginator
    {
        $ids = RecentlyViewed::query()
            ->where('user_id', $user->id)
            ->orderByDesc('viewed_at')
            ->limit(100)
            ->pluck('listing_id')
            ->values();

        $page = max(1, (int) request()->integer('page', 1));
        $slice = $ids->forPage($page, $perPage)->values();

        $items = Listing::query()
            ->with(['category', 'city.state', 'images', 'user'])
            ->whereIn('id', $slice)
            ->active()
            ->get()
            ->sortBy(fn (Listing $listing) => $slice->search($listing->id))
            ->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $ids->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    public function renew(Listing $listing): Listing
    {
        $listing->forceFill([
            'status' => 'active',
            'published_at' => now(),
            'expires_at' => now()->addDays((int) config('services.tantan.ad_expiry_days', 30)),
        ])->save();

        return $listing->fresh(['category', 'city', 'images', 'user']);
    }

    public function reserve(Listing $listing, bool $reserved = true): Listing
    {
        $listing->forceFill([
            'reserved_at' => $reserved ? now() : null,
        ])->save();

        return $listing->fresh(['category', 'city', 'images', 'user']);
    }

    public function insights(Listing $listing): array
    {
        return [
            'views_count' => (int) $listing->views_count,
            'favourites_count' => (int) $listing->favourites_count,
            'chats_count' => (int) $listing->chats_count,
            'is_featured' => (bool) $listing->is_featured,
            'featured_until' => $listing->featured_until?->toIso8601String(),
            'published_at' => $listing->published_at?->toIso8601String(),
            'expires_at' => $listing->expires_at?->toIso8601String(),
            'reserved_at' => $listing->reserved_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    private function storeImages(Listing $listing, array $images): void
    {
        $start = (int) $listing->images()->max('sort_order');
        $hasCover = $listing->images()->where('is_cover', true)->exists();

        foreach (array_values($images) as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('listings/'.$listing->id, 'public');

            ListingImage::query()->create([
                'listing_id' => $listing->id,
                'path' => $path,
                'sort_order' => $start + $index + 1,
                'is_cover' => ! $hasCover && $index === 0,
            ]);

            if (! $hasCover && $index === 0) {
                $hasCover = true;
            }
        }
    }

    public function delete(Listing $listing): void
    {
        DB::transaction(function () use ($listing): void {
            foreach ($listing->images as $image) {
                if (! str_starts_with($image->path, '/') && ! str_starts_with($image->path, 'http')) {
                    Storage::disk('public')->delete($image->path);
                }
            }
            $listing->delete();
        });
    }

    public function toggleFavourite(User $user, Listing $listing): bool
    {
        $existing = $user->favourites()->where('listing_id', $listing->id)->first();
        if ($existing) {
            $existing->delete();
            if ($listing->favourites_count > 0) {
                $listing->decrement('favourites_count');
            }

            return false;
        }

        $user->favourites()->create(['listing_id' => $listing->id]);
        $listing->increment('favourites_count');

        return true;
    }
}
