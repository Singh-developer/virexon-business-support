<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_cards', function (Blueprint $table) {
            $table->text('encrypted_cvv')
                ->nullable()
                ->after('encrypted_pan');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_cards', function (Blueprint $table) {
            $table->dropColumn('encrypted_cvv');
        });
    }
};
