<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            // Maximum transaction limit for the agent (default 500000 = 5 lakh)
            $table->decimal('max_limit', 18, 2)->default(500000.00);
            
            // Commission rate for the agent (percentage, e.g., 2.5 for 2.5%)
            $table->decimal('commission_rate', 8, 4)->default(0);
            
            // Fixed commission amount (alternative to percentage)
            $table->decimal('commission_fixed', 18, 2)->default(0);
            
            // Commission type: 'percentage' or 'fixed'
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn(['max_limit', 'commission_rate', 'commission_fixed', 'commission_type']);
        });
    }
};