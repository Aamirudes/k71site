<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_restaurant' => 'boolean',
        'is_pure_veg' => 'boolean',
        'cuisine_types' => 'array',
        'cod_enabled' => 'boolean',
        'upi_enabled' => 'boolean',
    ];
}
