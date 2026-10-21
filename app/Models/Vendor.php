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
        return empty($this->vendor_type) || in_array($this->vendor_type, ['clothing', 'footwear']);
    }

    public function isFood(): bool
    {
        return $this->vendor_type === 'food';
    }

    public function isServices(): bool
    {
        return in_array($this->vendor_type, ['services', 'local_services']);
    }

    public function isUsed(): bool
    {
        return $this->vendor_type === 'used';
    }

    public function isB2B(): bool
    {
        return in_array($this->vendor_type, ['b2b', 'machinery']);
    }

    public function isElectronics(): bool
    {
        return $this->vendor_type === 'electronics';
    }

    public function isAutomotive(): bool
    {
        return $this->vendor_type === 'automotive';
    }

    public function isBeauty(): bool
    {
        return $this->vendor_type === 'beauty';
    }

    public function isHomeLiving(): bool
    {
        return $this->vendor_type === 'home_living';
    }

    public function isGroceries(): bool
    {
        return $this->vendor_type === 'groceries';
    }

    public function isToysSports(): bool
    {
        return $this->vendor_type === 'toys_sports';
    }

    public function isDigitalGigs(): bool
    {
        return $this->vendor_type === 'digital_services';
    }

    public function isRealEstate(): bool
    {
        return $this->vendor_type === 'real_estate';
    }

    public function getSectorConfig(): array
    {
        $type = $this->vendor_type ?: 'clothing';

        $sectors = [
            'electronics' => [
                'key' => 'electronics',
                'label' => 'Electronics & IT',
                'icon' => '📱',
                'color' => 'from-blue-600 to-cyan-600',
                'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                'catalog_name' => 'Devices & Electronics Catalog',
                'item_singular' => 'Device / IT Product',
                'add_btn' => 'Add Tech Product',
                'specs_label' => 'Technical Specs (RAM, Storage, Processor, Warranty)',
            ],
            'automotive' => [
                'key' => 'automotive',
                'label' => 'Automotive & Spares',
                'icon' => '🚗',
                'color' => 'from-red-600 to-amber-600',
                'badge' => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
                'catalog_name' => 'Auto Parts & Spares Catalog',
                'item_singular' => 'Auto Part / Accessory',
                'add_btn' => 'Add Spare Part',
                'specs_label' => 'Vehicle Compatibility & Fitment Specs',
            ],
            'beauty' => [
                'key' => 'beauty',
                'label' => 'Beauty & Personal Care',
                'icon' => '💄',
                'color' => 'from-pink-500 to-rose-600',
                'badge' => 'bg-pink-500/10 text-pink-600 dark:text-pink-400 border-pink-500/20',
                'catalog_name' => 'Beauty & Wellness Catalog',
                'item_singular' => 'Beauty Product',
                'add_btn' => 'Add Beauty Item',
                'specs_label' => 'Skin Type, Ingredients & Volume',
            ],
            'home_living' => [
                'key' => 'home_living',
                'label' => 'Home & Living',
                'icon' => '🏠',
                'color' => 'from-amber-600 to-yellow-600',
                'badge' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                'catalog_name' => 'Furniture & Home Decor',
                'item_singular' => 'Furniture / Decor Item',
                'add_btn' => 'Add Home Product',
                'specs_label' => 'Wood / Material, Dimensions & Assembly',
            ],
            'groceries' => [
                'key' => 'groceries',
                'label' => 'Groceries & FMCG',
                'icon' => '🛒',
                'color' => 'from-lime-600 to-emerald-600',
                'badge' => 'bg-lime-500/10 text-lime-600 dark:text-lime-400 border-lime-500/20',
                'catalog_name' => 'Staples & FMCG Inventory',
                'item_singular' => 'Grocery Item',
                'add_btn' => 'Add Grocery Item',
                'specs_label' => 'Net Weight, Shelf Life & Organic Cert',
            ],
            'toys_sports' => [
                'key' => 'toys_sports',
                'label' => 'Toys, Books & Sports',
                'icon' => '🧸',
                'color' => 'from-cyan-600 to-teal-600',
                'badge' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20',
                'catalog_name' => 'Toys, Sports & Books Catalog',
                'item_singular' => 'Book / Gear / Toy',
                'add_btn' => 'Add Item',
                'specs_label' => 'Age Group, Material & Weight',
            ],
            'digital_services' => [
                'key' => 'digital_services',
                'label' => 'Digital Gigs & Freelance',
                'icon' => '💼',
                'color' => 'from-violet-600 to-indigo-600',
                'badge' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20',
                'catalog_name' => 'Digital Gigs & Deliverables',
                'item_singular' => 'Gig / Service Package',
                'add_btn' => 'Add Gig Package',
                'specs_label' => 'Turnaround Days, Deliverables & Revisions',
            ],
            'real_estate' => [
                'key' => 'real_estate',
                'label' => 'Real Estate & Assets',
                'icon' => '🏡',
                'color' => 'from-emerald-700 to-teal-800',
                'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                'catalog_name' => 'Property & Asset Listings',
                'item_singular' => 'Property / Floor / Land',
                'add_btn' => 'Add Property Listing',
                'specs_label' => 'Carpet Area, BHK, Location & RERA Reg',
            ],
            'machinery' => [
                'key' => 'machinery',
                'label' => 'Machinery & B2B RFQ',
                'icon' => '🛠️',
                'color' => 'from-purple-600 to-indigo-700',
                'badge' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                'catalog_name' => 'Industrial Machinery & Materials',
                'item_singular' => 'Industrial Machine / Slabs',
                'add_btn' => 'Add Industrial Product',
                'specs_label' => 'Technical Capacity, Voltage & MOQ',
            ],
            'food' => [
                'key' => 'food',
                'label' => 'Cloud Kitchen & Menu',
                'icon' => '🍽️',
                'color' => 'from-amber-500 to-orange-600',
                'badge' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
                'catalog_name' => 'Digital Menu & Kitchen Dishes',
                'item_singular' => 'Dish / Meal',
                'add_btn' => 'Add Dish',
                'specs_label' => 'Portion Size, Dietary & Prep Time',
            ],
            'services' => [
                'key' => 'services',
                'label' => 'Doorstep & Pro Services',
                'icon' => '🔧',
                'color' => 'from-blue-500 to-indigo-600',
                'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                'catalog_name' => 'Service Packages & Bookings',
                'item_singular' => 'Service Package',
                'add_btn' => 'Add Service',
                'specs_label' => 'Service Duration, Tools & Radius',
            ],
            'used' => [
                'key' => 'used',
                'label' => 'Pre-Owned & Refurbished',
                'icon' => '♻️',
                'color' => 'from-emerald-500 to-teal-600',
                'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                'catalog_name' => 'Pre-Owned Inventory & Bids',
                'item_singular' => 'Pre-Owned Item',
                'add_btn' => 'Add Pre-Owned Item',
                'specs_label' => 'Item Condition, Warranty & Age',
            ],
            'clothing' => [
                'key' => 'clothing',
                'label' => 'Fashion & Apparel',
                'icon' => '👗',
                'color' => 'from-rose-500 to-pink-600',
                'badge' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                'catalog_name' => 'Fashion & Apparel Catalog',
                'item_singular' => 'Garment / Footwear',
                'add_btn' => 'Add Fashion Product',
                'specs_label' => 'Fabric, Fit, Sizes & Care Instructions',
            ],
        ];

        $config = $sectors[$type] ?? $sectors['clothing'];
        $config['name'] = $config['name'] ?? ($config['label'] ?? 'Marketplace Store');
        $config['orders_name'] = match($config['key'] ?? '') {
            'food' => 'Kitchen Orders & Live Delivery Stream',
            'services' => 'Service Appointments & Bookings',
            'real_estate' => 'Client Inquiries & Property Visits',
            'digital_services' => 'Project Briefs & Milestones',
            default => 'Customer Orders & Shipments',
        };

        return $config;
    }

    public function getVerticalKey(): string
    {
        if ($this->vendor_type) {
            return match($this->vendor_type) {
                'food' => 'food',
                'services', 'local_services' => 'services',
                'used' => 'used',
                'machinery', 'b2b' => 'enquiry',
                'real_estate' => 'real_estate',
                'digital_services' => 'digital_services',
                'electronics' => 'electronics',
                'automotive' => 'automotive',
                'home_living' => 'home_living',
                'beauty' => 'beauty',
                'groceries' => 'groceries',
                'toys_sports' => 'toys_sports',
                'clothing', 'footwear' => 'clothing',
                default => 'marketplace',
            };
        }
        if ($this->isRestaurant()) {
            return 'food';
        }
        return 'marketplace';
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

    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo)) {
            return null;
        }
        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }
        return asset('storage/' . ltrim($this->logo, '/'));
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (empty($this->banner)) {
            return null;
        }
        if (str_starts_with($this->banner, 'http://') || str_starts_with($this->banner, 'https://')) {
            return $this->banner;
        }
        return asset('storage/' . ltrim($this->banner, '/'));
    }

    public function getCityAttribute($value): ?string
    {
        return $value ?: ($this->pickup_address['city'] ?? null);
    }

    public function getStorefrontUrlAttribute(): string
    {
        $requestHost = request()->getHost();
        $isIp = in_array($requestHost, ['127.0.0.1', '::1']) || filter_var($requestHost, FILTER_VALIDATE_IP);

        if (config('marketplace.use_subdomains', true) && !$isIp) {
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
