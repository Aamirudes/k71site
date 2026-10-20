<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'shop_name',
        'vendor_type',
        'description',
        'logo',
        'banner',
        'contact_email',
        'contact_phone',
        'city',
        'state',
        'pincode',
        'address',
        'phone',
        'email',
        'pickup_address',
        'return_address',
        'gstin',
        'social_links',
        'store_policies',
        'seo_title',
        'seo_description',
        'og_image',
        'colour_palette',
        'layout_preset',
        'product_preset',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'vacation_mode',
        'vacation_message',
        'slug_reserved_at',
        'slug_released_at',
    ];

    protected $casts = [
        'pickup_address' => 'json',
        'return_address' => 'json',
        'social_links' => 'json',
        'approved_at' => 'datetime',
        'slug_reserved_at' => 'datetime',
        'slug_released_at' => 'datetime',
        'vacation_mode' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(VendorSetting::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shorts(): HasMany
    {
        return $this->hasMany(Short::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Product::class)->where('vertical', 'services')->where('status', 'active');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function isServiceProvider(): bool
    {
        return $this->products()->where('vertical', 'services')->exists();
    }

    public function shortBoosts(): HasMany
    {
        return $this->hasMany(ShortBoost::class);
    }

    public function shortFollowers(): HasMany
    {
        return $this->hasMany(ShortFollow::class, 'vendor_id');
    }

    /**
     * Check vendor vertical type.
     */
    public function isClothing(): bool
    {
        return empty($this->vendor_type) || $this->vendor_type === 'clothing';
    }

    public function isFood(): bool
    {
        return $this->vendor_type === 'food';
    }

    public function isServices(): bool
    {
        return $this->vendor_type === 'services';
    }

    public function isUsed(): bool
    {
        return $this->vendor_type === 'used';
    }

    public function isB2B(): bool
    {
        return $this->vendor_type === 'b2b';
    }

    public function getVerticalKey(): string
    {
        return match($this->vendor_type) {
            'food' => 'food',
            'services' => 'services',
            'used' => 'used',
            'b2b' => 'enquiry',
            default => 'marketplace',
        };
    }

    /**
     * Check if vendor operates as a food restaurant or cloud kitchen.
     */
    public function isRestaurant(): bool
    {
        return $this->vendor_type === 'food' 
            || (bool) ($this->settings?->is_restaurant || $this->products()->where('vertical', 'food')->exists());
    }

    /**
     * Check if restaurant is certified Pure Vegetarian.
     */
    public function isPureVeg(): bool
    {
        return (bool) ($this->settings?->is_pure_veg ?? false);
    }

    /**
     * Active food menu items for this restaurant.
     */
    public function foodMenu(): HasMany
    {
        return $this->hasMany(Product::class)->where('vertical', 'food')->where('status', 'active');
    }

    public function getCityAttribute($value): ?string
    {
        return $value ?: ($this->pickup_address['city'] ?? null);
    }

    public function getStorefrontUrlAttribute(): string
    {
        $requestHost = request()->getHost();
        $isIp = in_array($requestHost, ['127.0.0.1', '::1']) || filter_var($requestHost, FILTER_VALIDATE_IP);

        if (config('marketplace.use_subdomains', false) && !$isIp) {
            $rootDomain = k71_root_domain($requestHost);
            $scheme = (app()->environment('production') || request()->secure()) ? 'https' : 'http';
            $port = request()->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';
            return "{$scheme}://{$this->slug}.{$rootDomain}{$portSuffix}";
        }

        return route('storefront.show', $this->slug);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }
}
