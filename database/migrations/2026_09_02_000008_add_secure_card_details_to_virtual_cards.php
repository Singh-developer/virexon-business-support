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
        Schema::table('virtual_cards', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Provider Card ID
            |--------------------------------------------------------------------------
            |
            | ID returned by the real/mock card issuing provider.
            |
            */
            $table->string('provider_card_id')
                ->nullable()
                ->after('reference');

            /*
            |--------------------------------------------------------------------------
            | Encrypted PAN
            |--------------------------------------------------------------------------
            |
            | Full card number is encrypted before being stored.
            |
            */
            $table->text('encrypted_pan')
                ->nullable()
                ->after('provider_card_id');

            /*
            |--------------------------------------------------------------------------
            | Last Four Digits
            |--------------------------------------------------------------------------
            |
            | Used for lists/search/identification without decrypting PAN.
            |
            */
            $table->string('last4', 4)
                ->nullable()
                ->after('encrypted_pan');

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */
            $table->index(
                'provider_card_id',
                'virtual_cards_provider_card_id_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_cards', function (Blueprint $table) {

            $table->dropIndex(
                'virtual_cards_provider_card_id_index'
            );

            $table->dropColumn([
                'provider_card_id',
                'encrypted_pan',
                'last4',
            ]);
        });
    }
};
