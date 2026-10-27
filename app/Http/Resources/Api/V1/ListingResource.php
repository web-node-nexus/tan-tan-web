<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Listing */
class ListingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isFavourited = false;
        if ($user && $this->relationLoaded('favourites')) {
            $isFavourited = $this->favourites->contains('user_id', $user->id);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'posting_fee' => (float) ($this->posting_fee ?? 0),
            'previous_price' => $this->previous_price !== null ? (float) $this->previous_price : null,
            'price_dropped' => $this->previous_price !== null && (float) $this->price < (float) $this->previous_price,
            'is_negotiable' => (bool) ($this->is_negotiable ?? true),
            'currency' => $this->currency,
            'condition' => $this->condition,
            'attributes' => $this->attributes ?: null,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_reserved' => $this->reserved_at !== null,
            'location_label' => $this->location_label ?? $this->city?->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'views_count' => $this->views_count,
            'favourites_count' => (int) ($this->favourites_count ?? 0),
            'chats_count' => (int) ($this->chats_count ?? 0),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'cover_url' => $this->coverUrl(),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url(),
                'is_cover' => $img->is_cover,
                'sort_order' => $img->sort_order,
            ])->values()),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->localizedName(),
                'slug' => $this->category->slug,
                'kind' => $this->category->kind ?? 'product',
                'tint' => $this->category->tint,
                'icon' => $this->category->icon,
            ]),
            'city' => $this->whenLoaded('city', fn () => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->name,
                'state' => $this->city->state?->name,
            ] : null),
            'seller' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'is_verified' => (bool) $this->user->is_verified,
                'phone' => $this->user->show_phone ? $this->user->phone : null,
                'member_since' => $this->user->created_at?->toDateString(),
                'rating_avg' => (float) ($this->user->rating_avg ?? 0),
                'rating_count' => (int) ($this->user->rating_count ?? 0),
            ]),
            'featured_until' => $this->featured_until?->toIso8601String(),
            'is_favourited' => $isFavourited,
            'is_mine' => $user?->id === $this->user_id,
            'has_applied' => $this->when(
                $request->routeIs('api.v1.listings.show'),
                fn () => $user ? $this->applications()->where('user_id', $user->id)->exists() : false,
            ),
        ];
    }

    private function coverUrl(): ?string
    {
        if ($this->relationLoaded('images')) {
            $cover = $this->images->firstWhere('is_cover', true) ?? $this->images->first();

            return $cover?->url();
        }

        $path = $this->coverPath();
        if (! $path) {
            return null;
        }

        $image = new \App\Models\ListingImage(['path' => $path]);

        return $image->url();
    }
}
