<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Final Verification ===\n";
$gw = App\Models\PaymentGateway::where('slug', 'paytm')->first();
echo "Gateway: {$gw->name}\n";
echo "Active: " . ($gw->is_active ? 'YES' : 'NO') . "\n";
echo "Mode: {$gw->mode}\n";
echo "is_array(credentials): " . (is_array($gw->credentials) ? 'YES' : 'NO') . "\n";
echo "sandbox_key_id: {$gw->sandbox_key_id}\n";
echo "sandbox_key_secret: {$gw->sandbox_key_secret}\n";
echo "credentials: " . json_encode($gw->credentials) . "\n";

echo "\n=== Controller Instantiation Test ===\n";
try {
    $controller = new App\Http\Controllers\PaytmPaymentController();
    echo "Controller instantiated successfully!\n";
    echo "merchantKey length: " . strlen($controller->merchantKey) . "\n";
    echo "mid: {$controller->mid}\n";
    echo "environment: {$controller->environment}\n";
    echo "apiUrl: {$controller->apiUrl}\n";
} catch (Exception $e) {
    echo "Controller error: " . $e->getMessage() . "\n";
}

echo "\n=== Route Registration Test ===\n";
$routes = App::make('router')->getRoutes();
$hasCheckout = false; $hasPay = false; $hasCallback = false;
foreach ($routes as $route) {
    if ($route->getName() === 'paytm.checkout') $hasCheckout = true;
    if ($route->getName() === 'paytm.pay') $hasPay = true;
    if ($route->getName() === 'paytm.callback') $hasCallback = true;
}
echo "paytm.checkout route exists: " . ($hasCheckout ? 'YES' : 'NO') . "\n";
echo "paytm.pay route exists: " . ($hasPay ? 'YES' : 'NO') . "\n";
echo "paytm.callback route exists: " . ($hasCallback ? 'YES' : 'NO') . "\n";

echo "\n=== All verification complete ===\n";
