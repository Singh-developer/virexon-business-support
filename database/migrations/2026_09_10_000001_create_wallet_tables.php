<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('order_id')->unique();
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('gateway')->default('paytm');
            $table->text('txn_token')->nullable();
            $table->string('txn_id')->nullable();
            $table->string('bank_txn_id')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('gateway_name')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('wallet_transactions');
    }
};
