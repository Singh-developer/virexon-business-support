<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'form_received' to user_details.application_status
        DB::statement("ALTER TABLE user_details MODIFY COLUMN application_status ENUM('pending','form_received','approved','rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE user_details MODIFY COLUMN application_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }
};
