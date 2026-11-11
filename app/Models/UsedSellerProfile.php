<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UsedSellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'handle',
        'display_name',
        'bio',
        'city',
        'locality',
        'preferred_contact_methods',
        'preferred_handoff_methods',
        'show_sold_history',
        'show_phone_publicly',
        'avg_rating',
        'reviews_count',
        'sold_count',
    ];

    protected $casts = [
        'preferred_contact_methods' => 'array',
        'preferred_handoff_methods' => 'array',
        'show_sold_history' => 'boolean',
        'show_phone_publicly' => 'boolean',
        'avg_rating' => 'float',
        'reviews_count' => 'integer',
        'sold_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function getLocationLabelAttribute(): string
    {
        $parts = array_filter([$this->locality, $this->city]);
        return !empty($parts) ? implode(', ', $parts) : 'India';
    }
}

