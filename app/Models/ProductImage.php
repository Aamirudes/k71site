<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'path',
        'paths_responsive',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'paths_responsive' => 'json',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the publicly accessible URL for the image
     */
    public function getUrlAttribute(): string
    {
        if (empty($this->path)) {
            return asset('images/product-placeholder.svg');
        }

        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return asset('storage/' . ltrim($this->path, '/'));
    }
}
