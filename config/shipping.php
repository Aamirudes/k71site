<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shipping Automation Engine Configuration
    |--------------------------------------------------------------------------
    */

    'mode' => env('SHIPPING_MODE', 'live'), // 'live' (real couriers via Shiprocket) or 'sandbox' (testing)

    'shiprocket' => [
        'email' => env('SHIPROCKET_API_EMAIL', ''),
        'password' => env('SHIPROCKET_API_PASSWORD', ''),
        'base_url' => 'https://apiv2.shiprocket.in/v1/external',
        'token_cache_ttl' => 240 * 60, // 10 days in minutes
        'default_channel_id' => null,
    ],

    // Platform delivery profit markup (in paise: 2000 = ₹20.00)
    'platform_markup_paise' => (int) env('SHIPROCKET_PLATFORM_MARKUP', 2000),

    // Fallback pickup address details if vendor hasn't set one yet
    'fallback_pickup' => [
        'name' => 'K71 Fulfillment Center',
        'phone' => '9549311580',
        'address' => 'dhani road, khushi colony',
        'city' => 'Ajmer',
        'state' => 'Rajasthan',
        'pincode' => '305801',
    ],
];
