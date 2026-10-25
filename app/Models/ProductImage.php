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
        return format_image_url($this->path);
    }
}
