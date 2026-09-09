<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Services\Payments\{PaymentGatewayInterface, MockGateway, RazorpayGateway, PaytmGateway};
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function driver(string $name): PaymentGatewayInterface
    {
        if ($name === 'mock') {
            return app(MockGateway::class);
        }

        $gateway = PaymentGateway::where('slug', $name)->first();

        $credentials = $gateway->credentials ?? [];
        $environment = $gateway->environment ?? 'sandbox';

        return match ($name) {
            'razorpay' => app(RazorpayGateway::class, [
                'credentials' => $credentials,
                'environment' => $environment,
            ]),
            'paytm' => app(PaytmGateway::class, [
                'credentials' => $credentials,
                'environment' => $environment,
            ]),
            default => throw new InvalidArgumentException("Unsupported gateway [{$name}]."),
        };
    }
}
