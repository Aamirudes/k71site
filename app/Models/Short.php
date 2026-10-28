<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Short extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'video_url',
        'video_path',
        'thumb_url',
        'thumb_b_url',
        'ratio',
        'duration',
        'is_featured',
        'is_boosted',
        'is_manual_boost',
        'boost_type',
        'views',
        'likes',
        'shares',
        'clicks',
        'comments_count',
        'trending_score',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_boosted' => 'boolean',
        'is_manual_boost' => 'boolean',
        'views' => 'integer',
        'likes' => 'integer',
        'shares' => 'integer',
        'clicks' => 'integer',
        'comments_count' => 'integer',
        'duration' => 'integer',
        'trending_score' => 'float',
        'approved_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'short_products')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShortEvent::class);
    }

    public function boosts(): HasMany
    {
        return $this->hasMany(ShortBoost::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ShortComment::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ShortShare::class);
    }

    public function scopePublished($query)
    {
        return $query->whereIn('status', ['published', 'approved', 'active']);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBoosted($query)
    {
        return $query->where('is_boosted', true);
    }

    public static function formatNumber(?int $num): string
    {
        $num = (int) $num;
        if ($num >= 1000000) {
            return round($num / 1000000, 1) . 'M';
        }
        if ($num >= 1000) {
            return round($num / 1000, 1) . 'K';
        }
        return (string) $num;
    }

    /**
     * Build standard payload compatible with legacy ZSU frontend player.
     */
    public function toZsuArray(): array
    {
        $vendor = $this->vendor;
        $vendorName = $vendor?->shop_name ?? 'Creator';
        $vendorStore = $vendor?->shop_name ?? '';
        $vendorAvatar = $vendor?->logo ? format_image_url($vendor->logo, asset('images/default-avatar.png')) : asset('images/default-avatar.png');
        $vendorStoreUrl = $vendor ? $vendor->storefront_url : '#';

        $categories = $this->category ? [$this->category->name] : [];

        $thumb = $this->thumb_url ? format_image_url($this->thumb_url) : self::placeholderThumb($this->id, $this->title);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => Str::limit($this->description ?? '', 80),
            'video_url' => $this->video_url,
            'thumb_url' => $thumb,
            'thumb_b_url' => $this->thumb_b_url ? format_image_url($this->thumb_b_url) : null,
            'product_ids' => $this->relationLoaded('products') ? $this->products->pluck('id')->toArray() : $this->products()->pluck('products.id')->toArray(),
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $vendorName,
            'vendor_store' => $vendorStore,
            'vendor_store_url' => $vendorStoreUrl,
            'vendor_avatar' => $vendorAvatar,
            'views' => self::formatNumber($this->views),
            'views_raw' => $this->views,
            'likes' => self::formatNumber($this->likes),
            'likes_raw' => $this->likes,
            'shares' => self::formatNumber($this->shares),
            'shares_raw' => $this->shares,
            'clicks' => self::formatNumber($this->clicks),
            'comments_count' => $this->comments_count,
            'is_featured' => $this->is_featured,
            'is_boosted' => $this->is_boosted,
            'is_manual_boost' => $this->is_manual_boost,
            'trending_score' => $this->trending_score,
            'ratio' => $this->ratio ?: '9:16',
            'duration' => $this->duration,
            'watch_avg_pct' => 0,
            'categories' => $categories,
            'date' => $this->created_at?->timestamp ?? time(),
            'date_formatted' => $this->created_at?->format('M j, Y') ?? '',
            'permalink' => route('shorts.show', $this->id),
        ];
    }

    public static function placeholderThumb(int $id, string $title = ''): string
    {
        $palettes = [
            ['#0f172a', '#1e293b'],
            ['#111827', '#1f2937'],
            ['#0c1a2e', '#162540'],
            ['#0a0a0a', '#1a1a1a'],
            ['#0f0f1a', '#1a1a2e'],
            ['#0a1208', '#0f1f10'],
        ];
        $pair = $palettes[$id % count($palettes)];
        $c0 = $pair[0];
        $c1 = $pair[1];
        $label = strtoupper(substr($title ?: 'ZS', 0, 2));

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="711">'
            . '<defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0" stop-color="' . $c0 . '"/><stop offset="1" stop-color="' . $c1 . '"/>'
            . '</linearGradient></defs>'
            . '<rect width="400" height="711" fill="url(#g)"/>'
            . '<circle cx="200" cy="340" r="48" fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.15)" stroke-width="1.5"/>'
            . '<polygon points="188,320 188,360 228,340" fill="rgba(255,255,255,0.4)"/>'
            . '<text x="200" y="420" font-family="Arial,sans-serif" font-size="22" font-weight="600" fill="rgba(255,255,255,0.18)" text-anchor="middle" letter-spacing="4">'
            . htmlspecialchars($label)
            . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
