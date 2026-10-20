<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'gig_package_id',
        'selected_modifiers_json',
        'booking_date_time',
        'vendor_id',
        'quantity',
        'price_at_add',
    ];

    protected $casts = [
        'selected_modifiers_json' => 'array',
        'booking_date_time' => 'datetime',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id')->withTrashed();
    }

    public function gigPackage(): BelongsTo
    {
        return $this->belongsTo(ProductGigPackage::class, 'gig_package_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
