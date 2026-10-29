<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Listing extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'pending', 'active', 'sold', 'expired', 'rejected', 'blocked'];

    public const CONDITIONS = ['new', 'like_new', 'used', 'for_parts'];

    protected $fillable = [
        'user_id',
        'category_id',
        'city_id',
        'area_id',
        'title',
        'slug',
        'description',
        'price',
        'posting_fee',
        'posting_payment_id',
        'previous_price',
        'is_negotiable',
        'currency',
        'condition',
        'attributes',
        'status',
        'moderation_note',
        'moderated_at',
        'is_featured',
        'featured_until',
        'location_label',
        'video_path',
        'latitude',
        'longitude',
        'views_count',
        'favourites_count',
        'chats_count',
        'published_at',
        'expires_at',
        'sold_at',
        'reserved_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'posting_fee' => 'decimal:2',
            'previous_price' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'attributes' => 'array',
            'is_featured' => 'boolean',
            'featured_until' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'views_count' => 'integer',
            'favourites_count' => 'integer',
            'chats_count' => 'integer',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'sold_at' => 'datetime',
            'reserved_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing): void {
            if (blank($listing->slug)) {
                $base = Str::slug(Str::limit($listing->title, 60, ''));
                $listing->slug = $base.'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(ListingPromotion::class);
    }

    public function priceLabel(): string
    {
        $amount = '₹'.number_format((float) $this->price, 0);

        return match ($this->category?->kind) {
            'job' => $amount.' / month',
            'service' => 'From '.$amount,
            default => $amount,
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function coverPath(): ?string
    {
        $cover = $this->images->firstWhere('is_cover', true) ?? $this->images->first();

        return $cover?->path;
    }
}
