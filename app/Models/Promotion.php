<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'title',
        'slug',
        'badge',
        'discount_type',
        'discount_value',
        'min_spend',
        'starts_at',
        'ends_at',
        'is_active',
        'banner_image',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'min_spend' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isLive(): bool
    {
        return $this->is_active 
            && $this->starts_at->isPast() 
            && $this->ends_at->isFuture();
    }

    public function scopeLive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
