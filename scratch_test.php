<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Auth::loginUsingId(2); // firstform@yopmail.com
$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$response = app()->handle($request);
echo "Response status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() === 500) {
    if (isset($response->exception)) {
        echo $response->exception->getMessage() . "\n";
        echo $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
    }
}

