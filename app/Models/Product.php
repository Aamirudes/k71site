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
        return empty($this->listing_type) 
            || in_array($this->listing_type, ['purchase', 'food', 'grocery', 'service', 'booking', 'classified']);
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
}
