<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('virtual_cards', function (Blueprint $t) {
            $t->foreignId('agent_id')->nullable()->after('business_id')->constrained('users')->nullOnDelete();
            $t->unique('agent_id');
            $t->index(['agent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('virtual_cards', function (Blueprint $t) {
            $t->dropUnique(['agent_id']);
            $t->dropIndex(['agent_id', 'status']);
            $t->dropForeign(['agent_id']);
            $t->dropColumn('agent_id');
        });
    }
};
