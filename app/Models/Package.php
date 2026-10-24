<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'code',
        'name_en',
        'name_hi',
        'description_en',
        'description_hi',
        'price',
        'duration_days',
        'is_featured_boost',
        'is_best_value',
        'benefits',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured_boost' => 'boolean',
            'is_best_value' => 'boolean',
            'is_active' => 'boolean',
            'benefits' => 'array',
            'duration_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function localizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'hi' ? $this->name_hi : $this->name_en;
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'hi' ? $this->description_hi : $this->description_en;
    }
}
