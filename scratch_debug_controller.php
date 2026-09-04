<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate exactly what SettingsController->profile() does for agent ID 7
$user = App\Models\User::find(7)->load('detail', 'role');

echo "User: {$user->name} ({$user->email})\n";
echo "isAgent: " . ($user->isAgent() ? 'YES' : 'NO') . "\n";

$isAgent = $user->isAgent();
$detail = $isAgent ? $user->detail : null;

echo "Detail null? " . ($detail === null ? 'YES - NULL' : 'NO - has data') . "\n";

if ($detail) {
    echo "Personal Email: {$detail->personal_email}\n";
    echo "Mobile: {$detail->mobile}\n";
    echo "Guardian: {$detail->guardian_name}\n";
    echo "Agent ID: {$detail->agent_id_number}\n";
    echo "DOB: {$detail->date_of_birth}\n";
    echo "Status: {$detail->application_status}\n";
}

// Also test the null-safe operator PHP 8+ syntax
echo "\nNull-safe test: " . ($detail?->personal_email ?? 'NULL/EMPTY') . "\n";
echo "PHP version: " . phpversion() . "\n";

