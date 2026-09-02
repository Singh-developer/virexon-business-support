<?php

return [
    'currency' => 'INR',
    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'mock'),
    'gateways' => [
        'mock' => ['enabled' => true],
        'razorpay' => ['enabled' => (bool) env('RAZORPAY_KEY_ID')],
        'paytm' => ['enabled' => (bool) env('PAYTM_MID')],
    ],
];
