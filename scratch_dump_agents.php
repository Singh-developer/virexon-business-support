<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::whereHas('role', function($q){$q->where('slug', 'agent');})->get();
foreach($users as $user) {
    echo "====================================\n";
    echo "Email: " . $user->email . "\n";
    if ($user->detail) {
        echo "Guardian Name: " . $user->detail->guardian_name . "\n";
        echo "Father Name: " . $user->detail->father_name . "\n";
        echo "Personal Email: " . $user->detail->personal_email . "\n";
        echo "Mobile: " . $user->detail->mobile . "\n";
    } else {
        echo "NO DETAILS FOUND!\n";
    }
}

