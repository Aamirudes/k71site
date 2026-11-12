<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SizeChart extends Model
{
    protected $fillable = [
        'vendor_id',
        'product_id',
        'category_id',
        'title',
        'image',
        'table_data',
    ];
}
