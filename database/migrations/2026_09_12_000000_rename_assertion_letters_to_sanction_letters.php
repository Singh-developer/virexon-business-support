<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assertion_letters') && !Schema::hasTable('sanction_letters')) {
            Schema::rename('assertion_letters', 'sanction_letters');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sanction_letters') && !Schema::hasTable('assertion_letters')) {
            Schema::rename('sanction_letters', 'assertion_letters');
        }
    }
};