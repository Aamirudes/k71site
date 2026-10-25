<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Identity
    |--------------------------------------------------------------------------
    */
    'name' => env('MARKETPLACE_NAME', 'K71'),
    'tagline' => env('MARKETPLACE_TAGLINE', "Unified Creative Agency & Multi-Vertical Super-Platform"),
    'domain' => env('MARKETPLACE_DOMAIN', 'marblemandi.org'),
    'contact_email' => env('MARKETPLACE_CONTACT_EMAIL', 'crab.hashir@gmail.com'),
    'contact_phone' => env('MARKETPLACE_CONTACT_PHONE', '+91 95493 11580'),
    'logo' => env('MARKETPLACE_LOGO', '/images/k71-logo.png'),
    'favicon' => env('MARKETPLACE_FAVICON', '/favicon.ico'),

    /*
    |--------------------------------------------------------------------------
    | Subdomain / Path Tenancy
    |--------------------------------------------------------------------------
    */
    'use_subdomains' => env('MARKETPLACE_USE_SUBDOMAINS', true),
    'storefront_path_prefix' => 's', // used when subdomains are off: /s/{shop-slug}

    /*
    |--------------------------------------------------------------------------
    | Reserved Subdomains (system-level, non-editable)
    |--------------------------------------------------------------------------
    | Admin-managed blocklist is stored in the `reserved_slugs` DB table.
    */
    'reserved_subdomains' => [
        'www', 'admin', 'api', 'app', 'mail', 'ftp', 'cpanel', 'support',
        'help', 'blog', 'shop', 'store', 'stores', 'dashboard', 'login',
        'register', 'cdn', 'static', 'assets', 'billing', 'staging', 'dev',
        'test', 'vendor', 'vendors', 'seller', 'sellers', 'account', 'my',
        'checkout', 'cart', 'search', 'category', 'categories', 'product',
        'products', 'order', 'orders', 'payment', 'payments', 'page',
        'pages', 'sitemap', 'feed', 'rss', 'robots', 'styleguide',
        'notifications', 'settings', 'profile', 'wishlist', 'auth',
        'oauth', 'callback', 'verify', 'reset', 'confirm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shop Slug Rules
    |--------------------------------------------------------------------------
    */
    'slug' => [
        'min_length' => 3,
        'max_length' => 30,
        'pattern' => '/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', // no leading/trailing/consecutive hyphens
        'deleted_hold_days' => 90, // days a deleted vendor's slug is reserved
        'reservation_hours' => 24, // hours slug is held during email verification
    ],

    /*
    |--------------------------------------------------------------------------
    | Vendor Settings
    |--------------------------------------------------------------------------
    */
    'vendor' => [
        'auto_approve' => env('MARKETPLACE_AUTO_APPROVE_VENDORS', true),
        'product_approval_required' => env('MARKETPLACE_PRODUCT_APPROVAL', false),
        'max_images_per_product' => 10,
        'max_image_size_kb' => 5120, // 5MB
        'allowed_image_types' => ['jpeg', 'jpg', 'png', 'webp'],
        'default_processing_days' => 3,
        'default_return_window_days' => 7,
        'default_low_stock_threshold' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'default_driver' => env('PAYMENT_DRIVER', 'upi_direct'),
        'cod_enabled' => env('PAYMENT_COD_ENABLED', false),
        'upi_apps' => [
            'phonepe' => [
                'name' => 'PhonePe',
                'scheme' => 'phonepe://pay',
                'icon' => '/images/upi/phonepe.svg',
            ],
            'googlepay' => [
                'name' => 'Google Pay',
                'scheme' => 'tez://upi/pay',
                'icon' => '/images/upi/googlepay.svg',
            ],
            'paytm' => [
                'name' => 'Paytm',
                'scheme' => 'paytmmp://pay',
                'icon' => '/images/upi/paytm.svg',
            ],
            'generic' => [
                'name' => 'Any UPI App',
                'scheme' => 'upi://pay',
                'icon' => '/images/upi/upi.svg',
            ],
        ],
        'payment_deadline_minutes' => 30,
        'verification_deadline_hours' => 48,
        'utr_pattern' => '/^\d{12}$/', // 12-digit UTR
    ],

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */
    'orders' => [
        'number_prefix' => 'MP',
        'stock_reservation_minutes' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
        'max_attempts' => 5,
        'lockout_minutes' => 15,
        'resend_cooldown_seconds' => 60,
        'max_resend_per_hour' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | GST / Tax
    |--------------------------------------------------------------------------
    */
    'tax' => [
        'default_gst_rate' => 5.00,
        'gst_rates' => [0, 5, 12, 18, 28], // valid GST slab rates
        'marketplace_state_code' => env('MARKETPLACE_STATE_CODE', '27'), // Maharashtra default
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'title_suffix' => env('MARKETPLACE_NAME', 'K71'),
        'default_description' => 'Shop & source across K71 Multi-Category Marketplace: Fashion, Footwear, Kirana, Electronics, Food, Doorstep Services, Certified Pre-Owned, and Industrial Marble & Machinery RFQs.',
        'og_image' => '/images/og-default.jpg',
        'canonical_domain' => env('MARKETPLACE_DOMAIN', 'marblemandi.org'),
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),
        'google_search_console_id' => env('GOOGLE_SEARCH_CONSOLE_ID', ''),
        'meta_pixel_id' => env('META_PIXEL_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Recaptcha / Turnstile
    |--------------------------------------------------------------------------
    */
    'captcha' => [
        'driver' => env('CAPTCHA_DRIVER', 'recaptcha'), // recaptcha | turnstile | none
        'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY', ''),
        'recaptcha_secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
        'turnstile_site_key' => env('TURNSTILE_SITE_KEY', ''),
        'turnstile_secret_key' => env('TURNSTILE_SECRET_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Display / Locale
    |--------------------------------------------------------------------------
    */
    'currency' => 'INR',
    'currency_symbol' => '₹',
    'display_timezone' => 'Asia/Kolkata',

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'reviews' => false,            // Phase 6 optional
        'wishlist_price_alerts' => false,
        'abandoned_cart_emails' => false,
        'vendor_chat' => false,
        'flash_sales' => false,
        'dark_mode' => false,
    ],

];
