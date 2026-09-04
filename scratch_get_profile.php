<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Auth::loginUsingId(7); // agent@agent-support.local
$request = Illuminate\Http\Request::create('/settings/profile', 'GET');
$response = app()->handle($request);
echo $response->getContent();

