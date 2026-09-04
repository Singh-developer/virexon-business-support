<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$details = App\Models\UserDetail::all();
foreach($details as $d) {
    echo "User ID: " . $d->user_id . " | Email: " . optional($d->user)->email . "\n";
    echo "Guardian: " . $d->guardian_name . " | Father: " . $d->father_name . "\n";
    echo "Agent ID: " . $d->agent_id_number . "\n";
    echo "Mobile: " . $d->mobile . "\n";
    echo "Personal Email: " . $d->personal_email . "\n";
    echo "---------------------------------\n";
}

