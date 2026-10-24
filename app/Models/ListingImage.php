<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    protected $fillable = [
        'listing_id',
        'path',
        'sort_order',
        'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_cover' => 'boolean',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function url(): string
    {
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        // Prefer the current request host so phones on LAN get reachable image URLs
        // even when APP_URL is still localhost.
        $base = request()?->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');

        if (str_starts_with($this->path, '/')) {
            return $base.$this->path;
        }

        $public = Storage::disk('public')->url($this->path);
        if (str_starts_with($public, 'http://') || str_starts_with($public, 'https://')) {
            return $public;
        }

        return $base.'/'.ltrim($public, '/');
    }
}
