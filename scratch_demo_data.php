<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'agent@agent-support.local')->first(); 
if ($user && !$user->detail) { 
    $user->detail()->create([
        'application_status' => 'pending', 
        'agent_id_number' => 'AGT12345', 
        'guardian_name' => 'Rajesh Sharma', 
        'date_of_birth' => '1990-05-15', 
        'gender' => 'male', 
        'mobile' => '9876543210', 
        'personal_email' => 'rahul.personal@gmail.com', 
        'is_married' => 1, 
        'current_address' => '123 Support St', 
        'current_city' => 'Mumbai', 
        'current_state' => 'Maharashtra', 
        'current_pincode' => '400001', 
        'account_name' => 'Rahul Sharma', 
        'bank_name' => 'HDFC Bank', 
        'account_number' => '50100200300400', 
        'routing_number' => 'HDFC0001234', 
        'account_type' => 'Savings', 
        'branch_name' => 'Andheri', 
        'pan_number' => 'ABCDE1234F'
    ]); 
    echo 'Added details'; 
} else {
    echo 'No action needed or user not found';
}

