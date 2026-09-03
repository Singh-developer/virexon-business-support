<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$success = Auth::attempt(['email' => 'testagent@example.com', 'password' => 'Password123']);
echo "Auth attempt result: " . ($success ? "SUCCESS" : "FAILED") . "\n";

