<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find all agents and their details
$users = App\Models\User::with(['detail', 'role'])->get();

foreach($users as $user) {
    $role = optional($user->role)->slug;
    if ($role !== 'agent') continue;

    echo "=== AGENT: {$user->email} (ID: {$user->id}) ===\n";
    echo "Name: {$user->name}\n";
    echo "Phone: {$user->phone}\n";

    if ($user->detail) {
        echo "Detail ID: {$user->detail->id}\n";
        echo "Guardian Name: {$user->detail->guardian_name}\n";
        echo "Father Name: {$user->detail->father_name}\n";
        echo "Mobile: {$user->detail->mobile}\n";
        echo "Personal Email: {$user->detail->personal_email}\n";
        echo "Agent ID: {$user->detail->agent_id_number}\n";
        echo "DOB: {$user->detail->date_of_birth}\n";
        echo "Gender: {$user->detail->gender}\n";
        echo "PAN: {$user->detail->pan_number}\n";
        echo "Status: {$user->detail->application_status}\n";
    } else {
        echo "!!! NO DETAIL RECORD FOUND !!!\n";
    }
    echo "\n";
}

