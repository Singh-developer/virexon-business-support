<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::find(2);
$u->email = 'agent@agent-support.local';
$u->password = 'Agent@12345'; // this will trigger the hashed cast automatically!
$u->save();

echo "Demo Agent restored!\n";

