<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class K71Sector extends Model
{
    protected $table = 'k71_sectors';

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
        'supports_commissions',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'supports_commissions' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];
}
