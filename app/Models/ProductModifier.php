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
     * Get price delta in paise for cart integration
     */
    public function getPriceDeltaPaiseAttribute(): int
    {
        return (int) round(((float) $this->price_delta) * 100);
    }
}
