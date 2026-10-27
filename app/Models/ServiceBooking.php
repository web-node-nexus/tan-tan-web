<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceBooking extends Model
{
    public const SLOTS = ['morning', 'afternoon', 'evening', 'full_day'];

    public const STATUSES = ['requested', 'accepted', 'declined', 'done', 'cancelled'];

    public const PROVIDER_STATUSES = ['requested', 'accepted', 'declined', 'done'];

    protected $fillable = [
        'listing_id',
        'user_id',
        'visit_on',
        'slot',
        'note',
        'details',
        'quoted_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'visit_on' => 'date',
            'details' => 'array',
            'quoted_amount' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
