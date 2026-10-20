<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductGigPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'tier',
        'title',
        'description',
        'delivery_days',
        'revisions',
        'price',
        'features_json',
    ];

    protected $casts = [
        'delivery_days' => 'integer',
        'revisions' => 'integer',
        'price' => 'decimal:2',
        'features_json' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get price in paise for cart integration
     */
    public function getPricePaiseAttribute(): int
    {
        return (int) round(((float) $this->price) * 100);
    }
}
