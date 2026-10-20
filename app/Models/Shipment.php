<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'provider',
        'shiprocket_order_id',
        'shiprocket_shipment_id',
        'awb_code',
        'courier_company_id',
        'courier_name',
        'status',
        'pickup_location',
        'pickup_token',
        'pickup_scheduled_at',
        'label_url',
        'manifest_url',
        'tracking_url',
        'package_weight_kg',
        'actual_cost_paise',
        'customer_shipping_paise',
        'platform_margin_paise',
        'metadata',
    ];

    protected $casts = [
        'pickup_scheduled_at' => 'datetime',
        'package_weight_kg' => 'decimal:3',
        'actual_cost_paise' => 'integer',
        'customer_shipping_paise' => 'integer',
        'platform_margin_paise' => 'integer',
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
