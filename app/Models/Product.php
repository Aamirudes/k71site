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
        'faqs_json',
        'requirements_prompt',
        'addons_json',
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
        'faqs_json' => 'array',
        'addons_json' => 'array',
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
        if ($this->isEnquiry() || $this->vertical === 'used' || $this->listing_type === 'classified') {
            return false;
        }

        if ($this->isBooking() || $this->vertical === 'services') {
            return false;
        }

        return empty($this->listing_type) 
            || in_array($this->listing_type, ['purchase', 'food', 'grocery']);
    }

    /**
     * Check if product is an enquiry-only listing (e.g. Real Estate, Marble, Industrial Machinery, B2B wholesale quotes).
     */
    public function isEnquiry(): bool
    {
        if (in_array($this->listing_type, ['enquiry', 'quote_only', 'contact_seller'])) {
            return true;
        }

        if ($this->vertical === 'enquiry') {
            return true;
        }

        $titleLower = strtolower($this->title ?? '');
        $catSlug = strtolower($this->category?->slug ?? '');
        $catName = strtolower($this->category?->name ?? '');
        $vendorType = strtolower($this->vendor?->vendor_type ?? '');

        // Marble, Granite, Stones, Industrial Machinery, Heavy Equipment, B2B wholesale, Real Estate
        $enquiryKeywords = [
            'marble', 'granite', 'machinery', 'machine', 'industrial', 'plant', 'equipment',
            'real estate', 'villa', 'apartment', 'plot', 'property', 'house for sale',
            'stone slab', 'bridge cutting', 'cnc', 'lathe', 'generator', 'transformer',
            'quarry', 'stone block', 'slabs'
        ];

        foreach ($enquiryKeywords as $kw) {
            if (str_contains($titleLower, $kw) || str_contains($catSlug, $kw) || str_contains($catName, $kw)) {
                return true;
            }
        }

        if (in_array($vendorType, ['machinery', 'marble', 'b2b', 'real_estate'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine category vertical type with robust fallbacks
     */
    public function getVerticalTypeAttribute(): string
    {
        // 1. Marble, Granite, Stone Slabs, Industrial Machinery & Equipment, B2B wholesale enquiries
        if ($this->vertical === 'enquiry' || $this->isEnquiry()) {
            return 'b2b_machinery';
        }

        // 2. Kirana & Groceries
        if ($this->vertical === 'kirana' || $this->vertical === 'groceries' || $this->listing_type === 'grocery') {
            return 'standard_retail';
        }

        // 3. Food listings & restaurants
        if ($this->vertical === 'food' || $this->listing_type === 'food' || $this->modifiers()->exists()) {
            return 'food_portion';
        }

        // 4. Services & freelance gigs
        if ($this->vertical === 'services' || $this->isBooking()) {
            return 'service_booking';
        }
        if ($this->gigPackages()->exists()) {
            return 'digital_gig';
        }

        // 5. Pre-owned & classifieds
        if ($this->vertical === 'used' || $this->listing_type === 'classified') {
            return 'classified_asset';
        }

        // 6. Explicit Category vertical type if available
        if ($this->category && !empty($this->category->vertical_type) && $this->category->vertical_type !== 'standard_retail') {
            return $this->category->vertical_type;
        }

        // 7. Check if actually apparel / fashion
        if ($this->isClothing()) {
            return 'fashion_matrix';
        }

        return 'standard_retail';
    }

    /**
     * Check if product is apparel / clothing / footwear
     */
    public function isClothing(): bool
    {
        $catName = strtolower($this->category?->name ?? '');
        $catSlug = strtolower($this->category?->slug ?? '');
        $titleLower = strtolower($this->title ?? '');

        // Non-clothing verticals and enquiry/kirana/electronics listings explicitly false
        if ($this->isEnquiry() || in_array($this->vertical, ['food', 'services', 'used', 'enquiry', 'kirana', 'groceries', 'electronics'])) {
            return false;
        }

        // Exclude marble, stones, machinery, electronics from clothing
        $nonApparelKeywords = ['marble', 'granite', 'stone', 'slab', 'slabs', 'tile', 'tiles', 'machinery', 'machine', 'phone', 'laptop', 'tablet'];
        foreach ($nonApparelKeywords as $nak) {
            if (str_contains($catName, $nak) || str_contains($catSlug, $nak) || str_contains($titleLower, $nak)) {
                return false;
            }
        }

        $apparelRegex = '/\b(cloth|clothes|clothing|apparel|fashion|shirt|shirts|t-shirt|tshirt|t-shirts|tshirts|pant|pants|trouser|trousers|jeans|dress|dresses|saree|saris|sarees|kurti|kurtis|kurta|lehenga|suit|suits|blazer|jacket|jackets|hoodie|hoodies|sweater|sweaters|ethnic|footwear|shoe|shoes|sneaker|sneakers|sandals|sandal|blouse|dupatta)\b/i';

        if (preg_match($apparelRegex, $catName) || preg_match($apparelRegex, $catSlug) || preg_match($apparelRegex, $titleLower)) {
            return true;
        }

        return false;
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
     * Resolve the visual branding theme and badge metadata for this product card.
     */
    public function getVerticalThemeAttribute(): array
    {
        $v = strtolower($this->vertical ?? '');
        $listingType = strtolower($this->listing_type ?? '');
        if ($v === 'marketplace' || empty($v)) {
            $v = strtolower($this->category?->vertical ?? '');
        }
        $catName = strtolower($this->category?->name ?? '');
        $catSlug = strtolower($this->category?->slug ?? '');
        $titleLower = strtolower($this->title ?? '');

        // Liquid Bubble Glassmorphic Standard Styling
        $baseBubble = 'bg-white/80 dark:bg-black/70 backdrop-blur-md border border-white/60 dark:border-white/15 text-zinc-900 dark:text-white shadow-[0_2px_10px_rgba(0,0,0,0.06),inset_0_1px_1px_rgba(255,255,255,0.8)]';

        // 1. Electronics, Mobiles & Tech (Check FIRST to prevent service badge misclassification)
        if (str_contains($catSlug, 'electronic') || str_contains($catName, 'electronic') 
            || str_contains($catSlug, 'mobile') || str_contains($catName, 'mobile') 
            || str_contains($catSlug, 'smartphone') || str_contains($catSlug, 'laptop') 
            || str_contains($catSlug, 'computer') || str_contains($catSlug, 'tablet') 
            || str_contains($catSlug, 'gadget') || str_contains($catSlug, 'appliance')
            || str_contains($titleLower, 'phone') || str_contains($titleLower, 'laptop') || str_contains($titleLower, 'gb ram')) {
            return [
                'key' => 'electronics',
                'vertical' => 'electronics',
                'label' => 'TECH',
                'icon' => '📱',
                'dot_color' => 'bg-blue-500',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-slate-900 dark:hover:border-white',
                'accent_text' => 'text-slate-900 dark:text-white',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-slate-100 hover:bg-slate-900 text-slate-900 hover:text-white border border-slate-200',
                'pill_bg' => 'bg-slate-100 text-slate-800',
            ];
        }

        // 2. Kirana & FMCG Groceries (Separate from restaurant food!)
        if (str_contains($catSlug, 'grocery') || str_contains($catName, 'grocery') 
            || str_contains($catSlug, 'grocer') || str_contains($catName, 'grocer')
            || str_contains($catSlug, 'kirana') || str_contains($catName, 'kirana') 
            || str_contains($catSlug, 'fmcg') || str_contains($catName, 'fmcg')
            || str_contains($catSlug, 'staple') || str_contains($catName, 'staple')) {
            return [
                'key' => 'groceries',
                'vertical' => 'groceries',
                'label' => 'KIRANA',
                'icon' => '🛒',
                'dot_color' => 'bg-emerald-500',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-emerald-600',
                'accent_text' => 'text-emerald-700 dark:text-emerald-400',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-emerald-50 hover:bg-emerald-600 text-emerald-800 hover:text-white border border-emerald-200',
                'pill_bg' => 'bg-emerald-50 text-emerald-800',
            ];
        }

        // 3. Restaurant Food & Dining
        if ($v === 'food' || $listingType === 'food' || str_contains($catSlug, 'food') || str_contains($catName, 'food') || str_contains($catName, 'dining') || str_contains($catSlug, 'dining') || str_contains($catSlug, 'restaurant')) {
            return [
                'key' => 'food',
                'vertical' => 'food',
                'label' => 'FOOD',
                'icon' => '🍲',
                'dot_color' => 'bg-amber-500',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-amber-600',
                'accent_text' => 'text-amber-800 dark:text-amber-400',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white border border-amber-200',
                'pill_bg' => 'bg-amber-50 text-amber-800',
            ];
        }

        // 4. Marble, Granite & Natural Stone
        if (str_contains($catSlug, 'marble') || str_contains($catName, 'marble') || str_contains($catSlug, 'granite') || str_contains($catName, 'granite') || str_contains($catSlug, 'stone') || str_contains($titleLower, 'marble')) {
            return [
                'key' => 'marble',
                'vertical' => 'marble',
                'label' => 'MARBLE',
                'icon' => '🏛️',
                'dot_color' => 'bg-stone-500',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-stone-800 dark:hover:border-stone-200',
                'accent_text' => 'text-stone-800 dark:text-stone-200',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-stone-100 hover:bg-stone-800 text-stone-800 hover:text-white border border-stone-200',
                'pill_bg' => 'bg-stone-100 text-stone-800',
            ];
        }

        // 5. Wholesale / Machinery / Industrial / B2B Quotes
        if ($v === 'enquiry' || $this->isEnquiry() || str_contains($catSlug, 'industrial') || str_contains($catSlug, 'machinery') || str_contains($catName, 'machinery') || str_contains($catSlug, 'b2b')) {
            return [
                'key' => 'enquiry',
                'vertical' => 'enquiry',
                'label' => 'MACHINERY',
                'icon' => '⚙️',
                'dot_color' => 'bg-cyan-500',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-cyan-700',
                'accent_text' => 'text-slate-900 dark:text-white',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-cyan-50 hover:bg-cyan-700 text-cyan-800 hover:text-white border border-cyan-200',
                'pill_bg' => 'bg-cyan-50 text-cyan-800',
            ];
        }

        // 6. Footwear & Shoes
        if (str_contains($catSlug, 'footwear') || str_contains($catName, 'footwear') || str_contains($catSlug, 'shoe') || str_contains($catName, 'shoe') || str_contains($titleLower, 'shoe') || str_contains($titleLower, 'sneaker')) {
            return [
                'key' => 'footwear',
                'vertical' => 'footwear',
                'label' => 'FOOTWEAR',
                'icon' => '👟',
                'dot_color' => 'bg-slate-700',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-slate-900',
                'accent_text' => 'text-slate-900 dark:text-white',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-slate-100 hover:bg-slate-900 text-slate-800 hover:text-white border border-slate-200',
                'pill_bg' => 'bg-slate-100 text-slate-800',
            ];
        }

        // 7. Pre-Owned / Used / Classifieds
        if ($v === 'used' || $listingType === 'classified' || str_contains($catSlug, 'used') || str_contains($catSlug, 'pre-loved') || str_contains($catName, 'pre-loved') || str_contains($catName, 'thrift')) {
            return [
                'key' => 'used',
                'vertical' => 'used',
                'label' => 'PRE-OWNED',
                'icon' => '🔄',
                'dot_color' => 'bg-sky-500',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-sky-600',
                'accent_text' => 'text-sky-700 dark:text-sky-400',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-sky-50 hover:bg-sky-600 text-sky-800 hover:text-white border border-sky-200',
                'pill_bg' => 'bg-sky-50 text-sky-800',
            ];
        }

        // 8. Services & Bookings (Only when verified service context)
        if ($v === 'services' || $this->isBooking() || str_contains($catSlug, 'service-booking') || str_contains($catSlug, 'gig') || str_contains($catName, 'freelanc')) {
            return [
                'key' => 'services',
                'vertical' => 'services',
                'label' => 'SERVICE',
                'icon' => '⚡',
                'dot_color' => 'bg-violet-500',
                'badge_bg' => $baseBubble,
                'border_color' => 'border-slate-200/80 dark:border-zinc-800',
                'card_border_hover' => 'hover:border-violet-600',
                'accent_text' => 'text-violet-700 dark:text-violet-400',
                'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
                'btn_pill' => 'bg-violet-50 hover:bg-violet-600 text-violet-800 hover:text-white border border-violet-200',
                'pill_bg' => 'bg-violet-50 text-violet-800',
            ];
        }

        // 9. Fashion & Apparel (Default)
        return [
            'key' => 'fashion',
            'vertical' => 'fashion',
            'label' => 'FASHION',
            'icon' => '✨',
            'dot_color' => 'bg-slate-900',
            'badge_bg' => $baseBubble,
            'border_color' => 'border-slate-200/80 dark:border-zinc-800',
            'card_border_hover' => 'hover:border-slate-900 dark:hover:border-white',
            'accent_text' => 'text-slate-900 dark:text-white',
            'btn_bg' => 'bg-slate-900 hover:bg-black text-white',
            'btn_pill' => 'bg-slate-100 hover:bg-slate-900 text-slate-800 hover:text-white border border-slate-200',
            'pill_bg' => 'bg-slate-100 text-slate-800',
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

    public function getCoverImageUrlAttribute(): string
    {
        return $this->primary_image_url;
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function getBasicPackageAttribute(): ?ProductGigPackage
    {
        return $this->gigPackages->firstWhere('tier', 'basic');
    }

    public function getStandardPackageAttribute(): ?ProductGigPackage
    {
        return $this->gigPackages->firstWhere('tier', 'standard');
    }

    public function getPremiumPackageAttribute(): ?ProductGigPackage
    {
        return $this->gigPackages->firstWhere('tier', 'premium');
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

    /**
     * Get human-formatted base price
     */
    public function getFormattedBasePriceAttribute(): string
    {
        return '₹' . number_format($this->base_price / 100, 2);
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
