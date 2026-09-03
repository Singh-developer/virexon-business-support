<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$agent = App\Models\User::create([
    'name' => 'Test Agent',
    'email' => 'testagent@example.com',
    'password' => 'Password123',
    'role_id' => 3, // Agent
    'business_id' => null,
    'status' => 'active'
]);

echo "Agent created.\n";

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'testagent@example.com',
    'password' => 'Password123'
]);

$response = app()->handle($request);
echo "Login response status: " . $response->getStatusCode() . "\n";
echo "Location: " . $response->headers->get('Location') . "\n";

$errors = session('errors');
if ($errors) {
    echo "Errors: " . json_encode($errors->getBag('default')->getMessages()) . "\n";
}

