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
        Schema::table('sanction_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('sanction_letters', 'sanction_number')) {
                $table->unsignedBigInteger('sanction_number')
                    ->nullable()
                    ->unique()
                    ->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanction_letters', function (Blueprint $table) {
            if (Schema::hasColumn('sanction_letters', 'sanction_number')) {
                $table->dropUnique(['sanction_number']);
                $table->dropColumn('sanction_number');
            }
        });
    }
};