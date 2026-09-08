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
        Schema::table('assertion_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('assertion_letters', 'signature_image')) {
                $table->string('signature_image')
                    ->nullable()
                    ->after('signature_company');
            }

            if (!Schema::hasColumn('assertion_letters', 'dynamic_fields')) {
                $table->json('dynamic_fields')
                    ->nullable()
                    ->after('body');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assertion_letters', function (Blueprint $table) {
            if (Schema::hasColumn('assertion_letters', 'signature_image')) {
                $table->dropColumn('signature_image');
            }

            if (Schema::hasColumn('assertion_letters', 'dynamic_fields')) {
                $table->dropColumn('dynamic_fields');
            }
        });
    }
};
