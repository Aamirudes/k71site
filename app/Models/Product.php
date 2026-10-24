<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToVendor;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable, BelongsToVendor, HasSeo;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'vertical',
        'listing_type',
        'custom_fields',
        'title',
        'slug',
        'description',
        'short_description',
        'brand',
        'gender',
        'age_group',
        'material',
        'care_instructions',
        'size_chart',
        'tags',
        'base_price',
        'compare_at_price',
        'gst_rate',
        'hsn_code',
        'sku',
        'status',
        'is_featured',
        'total_stock',
        'seo_title',
        'seo_description',
        'og_image',
        'published_at',
        'weight_grams',
        'is_returnable',
        'return_window_days',
        'country_of_origin',
        'is_negotiable',
        'is_sold',
        'has_variants',
        'specifications_json',
        'b2b_tiers_json',
        'service_config_json',
        'reserve_price',
        'bid_increment',
        'auction_starts_at',
        'auction_ends_at',
        'current_bid',
        'winning_bidder_id',
        'bids_count',
        'auction_status',
    ];

    protected $casts = [
        'has_variants' => 'boolean',
        'specifications_json' => 'array',
        'b2b_tiers_json' => 'array',
        'service_config_json' => 'array',
        'tags' => 'json',
        'size_chart' => 'array',
        'custom_fields' => 'array',
        'is_featured' => 'boolean',
        'is_returnable' => 'boolean',
        'is_negotiable' => 'boolean',
        'is_sold' => 'boolean',
        'published_at' => 'datetime',
        'auction_starts_at' => 'datetime',
        'auction_ends_at' => 'datetime',
        'reserve_price' => 'integer',
        'bid_increment' => 'integer',
        'current_bid' => 'integer',
        'bids_count' => 'integer',
    ];

    /**
     * Check if product supports direct cart purchase.
     */
    public function isPurchase(): bool
    {
        if ($this->vertical === 'used' || $this->listing_type === 'classified') {
            return false;
        }

        return empty($this->listing_type) 
            || in_array($this->listing_type, ['purchase', 'food', 'grocery', 'service', 'booking']);
    }

    /**
     * Check if product is an enquiry-only listing (e.g. Real Estate, High-ticket, B2B wholesale quotes).
     */
    public function isEnquiry(): bool
    {
        return in_array($this->listing_type, ['enquiry', 'quote_only', 'contact_seller']);
    }

    /**
     * Check if product is a booking listing (e.g. doorstep services).
     */
    public function isBooking(): bool
    {
        return in_array($this->listing_type, ['booking', 'service']);
    }

    /**
     * Check if product is an auction listing.
     */
    public function isAuction(): bool
    {
        return $this->listing_type === 'auction';
    }

    /**
     * Check if product is contact seller only.
     */
    public function isContactSeller(): bool
    {
        return in_array($this->listing_type, ['contact_seller', 'quote_only', 'enquiry']);
    }

    /**
     * Get value of custom attribute from JSON fields.
     */
    public function getCustomField(string $key, $default = null)
    {
        return $this->custom_fields[$key] ?? $default;
    }

    /**
     * Scope query by marketplace vertical.
     */
    public function scopeForVertical($query, string $vertical)
    {
        return $query->where('vertical', $vertical);
    }

    /**
     * Scope query by listing type.
     */
    public function scopeListingType($query, string $type)
    {
        return $query->where('listing_type', $type);
    }

    /**
     * Scope query to exclude used / classifieds listings from standard retail and home displays.
     */
    public function scopeExcludeUsed($query)
    {
        return $query->where(function($q) {
            $q->whereNull('vertical')
              ->orWhere('vertical', '!=', 'used');
        })->where(function($q) {
            $q->whereNull('listing_type')
              ->orWhere('listing_type', '!=', 'classified');
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(ProductModifier::class);
    }

    public function gigPackages(): HasMany
    {
        return $this->hasMany(ProductGigPackage::class);
    }

    /**
     * Resolve the active vertical type for this product
     */
    public function getVerticalTypeAttribute(): string
    {
        if ($this->category && !empty($this->category->vertical_type)) {
            return $this->category->vertical_type;
        }

        return match ($this->vertical) {
            'food' => 'food_portion',
            'services' => ($this->listing_type === 'gig' ? 'digital_gig' : 'service_booking'),
            'enquiry' => 'b2b_machinery',
            'used' => 'classified_asset',
            default => 'fashion_matrix',
        };
    }

    /**
     * Resolve the visual branding theme and badge metadata for this product card.
     */
    public function getVerticalThemeAttribute(): array
    {
        $v = strtolower($this->vertical ?? '');
        if ($v === 'marketplace' || empty($v)) {
            $v = strtolower($this->category?->vertical ?? '');
        }
        $catName = strtolower($this->category?->name ?? '');
        $catSlug = strtolower($this->category?->slug ?? '');

        // 1. Food & Dining / Groceries
        if ($v === 'food' || str_contains($catSlug, 'food') || str_contains($catName, 'food') || str_contains($catSlug, 'grocer') || str_contains($catName, 'grocer') || str_contains($catName, 'dining') || str_contains($catSlug, 'dining')) {
            return [
                'key' => 'food',
                'vertical' => 'food',
                'label' => 'FOOD',
                'icon' => '🍲',
                'badge_bg' => 'bg-[#ea580c] text-white',
                'border_color' => 'border-orange-200 dark:border-orange-950',
                'card_border_hover' => 'hover:border-[#ea580c] hover:shadow-orange-500/15',
                'accent_text' => 'text-[#ea580c]',
                'btn_bg' => 'bg-[#ea580c] hover:bg-[#c2410c] text-white shadow-orange-500/25',
                'btn_pill' => 'bg-orange-50 hover:bg-[#ea580c] text-[#ea580c] hover:text-white border border-orange-200',
                'pill_bg' => 'bg-orange-50 text-[#ea580c]',
            ];
        }

        // 2. Pre-Owned / Used / Classifieds (BLUE & WHITE)
        if ($v === 'used' || str_contains($catSlug, 'used') || str_contains($catSlug, 'pre-loved') || str_contains($catName, 'pre-loved') || str_contains($catName, 'thrift')) {
            return [
                'key' => 'used',
                'vertical' => 'used',
                'label' => 'PRE-OWNED',
                'icon' => '🔄',
                'badge_bg' => 'bg-blue-600 text-white',
                'border_color' => 'border-blue-200 dark:border-blue-900',
                'card_border_hover' => 'hover:border-blue-600 hover:shadow-blue-500/15',
                'accent_text' => 'text-blue-600',
                'btn_bg' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/25',
                'btn_pill' => 'bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200',
                'pill_bg' => 'bg-blue-50 text-blue-700',
            ];
        }

        // 3. Services / On-Demand / Gigs
        if ($v === 'services' || str_contains($catSlug, 'service') || str_contains($catName, 'service') || str_contains($catSlug, 'gig') || str_contains($catName, 'freelanc')) {
            return [
                'key' => 'services',
                'vertical' => 'services',
                'label' => 'SERVICE',
                'icon' => '⚡',
                'badge_bg' => 'bg-purple-600 text-white',
                'border_color' => 'border-purple-200 dark:border-purple-950',
                'card_border_hover' => 'hover:border-purple-500 hover:shadow-purple-500/15',
                'accent_text' => 'text-purple-600',
                'btn_bg' => 'bg-purple-600 hover:bg-purple-700 text-white shadow-purple-500/25',
                'btn_pill' => 'bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white border border-purple-200',
                'pill_bg' => 'bg-purple-50 text-purple-700',
            ];
        }

        // 4. Wholesale / Machinery / Industrial / B2B Quotes
        if ($v === 'enquiry' || str_contains($catSlug, 'industrial') || str_contains($catSlug, 'machinery') || str_contains($catName, 'machinery') || str_contains($catSlug, 'b2b')) {
            return [
                'key' => 'enquiry',
                'vertical' => 'enquiry',
                'label' => 'WHOLESALE',
                'icon' => '🏭',
                'badge_bg' => 'bg-cyan-700 text-white',
                'border_color' => 'border-cyan-200 dark:border-cyan-950',
                'card_border_hover' => 'hover:border-cyan-600 hover:shadow-cyan-500/15',
                'accent_text' => 'text-cyan-700',
                'btn_bg' => 'bg-cyan-700 hover:bg-cyan-800 text-white shadow-cyan-500/25',
                'btn_pill' => 'bg-cyan-50 hover:bg-cyan-700 text-cyan-800 hover:text-white border border-cyan-200',
                'pill_bg' => 'bg-cyan-50 text-cyan-800',
            ];
        }

        // 5. Electronics & Tech
        if (str_contains($catSlug, 'electronic') || str_contains($catName, 'electronic') || str_contains($catSlug, 'appliances') || str_contains($catSlug, 'smartphone')) {
            return [
                'key' => 'electronics',
                'vertical' => 'electronics',
                'label' => 'TECH',
                'icon' => '📱',
                'badge_bg' => 'bg-blue-600 text-white',
                'border_color' => 'border-blue-200 dark:border-blue-950',
                'card_border_hover' => 'hover:border-blue-500 hover:shadow-blue-500/15',
                'accent_text' => 'text-blue-600',
                'btn_bg' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/25',
                'btn_pill' => 'bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200',
                'pill_bg' => 'bg-blue-50 text-blue-700',
            ];
        }

        // 6. Marble, Granite, Home & Living
        if (str_contains($catSlug, 'marble') || str_contains($catName, 'marble') || str_contains($catSlug, 'furniture') || str_contains($catSlug, 'home') || str_contains($catSlug, 'living')) {
            return [
                'key' => 'marble',
                'vertical' => 'marble',
                'label' => 'MARBLE & HOME',
                'icon' => '🏛️',
                'badge_bg' => 'bg-amber-700 text-white',
                'border_color' => 'border-amber-200 dark:border-amber-950',
                'card_border_hover' => 'hover:border-amber-600 hover:shadow-amber-500/15',
                'accent_text' => 'text-amber-700',
                'btn_bg' => 'bg-amber-700 hover:bg-amber-800 text-white shadow-amber-500/25',
                'btn_pill' => 'bg-amber-50 hover:bg-amber-700 text-amber-800 hover:text-white border border-amber-200',
                'pill_bg' => 'bg-amber-50 text-amber-800',
            ];
        }

        // 7. Fashion & Apparel (Default)
        return [
            'key' => 'fashion',
            'vertical' => 'fashion',
            'label' => 'FASHION',
            'icon' => '✨',
            'badge_bg' => 'bg-indigo-600 text-white',
            'border_color' => 'border-indigo-200 dark:border-indigo-950',
            'card_border_hover' => 'hover:border-indigo-500 hover:shadow-indigo-500/15',
            'accent_text' => 'text-indigo-600',
            'btn_bg' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-500/25',
            'btn_pill' => 'bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200',
            'pill_bg' => 'bg-indigo-50 text-indigo-700',
        ];
    }

    public function getFirstInStockVariantAttribute(): ?ProductVariant
    {
        return $this->variants->firstWhere('stock', '>', 0) ?? $this->variants->first();
    }

    public function isFashionMatrix(): bool
    {
        return $this->vertical_type === 'fashion_matrix';
    }

    public function isFoodPortion(): bool
    {
        return $this->vertical_type === 'food_portion';
    }

    public function isServiceBooking(): bool
    {
        return $this->vertical_type === 'service_booking';
    }

    public function isB2bMachinery(): bool
    {
        return $this->vertical_type === 'b2b_machinery';
    }

    public function isDigitalGig(): bool
    {
        return $this->vertical_type === 'digital_gig';
    }

    public function isClassifiedAsset(): bool
    {
        return $this->vertical_type === 'classified_asset';
    }

    public function isStandardRetail(): bool
    {
        return $this->vertical_type === 'standard_retail';
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function shorts(): BelongsToMany
    {
        return $this->belongsToMany(Short::class, 'short_products')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function isNegotiable(): bool
    {
        return (bool) ($this->is_negotiable ?? true);
    }

    public function isSold(): bool
    {
        return (bool) ($this->is_sold ?? false);
    }

    public function markAsSold(): void
    {
        $this->update([
            'is_sold' => true,
            'total_stock' => 0,
        ]);
    }

    public function scopeUsed($query)
    {
        return $query->where('vertical', 'used');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isService(): bool
    {
        return $this->vertical === 'services' || $this->listing_type === 'booking';
    }

    public function scopeServices($query)
    {
        return $query->where('vertical', 'services');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function scopeEnquiries($query)
    {
        return $query->where('vertical', 'enquiry');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->orderBy('bid_amount', 'desc');
    }

    public function winningBidder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winning_bidder_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function isAuctionLive(): bool
    {
        if ($this->auction_status !== 'live') {
            return false;
        }

        if ($this->auction_starts_at && $this->auction_starts_at->isFuture()) {
            return false;
        }

        if ($this->auction_ends_at && $this->auction_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isAuctionEnded(): bool
    {
        return $this->auction_status === 'ended' || ($this->auction_ends_at && $this->auction_ends_at->isPast());
    }

    public function minNextBid(): int
    {
        $increment = $this->bid_increment ?: 10000;
        if (!$this->current_bid) {
            return $this->base_price ?: 10000;
        }

        return $this->current_bid + $increment;
    }

    public function formattedCurrentBid(): string
    {
        $amount = $this->current_bid ?? $this->base_price;
        return '₹' . number_format($amount / 100, 2);
    }

    public function formattedMinNextBid(): string
    {
        return '₹' . number_format($this->minNextBid() / 100, 2);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest();
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Get primary image URL
     */
    public function getPrimaryImageUrlAttribute(): string
    {
        $first = $this->images->first();
        if ($first) {
            return $first->url;
        }
        return asset('images/product-placeholder.svg');
    }

    public function isVeg(): bool
    {
        $cf = json_encode($this->custom_fields ?? []);
        return str_contains(strtolower($cf), 'veg') && !str_contains(strtolower($cf), 'non-veg');
    }

    public function getDietaryTypeAttribute(): string
    {
        return $this->custom_fields['dietary_type'] ?? ($this->isVeg() ? 'Veg' : 'Non-Veg');
    }

    public function getSpiceLevelAttribute(): ?string
    {
        return $this->custom_fields['spice_level'] ?? null;
    }

    public function getServingSizeAttribute(): ?string
    {
        return $this->custom_fields['serving_size'] ?? null;
    }
}
