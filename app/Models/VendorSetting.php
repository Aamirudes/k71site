<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_restaurant' => 'boolean',
        'is_pure_veg' => 'boolean',
        'is_accepting_food_orders' => 'boolean',
        'enforce_operating_hours' => 'boolean',
        'cuisine_types' => 'array',
        'weekly_hours_json' => 'array',
        'service_cities_json' => 'array',
        'cod_enabled' => 'boolean',
        'upi_enabled' => 'boolean',
        'fssai_verified' => 'boolean',
        'fssai_verified_at' => 'datetime',
        'allow_customer_cancellation' => 'boolean',
        'food_tax_percent' => 'float',
        'food_avg_rating' => 'float',
        'food_review_count' => 'integer',
        'food_packaging_fee' => 'integer',
        'food_free_delivery_above' => 'integer',
        'food_delivery_fee' => 'integer',
        'min_food_order_amount' => 'integer',
        'avg_prep_time_mins' => 'integer',
    ];
}
