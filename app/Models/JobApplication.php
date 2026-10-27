<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    public const STATUSES = ['applied', 'shortlisted', 'interview', 'hired', 'rejected', 'withdrawn'];

    public const EMPLOYER_STATUSES = ['applied', 'shortlisted', 'interview', 'hired', 'rejected'];

    public const INTERVIEW_SLOTS = ['morning', 'afternoon', 'evening'];

    public const EXPERIENCE = ['fresher', '1_2', '3_5', '5_plus'];

    public const EDUCATION = ['tenth', 'twelfth', 'graduate', 'any'];

    public const JOIN = ['immediate', '15_days', '1_month'];

    protected $fillable = [
        'listing_id',
        'user_id',
        'note',
        'experience',
        'education',
        'can_join',
        'expected_salary',
        'status',
        'interview_on',
        'interview_slot',
        'interview_place',
        'interview_reply',
    ];

    protected function casts(): array
    {
        return [
            'interview_on' => 'date',
            'expected_salary' => 'integer',
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
