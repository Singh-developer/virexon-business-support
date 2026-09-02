<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Step 2 Fields
            $table->string('agent_id_number')->unique()->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Step 3 Fields
            $table->string('mobile')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->boolean('is_married')->default(false);
            $table->string('spouse_name')->nullable();
            $table->string('spouse_mobile')->nullable();

            // Step 4 Fields
            $table->text('current_address')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_state')->nullable();
            $table->string('current_pincode')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('permanent_state')->nullable();
            $table->string('permanent_pincode')->nullable();

            // Step 5 & 6 Fields (Calculator & Bank)
            $table->decimal('loan_amount', 10, 2)->nullable();
            $table->integer('loan_tenure')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();

            // Step 7 Fields
            $table->string('pan_number')->unique()->nullable();
            $table->string('aadhar_number')->unique()->nullable();
            $table->string('pan_file_path')->nullable();
            $table->string('aadhar_file_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
