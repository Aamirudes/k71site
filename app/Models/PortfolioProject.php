<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'service_product_id',
        'service_order_id',
        'title',
        'slug',
        'summary',
        'description',
        'client_name',
        'role',
        'work_category',
        'skills_json',
        'deliverables_json',
        'external_url',
        'cover_image',
        'gallery_json',
        'completed_at',
        'is_featured',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'skills_json' => 'array',
        'deliverables_json' => 'array',
        'gallery_json' => 'array',
        'completed_at' => 'date',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'service_product_id')->withoutGlobalScopes();
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (empty($this->cover_image)) {
            $firstGallery = is_array($this->gallery_json) ? ($this->gallery_json[0] ?? null) : null;
            return $firstGallery ? format_image_url($firstGallery) : null;
        }
        return format_image_url($this->cover_image);
    }

    public function getGalleryUrlsAttribute(): array
    {
        $items = is_array($this->gallery_json) ? $this->gallery_json : [];
        return array_values(array_filter(array_map(fn ($img) => $img ? format_image_url($img) : null, $items)));
    }
}

