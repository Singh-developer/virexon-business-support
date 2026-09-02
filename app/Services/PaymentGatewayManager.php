<?php
namespace App\Services;
use App\Services\Payments\{PaymentGatewayInterface,MockGateway,RazorpayGateway,PaytmGateway};
use InvalidArgumentException;
class PaymentGatewayManager {public function driver(string $name):PaymentGatewayInterface{return match($name){'mock'=>app(MockGateway::class),'razorpay'=>app(RazorpayGateway::class),'paytm'=>app(PaytmGateway::class),default=>throw new InvalidArgumentException("Unsupported gateway [{$name}].")};}}
