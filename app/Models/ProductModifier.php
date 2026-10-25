<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'group_name',
        'modifier_name',
        'price_delta',
        'is_required',
    ];

    protected $casts = [
        'price_delta' => 'decimal:2',
        'is_required' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get title alias for modifier_name
     */
    public function getTitleAttribute(): string
    {
        return $this->modifier_name ?? '';
    }

    /**
     * Get price alias for price_delta (Rupees)
     */
    public function getPriceAttribute(): float
    {
        return (float) ($this->price_delta ?? 0);
    }

    /**
     * Get price delta in paise for cart integration
     */
    public function getPriceDeltaPaiseAttribute(): int
    {
        return (int) round(((float) $this->price_delta) * 100);
    }
}
