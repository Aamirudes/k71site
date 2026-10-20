<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'phonepe' => [
        'merchant_id' => (env('PHONEPE_MERCHANT_ID') && env('PHONEPE_MERCHANT_ID') !== 'PGTESTPAYUAT86') ? env('PHONEPE_MERCHANT_ID') : 'M23TKU0R0MJ8I',
        'client_id' => env('PHONEPE_CLIENT_ID') ?: 'SU2610161656542399718703',
        'client_secret' => env('PHONEPE_CLIENT_SECRET') ?: '8967e108-d791-45f6-a1b2-fb168beed1ee',
        'client_version' => (int) (env('PHONEPE_CLIENT_VERSION') ?: 1),
        'salt_key' => env('PHONEPE_SALT_KEY') ?: '8967e108-d791-45f6-a1b2-fb168beed1ee',
        'salt_index' => (int) (env('PHONEPE_SALT_INDEX') ?: 1),
        'env' => (env('PHONEPE_ENV') === 'UAT' && (!env('PHONEPE_CLIENT_ID') || env('PHONEPE_MERCHANT_ID') === 'PGTESTPAYUAT86')) ? 'PRODUCTION' : (env('PHONEPE_ENV') ?: 'PRODUCTION'),
    ],

];
