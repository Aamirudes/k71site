<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NavigationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'title',
        'subtitle',
        'url',
        'icon_type',
        'icon',
        'image_url',
        'badge',
        'badge_color',
        'sort_order',
        'is_active',
        'target',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('nav_mobile_bottom');
            Cache::forget('nav_mobile_drawer');
        });

        static::deleted(function () {
            Cache::forget('nav_mobile_bottom');
            Cache::forget('nav_mobile_drawer');
        });
    }

    /**
     * Retrieve active mobile bottom navigation items with automatic fallback defaults.
     */
    public static function getMobileBottomItems()
    {
        return Cache::remember('nav_mobile_bottom', 3600, function () {
            $items = static::where('group', 'mobile_bottom')
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            if ($items->isNotEmpty()) {
                return $items;
            }

            return static::defaultBottomItems();
        });
    }

    /**
     * Retrieve active mobile drawer navigation items.
     */
    public static function getMobileDrawerItems()
    {
        return Cache::remember('nav_mobile_drawer', 3600, function () {
            return static::where('group', 'mobile_drawer')
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();
        });
    }

    /**
     * Built-in fallback default items for Mobile Bottom Bar.
     */
    public static function defaultBottomItems()
    {
        return collect([
            (object) [
                'id' => 1,
                'title' => 'Home',
                'url' => '/',
                'icon_type' => 'svg',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
                'image_url' => null,
                'badge' => null,
                'badge_color' => null,
                'target' => '_self',
            ],
            (object) [
                'id' => 2,
                'title' => 'Mandi',
                'url' => '/catalog',
                'icon_type' => 'svg',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
                'image_url' => null,
                'badge' => null,
                'badge_color' => null,
                'target' => '_self',
            ],
            (object) [
                'id' => 3,
                'title' => 'Shorts',
                'url' => '/shorts',
                'icon_type' => 'svg',
                'icon' => '<path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>',
                'image_url' => null,
                'badge' => 'LIVE',
                'badge_color' => 'rose',
                'target' => '_self',
            ],
            (object) [
                'id' => 4,
                'title' => 'Wishlist',
                'url' => '/wishlist',
                'icon_type' => 'svg',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>',
                'image_url' => null,
                'badge' => null,
                'badge_color' => null,
                'target' => '_self',
            ],
            (object) [
                'id' => 5,
                'title' => 'Account',
                'url' => '/account',
                'icon_type' => 'svg',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
                'image_url' => null,
                'badge' => null,
                'badge_color' => null,
                'target' => '_self',
            ],
        ]);
    }
}
