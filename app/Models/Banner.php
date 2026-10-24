<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'cta_label',
        'cta_href',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function imageUrl(): string
    {
        $path = (string) $this->image_path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $base = request()?->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');

        // Public folder assets (seeded): /images/...
        if (str_starts_with($path, '/')) {
            return $base.$path;
        }

        // Filament uploads on the public disk: banners/xyz.jpg → /storage/banners/xyz.jpg
        return $base.'/storage/'.ltrim($path, '/');
    }
}
