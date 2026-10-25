<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'vertical',
        'vertical_type',
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'seo_title',
        'seo_description',
        'seo_content',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(CategoryField::class)->orderBy('sort_order');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class)->orderBy('sort_order');
    }

    public function scopeForVertical($query, string $vertical)
    {
        return $query->where('vertical', $vertical);
    }

    public function scopeForVerticalType($query, string $verticalType)
    {
        return $query->where('vertical_type', $verticalType);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    /**
     * Get appropriate image URL for category with photography fallbacks.
     */
    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image)) {
            return format_image_url($this->image);
        }

        $slugMap = [
            'graphic-design-branding' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?auto=format&fit=crop&w=600&q=80',
            'website-web-development' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=80',
            'app-development' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=600&q=80',
            'video-editing-animation' => 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?auto=format&fit=crop&w=600&q=80',
            'digital-marketing-ads' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
            'seo-content-writing' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=600&q=80',
            'business-consulting' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=600&q=80',
            'marble-home-craftsmanship' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80',
            'photography-creative' => 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?auto=format&fit=crop&w=600&q=80',
            'home-repair-services' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=600&q=80',
            'virtual-assistance' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=80',
        ];

        if (isset($slugMap[$this->slug])) {
            return $slugMap[$this->slug];
        }

        $nameLower = strtolower($this->slug . ' ' . $this->name);
        if (str_contains($nameLower, 'design') || str_contains($nameLower, 'logo') || str_contains($nameLower, 'brand')) {
            return 'https://images.unsplash.com/photo-1626785774573-4b799315345d?auto=format&fit=crop&w=600&q=80';
        }
        if (str_contains($nameLower, 'web') || str_contains($nameLower, 'code') || str_contains($nameLower, 'site')) {
            return 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=80';
        }
        if (str_contains($nameLower, 'app') || str_contains($nameLower, 'mobile') || str_contains($nameLower, 'ios') || str_contains($nameLower, 'android')) {
            return 'https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=600&q=80';
        }
        if (str_contains($nameLower, 'video') || str_contains($nameLower, 'anim') || str_contains($nameLower, 'film') || str_contains($nameLower, 'reel')) {
            return 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?auto=format&fit=crop&w=600&q=80';
        }
        if (str_contains($nameLower, 'market') || str_contains($nameLower, 'ad') || str_contains($nameLower, 'media')) {
            return 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80';
        }
        if (str_contains($nameLower, 'seo') || str_contains($nameLower, 'writ') || str_contains($nameLower, 'content')) {
            return 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=600&q=80';
        }
        if (str_contains($nameLower, 'consult') || str_contains($nameLower, 'business') || str_contains($nameLower, 'legal') || str_contains($nameLower, 'tax')) {
            return 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=600&q=80';
        }
        if (str_contains($nameLower, 'marble') || str_contains($nameLower, 'stone') || str_contains($nameLower, 'craft') || str_contains($nameLower, 'repair') || str_contains($nameLower, 'architect')) {
            return 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80';
        }

        return 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80';
    }
}
