<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPayment extends Model
{
    protected $fillable = [
        'user_id',
        'listing_id',
        'purpose',
        'amount',
        'currency',
        'status',
        'provider',
        'provider_ref',
        'paid_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function isUsableForExtraAd(): bool
    {
        return $this->purpose === 'extra_ad'
            && $this->status === 'paid'
            && $this->consumed_at === null
            && $this->listing_id === null;
    }
}
