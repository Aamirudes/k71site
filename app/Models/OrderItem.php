<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'gig_package_id',
        'selected_modifiers_json',
        'booking_date_time',
        'quantity',
        'unit_price',
        'total_price',
        'tax_amount',
        'gst_rate',
        'hsn_code',
        'snapshot',
        'meta_json',
    ];

    protected $casts = [
        'snapshot' => 'json',
        'meta_json' => 'json',
        'selected_modifiers_json' => 'array',
        'booking_date_time' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
}
