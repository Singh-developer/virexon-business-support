<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('slug');
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox')->after('is_active');
            $table->string('sandbox_key_id')->nullable()->after('mode');
            $table->text('sandbox_key_secret')->nullable()->after('sandbox_key_id');
            $table->string('live_key_id')->nullable()->after('sandbox_key_secret');
            $table->text('live_key_secret')->nullable()->after('live_key_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropColumn([
                'is_active', 'mode',
                'sandbox_key_id', 'sandbox_key_secret',
                'live_key_id', 'live_key_secret',
            ]);
        });
    }
};
