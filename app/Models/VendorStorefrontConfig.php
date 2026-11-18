<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorStorefrontConfig extends Model
{
    use HasFactory;

    protected $table = 'vendor_storefront_configs';

    protected $fillable = [
        'vendor_id',
        'preset_key',
        'draft_theme_id',
        'published_theme_id',
        'published_theme_version',
        'schema_version',
        'draft_config',
        'published_config',
        'is_published',
        'last_saved_at',
        'last_published_at',
    ];

    protected $casts = [
        'schema_version' => 'integer',
        'draft_config' => 'array',
        'published_config' => 'array',
        'is_published' => 'boolean',
        'last_saved_at' => 'datetime',
        'last_published_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get active configuration for display.
     * Strict separation: public storefront ($preview=false) NEVER leaks unpublished draft_config.
     */
    public function getEffectiveConfig(bool $preview = false): array
    {
        if ($preview && !empty($this->draft_config)) {
            return $this->draft_config;
        }

        if (!empty($this->published_config)) {
            return $this->published_config;
        }

        return [];
    }
}

