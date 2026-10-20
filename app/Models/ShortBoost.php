<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortBoost extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_id',
        'vendor_id',
        'boost_type',
        'coins_spent',
        'starts_at',
        'ends_at',
        'status',
        'impressions',
        'clicks',
    ];

    protected $casts = [
        'coins_spent' => 'integer',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function short(): BelongsTo
    {
        return $this->belongsTo(Short::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('ends_at', '>', now());
    }
}
