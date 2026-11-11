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
        'option_group_id',
        'group_name',
        'modifier_name',
        'price_delta',
        'is_required',
        'is_available',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'option_group_id' => 'integer',
        'price_delta' => 'decimal:2',
        'is_required' => 'boolean',
        'is_available' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class, 'option_group_id');
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
