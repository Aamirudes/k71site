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
        'tagline',
        'description',
        'version',
        'is_legacy',
        'is_free',
        'implementation_status',
        'is_visible_in_catalogue',
        'is_available_for_request',
        'price_paise',
        'currency',
        'preview_thumbnail',
        'sort_order',
        'manifest_overrides',
    ];

    protected $casts = [
        'is_legacy' => 'boolean',
        'is_free' => 'boolean',
        'is_visible_in_catalogue' => 'boolean',
        'is_available_for_request' => 'boolean',
        'price_paise' => 'integer',
        'sort_order' => 'integer',
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

    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free) {
            return 'Included';
        }
        if ($this->price_paise === null) {
            return 'Price on Request';
        }
        if ($this->price_paise === 0) {
            return '₹0';
        }
        return '₹' . number_format($this->price_paise / 100, $this->price_paise % 100 === 0 ? 0 : 2);
    }

    public function isReady(): bool
    {
        return $this->implementation_status === 'ready';
    }

    public function canBeRequested(): bool
    {
        return $this->isReady()
            && $this->is_visible_in_catalogue
            && $this->is_available_for_request
            && !$this->is_free;
    }
}
