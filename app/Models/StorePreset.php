<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'key',
        'name',
        'description',
        'tokens',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'tokens' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Scope active presets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type: store_layout, colour_preset, product_layout
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
