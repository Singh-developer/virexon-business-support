<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'agent@agent-support.local')->first();
if ($u) {
    echo "Found user: " . $u->id . " - " . $u->email . "\n";
    $success = Auth::attempt(['email' => $u->email, 'password' => 'Agent@12345']);
    echo "Login test: " . ($success ? "SUCCESS" : "FAIL") . "\n";
} else {
    echo "User not found\n";
}

