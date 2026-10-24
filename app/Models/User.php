<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'bio',
    'locale',
    'theme',
    'is_verified',
    'show_phone',
    'referral_code',
    'referred_by',
    'city_id',
    'latitude',
    'longitude',
    'last_active_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'show_phone' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'last_active_at' => 'datetime',
            'banned_at' => 'datetime',
            'rating_avg' => 'float',
            'rating_count' => 'integer',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->banned_at === null
            && $this->hasAnyRole(['Super Admin', 'Admin', 'Moderator', 'Support']);
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ListingPayment::class);
    }

    public function reportsFiled(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }
}
