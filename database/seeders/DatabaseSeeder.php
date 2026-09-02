<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\{Role, Permission, User, Business, VirtualCard, PaymentGateway, Payment, Transaction};
use App\Enums\{PaymentStatus, CardStatus, TransactionType};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [];
        foreach (['dashboard.view', 'businesses.manage', 'cards.view', 'cards.manage', 'payments.manage', 'transactions.view', 'reports.view', 'settings.manage', 'audit.view', 'agents.manage'] as $slug) {
            $permissions[$slug] = Permission::firstOrCreate(['slug' => $slug], ['name' => ucwords(str_replace('.', ' ', $slug))]);
        }
        $roles = [];
        foreach ([['Super Admin', 'super-admin'], ['Admin', 'admin'], ['Agent', 'agent']] as $r) {
            $roles[$r[1]] = Role::firstOrCreate(['slug' => $r[1]], ['name' => $r[0]]);
        }
        $roles['super-admin']->permissions()->sync(array_column($permissions, 'id'));
        $roles['admin']->permissions()->sync(array_map(fn($p) => $p->id, array_filter($permissions, fn($p) => !in_array($p->slug, ['settings.manage', 'audit.view']))));
        $roles['agent']->permissions()->sync([$permissions['dashboard.view']->id, $permissions['cards.view']->id, $permissions['payments.manage']->id, $permissions['transactions.view']->id]);
        $business = Business::firstOrCreate(['name' => 'Northstar Retail Pvt Ltd'], ['contact_person' => 'Rahul Sharma', 'email' => 'ops@northstar.demo', 'phone' => '+91 90000 10001', 'address' => 'New Delhi, India', 'tax_number' => 'GST29DEMO1234Z1Z1', 'status' => 'active']);
        $admin = User::firstOrCreate(['email' => 'admin@agent-support.local'], ['name' => 'Super Admin', 'password' => 'Admin@12345', 'role_id' => $roles['super-admin']->id, 'status' => 'active', 'phone' => '+91 90000 00001']);
        $agent = User::firstOrCreate(['email' => 'agent@agent-support.local'], ['name' => 'Rahul Sharma', 'password' => 'Agent@12345', 'role_id' => $roles['agent']->id, 'business_id' => $business->id, 'status' => 'active', 'phone' => '+91 90000 00002']);
        $card = VirtualCard::firstOrCreate(['agent_id' => $agent->id], ['business_id' => $business->id, 'agent_id' => $agent->id, 'reference' => 'VC-DEMO-0001', 'cardholder_name' => $agent->name, 'status' => CardStatus::ACTIVE, 'card_limit' => 100000, 'daily_limit' => 20000, 'monthly_limit' => 80000, 'per_transaction_limit' => 20000, 'current_usage' => 12500, 'expiry_date' => now()->addYear(), 'created_by' => $admin->id]);
        foreach ([['mock', 'Mock Sandbox', true, 'test'], ['razorpay', 'Razorpay', false, 'test'], ['paytm', 'Paytm', false, 'staging']] as $g) {
            PaymentGateway::firstOrCreate(['slug' => $g[0]], ['name' => $g[1], 'status' => $g[2], 'environment' => $g[3]]);
        }
        $payment = Payment::firstOrCreate(['reference' => 'PAY-DEMO-0001'], ['business_id' => $business->id, 'user_id' => $agent->id, 'card_id' => $card->id, 'gateway' => 'mock', 'amount' => 12500, 'currency' => 'INR', 'status' => PaymentStatus::SUCCESSFUL, 'gateway_payment_id' => 'mock_pay_demo', 'completed_at' => now()->subHours(2)]);
        Transaction::firstOrCreate(['payment_id' => $payment->id], ['business_id' => $business->id, 'user_id' => $agent->id, 'card_id' => $card->id, 'gateway' => 'mock', 'gateway_transaction_id' => 'mock_pay_demo', 'transaction_type' => TransactionType::PAYMENT, 'amount' => 12500, 'fee' => 0, 'net_amount' => 12500, 'status' => 'successful', 'reference' => 'PAY-DEMO-0001', 'description' => 'Demo card payment', 'metadata' => ['seeded' => true]]);
        SystemSetting::firstOrCreate(['key' => 'payment_mode'],['value' => 'sandbox']);
    }
}
