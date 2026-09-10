<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PaytmChecksum Test ===\n";
$test = \PaytmChecksum::generateSignature('test_params', 'test_key');
echo "Generated signature length: " . strlen($test) . "\n";
echo "Signature type: " . gettype($test) . "\n";

echo "\n=== PaymentGateway Test ===\n";
$gw = App\Models\PaymentGateway::where('slug', 'paytm')->where('is_active', 1)->first();
if ($gw) {
    echo "Gateway found: {$gw->name}\n";
    echo "Mode: {$gw->mode}\n";
    echo "MID: {$gw->sandbox_key_id}\n";
    echo "Merchant Key length: " . strlen($gw->sandbox_key_secret) . "\n";
    echo "Credentials: " . json_encode($gw->credentials) . "\n";
} else {
    echo "ERROR: Paytm gateway not found or not active!\n";
}

echo "\n=== Route Test ===\n";
$route1 = route('paytm.checkout');
$route2 = route('paytm.pay');
$route3 = route('paytm.callback');
echo "paytm.checkout: {$route1}\n";
echo "paytm.pay: {$route2}\n";
echo "paytm.callback: {$route3}\n";

echo "\n=== View Test ===\n";
$viewExists = file_exists(__DIR__ . '/resources/views/paytm/checkout.blade.php');
echo "paytm.checkout view exists: " . ($viewExists ? 'YES' : 'NO') . "\n";

echo "\n=== All checks complete ===\n";
