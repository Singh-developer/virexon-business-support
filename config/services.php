<?php

return [

    'razorpay' => [

        'test_key_id' => env(
            'RAZORPAY_TEST_KEY_ID'
        ),

        'test_key_secret' => env(
            'RAZORPAY_TEST_KEY_SECRET'
        ),

        'live_key_id' => env(
            'RAZORPAY_LIVE_KEY_ID'
        ),

        'live_key_secret' => env(
            'RAZORPAY_LIVE_KEY_SECRET'
        ),

        'webhook_secret' => env(
            'RAZORPAY_WEBHOOK_SECRET'
        ),

    ],

    'paytm' => [

        'environment' => env('PAYTM_ENVIRONMENT', 'staging'),
        'mid'         => env('PAYTM_MERCHANT_ID') ?: (env('PAYTM_ENVIRONMENT') === 'production' ? env('PAYTM_LIVE_MID') : env('PAYTM_TEST_MID')),
        'key'         => env('PAYTM_MERCHANT_KEY') ?: (env('PAYTM_ENVIRONMENT') === 'production' ? env('PAYTM_LIVE_MERCHANT_KEY') : env('PAYTM_TEST_MERCHANT_KEY')),
        'website'     => env('PAYTM_WEBSITE') ?: (env('PAYTM_ENVIRONMENT') === 'production' ? env('PAYTM_LIVE_WEBSITE', 'DEFAULT') : env('PAYTM_TEST_WEBSITE', 'WEBSTAGING')),
        'channel'     => env('PAYTM_CHANNEL_ID', 'WEB'),
        'industry'    => env('PAYTM_INDUSTRY_TYPE_ID', 'Retail'),
        'callback_url' => env(
            'PAYTM_CALLBACK_URL'
        ),

    ],

];
