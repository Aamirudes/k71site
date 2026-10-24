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

    public static function supportedGroups(): array
    {
        return [
            'mobile_bottom'   => ['label' => 'Universal Marketplace', 'icon' => '🌐', 'color' => 'indigo'],
            'mobile_food'     => ['label' => 'Food & Cloud Kitchens',  'icon' => '🍕', 'color' => 'amber'],
            'mobile_services' => ['label' => 'Doorstep Services',     'icon' => '🔧', 'color' => 'purple'],
            'mobile_used'     => ['label' => 'Pre-Owned Classifieds', 'icon' => '♻️', 'color' => 'emerald'],
            'mobile_shorts'   => ['label' => 'Live Shoppable Shorts', 'icon' => '🎬', 'color' => 'rose'],
            'mobile_drawer'   => ['label' => 'Mobile Drawer Menu',    'icon' => '📱', 'color' => 'slate'],
        ];
    }

    protected static function booted()
    {
        static::saved(function () {
            static::clearAllNavigationCache();
        });

        static::deleted(function () {
            static::clearAllNavigationCache();
        });
    }

    public static function clearAllNavigationCache(): void
    {
        $keys = [
            'nav_mobile_bottom',
            'nav_mobile_food',
            'nav_mobile_services',
            'nav_mobile_used',
            'nav_mobile_shorts',
            'nav_mobile_drawer',
        ];
        foreach ($keys as $k) {
            Cache::forget($k);
        }
    }

    /**
     * Retrieve active mobile items for any specific navigation panel group with automatic fallback.
     */
    public static function getPanelItems(string $group)
    {
        return Cache::remember('nav_' . $group, 3600, function () use ($group) {
            $items = static::where('group', $group)
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            if ($items->isNotEmpty()) {
                return $items;
            }

            return static::defaultGroupItems($group);
        });
    }

    /**
     * Legacy helper for universal bottom navigation
     */
    public static function getMobileBottomItems()
    {
        return static::getPanelItems('mobile_bottom');
    }

    /**
     * Retrieve active mobile drawer navigation items.
     */
    public static function getMobileDrawerItems()
    {
        return static::getPanelItems('mobile_drawer');
    }

    /**
     * Built-in fallback defaults for each panel group.
     */
    public static function defaultGroupItems(string $group)
    {
        $allDefaults = [
            'mobile_bottom' => [
                ['title' => 'Home', 'url' => '/', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Explore', 'url' => '/catalog', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Shorts', 'url' => '/shorts', 'icon_type' => 'svg', 'icon' => '<path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>', 'badge' => 'LIVE', 'badge_color' => 'rose'],
                ['title' => 'Wishlist', 'url' => '/wishlist', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Cart', 'url' => '/cart', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>', 'badge' => null, 'badge_color' => null],
            ],
            'mobile_food' => [
                ['title' => 'Menu', 'url' => '/food', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Pure Veg', 'url' => '/food?pure_veg=1', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>', 'badge' => 'VEG', 'badge_color' => 'emerald'],
                ['title' => 'Food Cart', 'url' => '/cart', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Wishlist', 'url' => '/wishlist', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Account', 'url' => '/account', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>', 'badge' => null, 'badge_color' => null],
            ],
            'mobile_services' => [
                ['title' => 'Services', 'url' => '/services', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.398-3.045 1.179l-6.44 6.44a2.25 2.25 0 01-3.182-3.182l6.44-6.44c.781-.781 1.27-1.969 1.179-3.045A4.5 4.5 0 0116.5 2.25h1.5v3h3v1.5z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Categories', 'url' => '/services#categories', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Service Cart', 'url' => '/cart', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Wishlist', 'url' => '/wishlist', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Bookings', 'url' => '/account', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>', 'badge' => 'PRO', 'badge_color' => 'purple'],
            ],
            'mobile_used' => [
                ['title' => 'Pre-Owned', 'url' => '/used', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Verified', 'url' => '/used?verified=1', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>', 'badge' => 'SAFE', 'badge_color' => 'emerald'],
                ['title' => 'Sell Now', 'url' => '/vendor/products/create', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/>', 'badge' => 'SELL', 'badge_color' => 'emerald'],
                ['title' => 'Wishlist', 'url' => '/wishlist', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Account', 'url' => '/account', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>', 'badge' => null, 'badge_color' => null],
            ],
            'mobile_shorts' => [
                ['title' => 'Live Feed', 'url' => '/shorts', 'icon_type' => 'svg', 'icon' => '<path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>', 'badge' => 'LIVE', 'badge_color' => 'rose'],
                ['title' => 'Trending', 'url' => '/shorts?tab=trending', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/>', 'badge' => 'HOT', 'badge_color' => 'amber'],
                ['title' => 'Shop Cart', 'url' => '/cart', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Saved', 'url' => '/wishlist', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>', 'badge' => null, 'badge_color' => null],
                ['title' => 'Creator Hub', 'url' => '/vendor/dashboard', 'icon_type' => 'svg', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>', 'badge' => 'CREATOR', 'badge_color' => 'indigo'],
            ],
            'mobile_drawer' => [
                ['title' => 'Categories & Sectors', 'url' => '/catalog', 'icon_type' => 'emoji', 'icon' => '🛍️', 'badge' => null, 'badge_color' => null],
                ['title' => 'Food & Cloud Kitchens', 'url' => '/food', 'icon_type' => 'emoji', 'icon' => '🍕', 'badge' => 'FAST', 'badge_color' => 'amber'],
                ['title' => 'Doorstep Services', 'url' => '/services', 'icon_type' => 'emoji', 'icon' => '🔧', 'badge' => 'PRO', 'badge_color' => 'purple'],
                ['title' => 'Pre-Owned Classifieds', 'url' => '/used', 'icon_type' => 'emoji', 'icon' => '♻️', 'badge' => null, 'badge_color' => null],
                ['title' => 'Shoppable Shorts', 'url' => '/shorts', 'icon_type' => 'emoji', 'icon' => '🎬', 'badge' => 'LIVE', 'badge_color' => 'rose'],
                ['title' => 'B2B Wholesale & RFQ', 'url' => '/b2b', 'icon_type' => 'emoji', 'icon' => '🏭', 'badge' => 'GST', 'badge_color' => 'emerald'],
                ['title' => 'Sell on K71', 'url' => '/vendor/register', 'icon_type' => 'emoji', 'icon' => '💼', 'badge' => 'FREE', 'badge_color' => 'emerald'],
            ],
        ];

        $items = $allDefaults[$group] ?? $allDefaults['mobile_bottom'];

        return collect($items)->map(function ($item, $idx) use ($group) {
            return (object) array_merge([
                'id' => $idx + 1,
                'group' => $group,
                'subtitle' => null,
                'image_url' => null,
                'target' => '_self',
                'sort_order' => $idx + 1,
                'is_active' => true,
            ], $item);
        });
    }

    /**
     * Legacy default items for Mobile Bottom Bar.
     */
    public static function defaultBottomItems()
    {
        return static::defaultGroupItems('mobile_bottom');
    }
}
