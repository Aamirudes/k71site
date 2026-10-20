<?php

return [

    /*
    |--------------------------------------------------------------------------
    | K71 Platform Identity
    |--------------------------------------------------------------------------
    */
    'name' => env('K71_NAME', 'K71'),
    'tagline' => env('K71_TAGLINE', 'Creative Agency & Unified Multi-Vertical Super-Platform'),
    'root_domain' => env('K71_ROOT_DOMAIN', 'marblemandi.org'),
    'contact_email' => env('K71_CONTACT_EMAIL', 'crab.hashir@gmail.com'),

    /*
    |--------------------------------------------------------------------------
    | Vertical Subdomains Mapping
    |--------------------------------------------------------------------------
    */
    'verticals' => [
        'main' => [
            'key' => 'main',
            'name' => 'Marketplace',
            'subdomain' => null,
            'title' => 'K71 — Creative Agency & Super-Marketplace',
            'description' => 'Discover, buy, and explore premium products across thousands of verified stores.',
            'badge' => 'K71',
            'theme_color' => '#0600c2',
        ],
        'food' => [
            'key' => 'food',
            'name' => 'Food & Restaurants',
            'subdomain' => 'food',
            'title' => 'K71 Food — Order Online from Top Restaurants & Cloud Kitchens',
            'description' => 'Explore fresh dishes, local food makers, and fast doorstep delivery or pickup.',
            'badge' => 'Food',
            'theme_color' => '#EA580C',
        ],
        'services' => [
            'key' => 'services',
            'name' => 'Professional Services',
            'subdomain' => 'services',
            'title' => 'K71 Services — Trusted Experts & On-Demand Bookings',
            'description' => 'Find verified local professionals, salon & wellness, repairs, tailoring, and book slots instantly.',
            'badge' => 'Services',
            'theme_color' => '#2563EB',
        ],
        'used' => [
            'key' => 'used',
            'name' => 'Used & Pre-Loved Items',
            'subdomain' => 'used',
            'title' => 'K71 Pre-Loved — Buy & Sell Verified Used Goods with Negotiation',
            'description' => 'Verified pre-owned electronics, fashion, vehicles, and direct buyer-seller offers.',
            'badge' => 'Pre-Loved',
            'theme_color' => '#059669',
        ],
        'enquiry' => [
            'key' => 'enquiry',
            'name' => 'B2B & Bulk Enquiry',
            'subdomain' => 'enquiry',
            'title' => 'K71 Enquiry — Request Quotes & Wholesale Sourcing',
            'description' => 'Post buyer requirements and receive direct quotes from verified manufacturers and distributors.',
            'badge' => 'Enquiry',
            'theme_color' => '#7C3AED',
        ],
        'shorts' => [
            'key' => 'shorts',
            'name' => 'Shorts Video Commerce',
            'subdomain' => 'shorts',
            'title' => 'K71 Shorts — Watch, Discover, and Shop Live Video Reels',
            'description' => 'Watch trending product reels, spin the wheel for coupons, and buy directly from videos.',
            'badge' => 'Shorts',
            'theme_color' => '#DC2626',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reserved Subdomains (Verticals + System)
    |--------------------------------------------------------------------------
    */
    'reserved_subdomains' => [
        'food', 'services', 'used', 'enquiry', 'shorts',
        'www', 'admin', 'api', 'app', 'mail', 'ftp', 'cpanel', 'support',
        'help', 'blog', 'shop', 'store', 'stores', 'dashboard', 'login',
        'register', 'cdn', 'static', 'assets', 'billing', 'staging', 'dev',
        'test', 'vendor', 'vendors', 'seller', 'sellers', 'account', 'my',
        'checkout', 'cart', 'search', 'category', 'categories', 'product',
        'products', 'order', 'orders', 'payment', 'payments', 'page',
        'pages', 'sitemap', 'feed', 'rss', 'robots', 'notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | COD Configuration
    |--------------------------------------------------------------------------
    */
    'cod' => [
        'marketplace_enabled' => env('K71_COD_ENABLED', true),
        'max_order_amount' => 500000, // paise (₹5,000)
        'fee' => 0,                  // paise
        'require_capable_courier' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | 3x3x3 Design Presets Definitions
    |--------------------------------------------------------------------------
    */
    'presets' => [
        'store_layouts' => [
            'classic' => [
                'name' => 'Classic Marketplace',
                'description' => 'Hero banner, featured category rail, and high-density product grid.',
            ],
            'editorial' => [
                'name' => 'Editorial Boutique',
                'description' => 'Story-driven layout with large typography, lookbooks, and craft highlights.',
            ],
            'minimal' => [
                'name' => 'Modern Minimalist',
                'description' => 'Clean whitespace, borderless product cards, and sleek focus on imagery.',
            ],
        ],
        'color_presets' => [
            'sage' => [
                'name' => 'Earthy Sage',
                'primary' => '#2D4A3E',
                'accent' => '#D8E2DC',
                'bg' => '#F8FAF8',
            ],
            'midnight' => [
                'name' => 'Midnight Indigo',
                'primary' => '#1E293B',
                'accent' => '#38BDF8',
                'bg' => '#F8FAFC',
            ],
            'sunset' => [
                'name' => 'Warm Terracotta / Sunset',
                'primary' => '#C85A32',
                'accent' => '#FDE047',
                'bg' => '#FFFDF9',
            ],
        ],
        'product_layouts' => [
            'gallery' => [
                'name' => 'Thumbnails & Gallery',
                'description' => 'Carousel image viewer with thumbnail row and compact purchasing card.',
            ],
            'immersive' => [
                'name' => 'Full-bleed Immersive',
                'description' => 'Large edge-to-edge media with floating sticky checkout bar.',
            ],
            'showcase' => [
                'name' => 'Specification Showcase',
                'description' => 'Split-screen layout highlighting technical specifications, variants, and trust badges.',
            ],
        ],
    ],
];
