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
            $table->string('guardian_name')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('account_type')->nullable();
            $table->string('branch_name')->nullable();
            $table->text('purpose_of_advance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn(['guardian_name', 'address_line_2', 'account_type', 'branch_name', 'purpose_of_advance']);
        });
    }
};
