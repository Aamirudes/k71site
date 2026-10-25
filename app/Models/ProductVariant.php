<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes; // BelongsToVendor is inherited through Product scope

    protected $fillable = [
        'product_id',
        'sku',
        'price_override',
        'stock',
        'is_active',
        'sort_order',
        'attributes_hash',
        'price',
        'compare_at_price',
        'stock_quantity',
        'image_url',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'attributes_hash' => 'array',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'stock' => 'integer',
        'price_override' => 'integer',
    ];

    /**
     * Boot model to automatically keep price <-> price_override and stock <-> stock_quantity in sync.
     */
    protected static function booted()
    {
        static::saving(function ($variant) {
            // Synchronize price (decimal rupees) and price_override (integer paise)
            if ($variant->isDirty('price') && !is_null($variant->price)) {
                $variant->price_override = (int) round(((float) $variant->price) * 100);
            } elseif ($variant->isDirty('price_override') && !is_null($variant->price_override)) {
                $variant->price = round($variant->price_override / 100, 2);
            }

            // Synchronize stock and stock_quantity
            if ($variant->isDirty('stock_quantity') && !is_null($variant->stock_quantity)) {
                $variant->stock = (int) $variant->stock_quantity;
            } elseif ($variant->isDirty('stock') && !is_null($variant->stock)) {
                $variant->stock_quantity = (int) $variant->stock;
            }

            if (empty($variant->status)) {
                $variant->status = $variant->is_active ? 'active' : 'inactive';
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Human-friendly variant title or label
     */
    public function getTitleAttribute(): string
    {
        if (!empty($this->attributes_hash) && is_array($this->attributes_hash)) {
            return implode(' / ', array_values($this->attributes_hash));
        }
        return $this->sku ?: 'Standard';
    }

    /**
     * Get price in rupees attribute with fallback to price_override or product base price
     */
    public function getEffectivePriceAttribute(): float
    {
        if (!is_null($this->price) && (float) $this->price > 0) {
            return (float) $this->price;
        }
        if (!is_null($this->price_override) && (int) $this->price_override > 0) {
            return (float) ($this->price_override / 100);
        }
        return (float) (($this->product?->base_price ?? 0) / 100);
    }

    /**
     * Get effective price in paise
     */
    public function getEffectivePricePaiseAttribute(): int
    {
        if (!is_null($this->price_override) && (int) $this->price_override > 0) {
            return (int) $this->price_override;
        }
        if (!is_null($this->price) && (float) $this->price > 0) {
            return (int) round(((float) $this->price) * 100);
        }
        return (int) ($this->product?->base_price ?? 0);
    }
}

