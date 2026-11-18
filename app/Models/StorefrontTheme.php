<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorefrontTheme extends Model
{
    protected $table = 'storefront_themes';

    protected $fillable = [
        'theme_id',
        'slug',
        'name',
        'category',
        'compatible_categories',
        'design_attributes',
        'tagline',
        'description',
        'version',
        'is_legacy',
        'is_free',
        'implementation_status',
        'qa_verified',
        'is_visible_in_catalogue',
        'is_available_for_request',
        'is_featured',
        'is_preview_enabled',
        'price_paise',
        'currency',
        'preview_thumbnail',
        'sort_order',
        'manifest_overrides',
    ];

    protected $casts = [
        'is_legacy' => 'boolean',
        'is_free' => 'boolean',
        'qa_verified' => 'boolean',
        'is_visible_in_catalogue' => 'boolean',
        'is_available_for_request' => 'boolean',
        'is_featured' => 'boolean',
        'is_preview_enabled' => 'boolean',
        'price_paise' => 'integer',
        'sort_order' => 'integer',
        'compatible_categories' => 'array',
        'design_attributes' => 'array',
        'manifest_overrides' => 'array',
    ];

    public function entitlements(): HasMany
    {
        return $this->hasMany(VendorThemeEntitlement::class, 'theme_id', 'theme_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(VendorThemeRequest::class, 'theme_id', 'theme_id');
    }

    /**
     * Pricing rule: A missing or invalid price NEVER silently makes a theme free.
     * A price of 0 is free ONLY when is_free is intentionally enabled by an administrator.
     */
    public function isIntentionallyFree(): bool
    {
        return (bool) $this->is_free;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->isIntentionallyFree()) {
            return 'Included';
        }
        if ($this->price_paise === null) {
            return 'Price on Request';
        }
        $symbol = ($this->currency === 'USD') ? '$' : '₹';
        return $symbol . number_format($this->price_paise / 100, $this->price_paise % 100 === 0 ? 0 : 2);
    }

    public function isReady(): bool
    {
        return $this->implementation_status === 'ready';
    }

    public function canBePreviewed(): bool
    {
        return $this->is_preview_enabled && $this->implementation_status !== 'deprecated';
    }

    public function canBeRequested(): bool
    {
        return $this->isReady()
            && $this->is_visible_in_catalogue
            && $this->is_available_for_request
            && !$this->isIntentionallyFree();
    }
}
