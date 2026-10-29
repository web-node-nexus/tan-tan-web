<?php

namespace App\Services\Listings;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\FraudFlag;
use App\Services\Search\SavedSearchAlertService;
use App\Services\Trust\FraudCheckService;
use App\Support\ImageCompressor;
use App\Support\JobAttributes;
use App\Support\ProductAttributes;
use App\Support\SearchText;
use App\Support\ServiceAttributes;
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
            ->with(['category', 'city.state', 'area', 'images', 'user:id,name,is_verified,phone_verified_at,rating_avg,rating_count,show_phone,phone,created_at'])
            ->active()
            ->latest('published_at');

        $textGroups = [];
        if (! empty($filters['q'])) {
            $textGroups = app(SearchText::class)->groups((string) $filters['q']);
            if ($textGroups !== []) {
                $query->where(function ($builder) use ($textGroups): void {
                    foreach ($textGroups as $forms) {
                        $builder->where(function ($inner) use ($forms): void {
                            foreach ($forms as $form) {
                                $like = '%'.$this->escapeLike($form).'%';
                                $inner->orWhere('title', 'like', $like)
                                    ->orWhere('description', 'like', $like)
                                    ->orWhere('location_label', 'like', $like)
                                    ->orWhere('attributes->brand', 'like', $like)
                                    ->orWhere('attributes->model', 'like', $like)
                                    ->orWhere('attributes->variant', 'like', $like);
                            }
                        });
                    }
                });
            }
        }

        foreach (['brand', 'model', 'fuel', 'bhk', 'purpose', 'furnishing', 'transmission'] as $attribute) {
            if (empty($filters[$attribute])) {
                continue;
            }
            $query->whereRaw('lower('.$this->jsonText($attribute).') = ?', [mb_strtolower((string) $filters[$attribute])]);
        }

        if (! empty($filters['variant'])) {
            $variant = mb_strtolower((string) $filters['variant']);
            $query->where(function ($builder) use ($variant): void {
                $builder->whereRaw('lower('.$this->jsonText('variant').') = ?', [$variant])
                    ->orWhereRaw('lower('.$this->jsonText('storage').') = ?', [$variant]);
            });
        }

        if (! empty($filters['negotiable'])) {
            $query->where('is_negotiable', true);
        }

        if (! empty($filters['price_drop'])) {
            $query->whereNotNull('previous_price')->whereColumn('price', '<', 'previous_price');
        }

        if (! empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $ids = Category::query()->where('parent_id', $categoryId)->pluck('id')->push($categoryId)->all();
            $query->whereIn('category_id', $ids);
        }

        if (! empty($filters['kind']) && in_array($filters['kind'], ['product', 'job', 'service'], true)) {
            $query->whereHas('category', fn ($builder) => $builder->where('kind', $filters['kind']));
        }

        if (! empty($filters['work_mode']) && in_array($filters['work_mode'], ['office', 'remote', 'hybrid', 'home'], true)) {
            $query->where('attributes->work_mode', $filters['work_mode']);
        }

        if (! empty($filters['experience']) && in_array($filters['experience'], ['fresher', '1_2', '3_5', '5_plus', 'experienced', '3_plus'], true)) {
            $query->where('attributes->experience', $filters['experience']);
        }

        foreach (['same_day', 'brings_material', 'home_visit', 'available_today'] as $flag) {
            if (empty($filters[$flag])) {
                continue;
            }
            $query->where(function ($builder) use ($flag): void {
                $builder->where('attributes->'.$flag, true)
                    ->orWhere('attributes->'.$flag, 1)
                    ->orWhere('attributes->'.$flag, 'true');
            });
        }

        if (! empty($filters['move_type']) && in_array($filters['move_type'], ['local', 'intercity'], true)) {
            $query->where('attributes->move_type', $filters['move_type']);
        }

        if (! empty($filters['verified'])) {
            $query->whereHas('user', fn ($builder) => $builder->where('is_verified', true));
        }

        if (! empty($filters['city_id'])) {
            $query->where('city_id', (int) $filters['city_id']);
        }

        if (! empty($filters['area_id'])) {
            $query->where('area_id', (int) $filters['area_id']);
        }

        $origin = $this->origin($filters);
        if ($origin) {
            $distanceSql = $this->distanceSql();
            $query->select('listings.*')->selectRaw($distanceSql.' as distance_km', [$origin['lat'], $origin['lng'], $origin['lat']]);
            if (isset($filters['radius_km']) && $filters['radius_km'] !== '') {
                $radius = min(100.0, max(1.0, (float) $filters['radius_km']));
                $latDelta = $radius / 111.045;
                $lngDelta = $radius / (111.045 * max(abs(cos(deg2rad($origin['lat']))), 0.2));
                $query->whereBetween('listings.latitude', [$origin['lat'] - $latDelta, $origin['lat'] + $latDelta])
                    ->whereBetween('listings.longitude', [$origin['lng'] - $lngDelta, $origin['lng'] + $lngDelta])
                    ->whereRaw($distanceSql.' <= ?', [$origin['lat'], $origin['lng'], $origin['lat'], $radius]);
            }
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
            'distance' => $origin
                ? $query->reorder()->orderByRaw('distance_km IS NULL')->orderBy('distance_km')
                : $query->reorder()->latest('published_at'),
            'relevance' => $textGroups !== []
                ? $query->reorder()->orderByRaw($this->relevanceSql($textGroups)['sql'].' desc', $this->relevanceSql($textGroups)['bindings'])->latest('published_at')
                : $query->reorder()->latest('published_at'),
            default => $query->reorder()->latest('published_at'),
        };

        $page = $query->paginate($perPage);

        if (! empty($filters['q']) && ! empty($filters['user_id'])) {
            SearchHistory::query()->create([
                'user_id' => (int) $filters['user_id'],
                'query' => trim((string) $filters['q']),
                'filters' => collect($filters)->only([
                    'category_id', 'city_id', 'area_id', 'min_price', 'max_price', 'condition', 'sort', 'radius_km',
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
    public function create(User $user, array $data, array $images = [], ?int $paymentId = null, ?UploadedFile $video = null): Listing
    {
        app(FraudCheckService::class)->assertClean((string) $data['title'], (string) $data['description']);
        $flags = app(FraudCheckService::class)->softFlags($user, (string) $data['title']);

        $listing = DB::transaction(function () use ($user, $data, $images, $paymentId, $video, $flags): Listing {
            $quota = app(ListingQuotaService::class);
            $slot = $quota->consumeCreditOrFree($user, $paymentId);

            $data = $this->applyPlace($data);
            $category = Category::query()->find($data['category_id']);
            $listing = Listing::query()->create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'city_id' => $data['city_id'] ?? null,
                'area_id' => $data['area_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'posting_fee' => $slot['fee'],
                'is_negotiable' => filter_var($data['is_negotiable'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'condition' => $data['condition'] ?? 'used',
                'attributes' => isset($data['attributes']) && is_array($data['attributes'])
                    ? $this->knownAttributes($category, $data['attributes'])
                    : null,
                'status' => ($data['status'] ?? null) === 'draft' ? 'draft' : 'pending',
                'moderation_note' => $flags === [] ? null : 'Fraud check: '.implode(', ', $flags),
                'location_label' => $data['location_label'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'video_path' => $video instanceof UploadedFile ? $video->store('listings/videos', 'public') : null,
                'published_at' => null,
                'expires_at' => now()->addDays((int) config('services.tantan.ad_expiry_days', 30)),
            ]);

            foreach ($flags as $code) {
                FraudFlag::query()->create([
                    'user_id' => $user->id,
                    'listing_id' => $listing->id,
                    'code' => $code,
                ]);
            }

            $this->storeImages($listing, $images);
            $quota->attachPaymentToListing($listing, $slot['payment']);

            return $listing->load(['category', 'city.state', 'area', 'images', 'user']);
        });

        try {
            app(SavedSearchAlertService::class)->notifyMatches($listing);
        } catch (\Throwable $e) {
            report($e);
        }

        return $listing;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function update(Listing $listing, array $data, array $images = []): Listing
    {
        app(FraudCheckService::class)->assertClean(
            (string) ($data['title'] ?? $listing->title),
            (string) ($data['description'] ?? $listing->description),
        );

        $droppedFrom = null;
        $droppedTo = null;

        $listing = DB::transaction(function () use ($listing, $data, $images, &$droppedFrom, &$droppedTo): Listing {
            $previous = (float) $listing->price;
            if (isset($data['price']) && (float) $data['price'] < $previous) {
                $listing->previous_price = $listing->price;
                $droppedFrom = $previous;
                $droppedTo = (float) $data['price'];
            }

            if (array_key_exists('is_negotiable', $data)) {
                $data['is_negotiable'] = filter_var($data['is_negotiable'], FILTER_VALIDATE_BOOLEAN);
            }

            if (isset($data['attributes']) && is_array($data['attributes'])) {
                $category = Category::query()->find($data['category_id'] ?? $listing->category_id);
                $data['attributes'] = $this->knownAttributes($category, $data['attributes']);
            }

            $placeKeys = ['area_id', 'city_id', 'latitude', 'longitude', 'location_label'];
            if (array_intersect($placeKeys, array_keys($data)) !== []) {
                $data = array_merge($data, $this->applyPlace(array_merge($listing->only($placeKeys), $data)));
            }

            $listing->fill(collect($data)->only([
                'category_id',
                'city_id',
                'area_id',
                'title',
                'description',
                'price',
                'is_negotiable',
                'condition',
                'attributes',
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

            return $listing->fresh(['category', 'city.state', 'area', 'images', 'user']);
        });

        if ($droppedFrom !== null && $droppedTo !== null && $listing->status === 'active') {
            try {
                app(SavedSearchAlertService::class)->notifyPriceDrop($listing, $droppedFrom, $droppedTo);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $listing;
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

            $path = app(ImageCompressor::class)->store($file, 'listings/'.$listing->id);

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

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function knownAttributes(?Category $category, array $attributes): array
    {
        return match ($category?->kind) {
            'job' => JobAttributes::onlyKnown($category, $attributes),
            'service' => ServiceAttributes::onlyKnown($category, $attributes),
            default => ProductAttributes::onlyKnown($category, $attributes),
        };
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    private function jsonText(string $key): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "json_extract(attributes, '$.{$key}')"
            : "JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.{$key}'))";
    }

    /**
     * @param  list<list<string>>  $groups
     * @return array{sql: string, bindings: list<string>}
     */
    private function relevanceSql(array $groups): array
    {
        $parts = [];
        $bindings = [];
        foreach ($groups as $forms) {
            $form = $forms[array_key_last($forms)] ?? $forms[0];
            $like = '%'.$this->escapeLike($form).'%';
            $parts[] = 'CASE WHEN title LIKE ? THEN 8 ELSE 0 END';
            $parts[] = 'CASE WHEN description LIKE ? THEN 2 ELSE 0 END';
            $bindings[] = $like;
            $bindings[] = $like;
        }

        return [
            'sql' => '('.implode(' + ', $parts).')',
            'bindings' => $bindings,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{lat: float, lng: float}|null
     */
    private function origin(array $filters): ?array
    {
        if (! isset($filters['lat'], $filters['lng']) || $filters['lat'] === '' || $filters['lng'] === '') {
            return null;
        }
        if (! is_numeric($filters['lat']) || ! is_numeric($filters['lng'])) {
            return null;
        }

        $lat = (float) $filters['lat'];
        $lng = (float) $filters['lng'];
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

    private function distanceSql(): string
    {
        $sqlite = DB::connection()->getDriverName() === 'sqlite';
        $clamp = $sqlite ? 'min' : 'least';
        $floor = $sqlite ? 'max' : 'greatest';

        return '(6371 * acos('.$clamp.'(1, '.$floor.'(-1, cos(radians(?)) * cos(radians(listings.latitude)) * cos(radians(listings.longitude) - radians(?)) + sin(radians(?)) * sin(radians(listings.latitude))))))';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyPlace(array $data): array
    {
        $area = ! empty($data['area_id']) ? Area::query()->find($data['area_id']) : null;
        if ($area) {
            $data['city_id'] = $data['city_id'] ?? $area->city_id;
            $data['latitude'] = $data['latitude'] ?? $area->latitude;
            $data['longitude'] = $data['longitude'] ?? $area->longitude;
            if (empty($data['location_label'])) {
                $data['location_label'] = $area->name;
            }
        }

        if ((empty($data['latitude']) || empty($data['longitude'])) && ! empty($data['city_id'])) {
            $city = City::query()->find($data['city_id']);
            $data['latitude'] = $data['latitude'] ?? $city?->latitude;
            $data['longitude'] = $data['longitude'] ?? $city?->longitude;
            if (empty($data['location_label'])) {
                $data['location_label'] = $city?->name;
            }
        }

        return $data;
    }
}
