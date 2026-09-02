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

        'test_mid' => env(
            'PAYTM_TEST_MID'
        ),

        'test_merchant_key' => env(
            'PAYTM_TEST_MERCHANT_KEY'
        ),

        'test_website' => env(
            'PAYTM_TEST_WEBSITE',
            'WEBSTAGING'
        ),

        'live_mid' => env(
            'PAYTM_LIVE_MID'
        ),

        'live_merchant_key' => env(
            'PAYTM_LIVE_MERCHANT_KEY'
        ),

        'live_website' => env(
            'PAYTM_LIVE_WEBSITE',
            'DEFAULT'
        ),

    ],

];
