<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = App\Models\User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin']))->first();
$agent = App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'agent'))->first();

if($admin && $agent) {
    Illuminate\Support\Facades\Notification::send([$admin], new App\Notifications\AgentRegisteredNotification($agent));
    echo "Notification sent to {$admin->email} for agent {$agent->email}\n";
} else {
    echo "No admin or agent found.\n";
}

